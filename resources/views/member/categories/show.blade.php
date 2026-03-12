@extends('member.layouts.app')

@section('title', $category->name)

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ $category->name }}</h1>
    @if($category->description)
        <p class="text-gray-600 mt-1">{{ $category->description }}</p>
    @endif
</div>

<div class="space-y-3">
    @forelse($pages as $page)
        <a href="{{ route('member.page', $page->slug) }}"
           class="bg-white rounded-lg shadow p-4 hover:shadow-md transition flex items-center justify-between block">
            <div>
                <h3 class="font-medium text-gray-800">{{ $page->title }}</h3>
                @if($page->excerpt)
                    <p class="text-sm text-gray-500 mt-1">{{ Str::limit($page->excerpt, 100) }}</p>
                @endif
            </div>
            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            このカテゴリにはまだコンテンツがありません。
        </div>
    @endforelse
</div>
@endsection
