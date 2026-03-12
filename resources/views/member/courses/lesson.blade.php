@extends('member.layouts.app')

@section('title', $lesson->page->title)

@section('content')
{{-- パンくず --}}
<nav class="mb-4 text-sm text-gray-500">
    <a href="{{ route('member.courses') }}" class="hover:text-blue-600">コース</a>
    <span class="mx-1">/</span>
    <a href="{{ route('member.course', $course->slug) }}" class="hover:text-blue-600">{{ $course->title }}</a>
    <span class="mx-1">/</span>
    <span class="text-gray-400">{{ $lesson->module->title }}</span>
    <span class="mx-1">/</span>
    <span class="text-gray-800">{{ $lesson->page->title }}</span>
</nav>

<div class="bg-white rounded-lg shadow">
    {{-- レッスンヘッダー --}}
    <div class="p-6 border-b">
        <h1 class="text-2xl font-bold text-gray-800">{{ $lesson->page->title }}</h1>
        @if($lesson->estimated_minutes)
            <span class="text-sm text-gray-400 mt-1 inline-block">所要時間: 約{{ $lesson->estimated_minutes }}分</span>
        @endif
    </div>

    {{-- コンテンツブロック --}}
    <div class="p-6 space-y-6">
        @foreach($lesson->page->contents as $content)
            @include('member.components.content-block.' . $content->type->value, ['content' => $content])
        @endforeach
    </div>

    {{-- 完了ボタン + ナビゲーション --}}
    <div class="px-6 py-4 bg-gray-50 border-t">
        {{-- 完了トグル --}}
        <div class="flex justify-center mb-4">
            <form method="POST" action="{{ route('member.course.lesson.complete', [$course->slug, $lesson->id]) }}">
                @csrf
                @if($isCompleted)
                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        完了済み（クリックで取り消し）
                    </button>
                @else
                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        このレッスンを完了にする
                    </button>
                @endif
            </form>
        </div>

        {{-- 前後ナビゲーション --}}
        <div class="flex items-center justify-between">
            <div>
                @if($prevLesson)
                    <a href="{{ route('member.course.lesson', [$course->slug, $prevLesson->id]) }}"
                       class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        前のレッスン
                    </a>
                @endif
            </div>

            <a href="{{ route('member.course', $course->slug) }}"
               class="text-sm text-gray-500 hover:text-blue-600 transition">
                コースに戻る
            </a>

            <div>
                @if($nextLesson)
                    <a href="{{ route('member.course.lesson', [$course->slug, $nextLesson->id]) }}"
                       class="inline-flex items-center text-sm text-gray-600 hover:text-blue-600 transition">
                        次のレッスン
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
