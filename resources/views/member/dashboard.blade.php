@extends('member.layouts.app')

@section('title', 'ダッシュボード')

@section('content')
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">ようこそ、{{ $member->name }}さん！</h1>
    <p class="text-gray-600">あなたのアクティブプラン：
        @forelse($member->activePlans as $plan)
            <span class="inline-block bg-blue-100 text-blue-800 text-sm px-2 py-0.5 rounded">{{ $plan->name }}</span>
        @empty
            <span class="text-gray-400">なし</span>
        @endforelse
    </p>
</div>

<h2 class="text-lg font-bold text-gray-800 mb-4">コンテンツ一覧</h2>

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    @foreach($categories as $category)
        <a href="{{ route('member.category', $category->slug) }}"
           class="bg-white rounded-lg shadow p-5 hover:shadow-md transition block">
            <h3 class="font-bold text-gray-800 mb-1">{{ $category->name }}</h3>
            @if($category->description)
                <p class="text-sm text-gray-600">{{ Str::limit($category->description, 80) }}</p>
            @endif
            <p class="text-xs text-gray-400 mt-2">{{ $category->pages->count() }}ページ</p>
        </a>
    @endforeach
</div>
@endsection
