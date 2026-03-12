<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'マイページ') - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('member.dashboard') }}" class="text-xl font-bold text-gray-800">
                        {{ config('app.name') }}
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ Auth::guard('member')->user()->name }} さん</span>
                    <a href="{{ route('member.subscriptions') }}" class="text-sm text-blue-600 hover:underline">サブスク管理</a>
                    <a href="{{ route('member.profile') }}" class="text-sm text-blue-600 hover:underline">プロフィール</a>
                    <form method="POST" action="{{ route('member.logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">ログアウト</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex gap-8">
            <!-- サイドバー -->
            <aside class="w-64 flex-shrink-0">
                <nav class="bg-white rounded-lg shadow p-4 space-y-1">
                    @php
                        $menuItems = \App\Models\MenuItem::visible()->root()->ordered()->with('children')->get();
                    @endphp
                    @foreach($menuItems as $item)
                        @if($item->type->value === 'divider')
                            <hr class="my-2">
                        @else
                            <a href="{{ $item->resolved_url ?? '#' }}"
                               class="block px-3 py-2 rounded-md text-sm {{ request()->url() === $item->resolved_url ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                {{ $item->label }}
                            </a>
                            @foreach($item->children as $child)
                                <a href="{{ $child->resolved_url ?? '#' }}"
                                   class="block pl-8 pr-3 py-1.5 rounded-md text-sm text-gray-600 hover:bg-gray-50">
                                    {{ $child->label }}
                                </a>
                            @endforeach
                        @endif
                    @endforeach
                </nav>
            </aside>

            <!-- メインコンテンツ -->
            <main class="flex-1 min-w-0">
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                        <ul class="list-disc pl-4">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
