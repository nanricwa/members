@extends('member.layouts.auth')

@section('title', 'ログイン')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6 text-center">ログイン</h2>

    <form method="POST" action="{{ route('member.login') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required autofocus>
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">パスワード</label>
            <input type="password" name="password" id="password"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
        </div>

        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600">
                <span class="ml-2 text-sm text-gray-600">ログイン状態を保持する</span>
            </label>
        </div>

        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition font-medium">
            ログイン
        </button>
    </form>
</div>
@endsection
