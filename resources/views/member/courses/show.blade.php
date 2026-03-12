@extends('member.layouts.app')

@section('title', $course->title)

@section('content')
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <a href="{{ route('member.courses') }}" class="text-sm text-blue-600 hover:underline mb-2 inline-block">&larr; コース一覧</a>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ $course->title }}</h1>
            @if($course->description)
                <p class="text-gray-600 mb-4">{{ $course->description }}</p>
            @endif
        </div>
    </div>

    {{-- 全体進捗 --}}
    <div class="flex items-center gap-4 mb-4">
        <div class="flex-1">
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-600 h-3 rounded-full transition-all" style="width: {{ $progress }}%"></div>
            </div>
        </div>
        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">{{ $progress }}% 完了</span>
    </div>

    <div class="flex items-center gap-4 text-sm text-gray-500">
        <span>{{ $course->lessons()->count() }}レッスン</span>
        @if($course->estimated_minutes)
            <span>約{{ $course->estimated_minutes }}分</span>
        @endif
    </div>

    @if($nextLesson)
        <div class="mt-4">
            <a href="{{ route('member.course.lesson', [$course->slug, $nextLesson->id]) }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                {{ $progress > 0 ? '次のレッスンへ' : 'コースを開始する' }}
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    @elseif($progress === 100)
        <div class="mt-4 inline-flex items-center px-4 py-2 bg-green-100 text-green-800 text-sm font-medium rounded-lg">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            コース完了！
        </div>
    @endif
</div>

{{-- モジュール・レッスン一覧 --}}
<div class="space-y-4">
    @foreach($course->modules as $moduleIndex => $module)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b">
                <h2 class="font-bold text-gray-800">
                    <span class="text-gray-400 mr-2">{{ $moduleIndex + 1 }}.</span>
                    {{ $module->title }}
                </h2>
                @if($module->description)
                    <p class="text-sm text-gray-500 mt-1">{{ $module->description }}</p>
                @endif
            </div>

            <ul class="divide-y">
                @foreach($module->lessons as $lessonIndex => $lesson)
                    @php
                        $lessonProgress = $progressMap->get($lesson->id);
                        $isLessonCompleted = $lessonProgress && $lessonProgress->is_completed;
                    @endphp
                    <li>
                        <a href="{{ route('member.course.lesson', [$course->slug, $lesson->id]) }}"
                           class="flex items-center px-6 py-4 hover:bg-gray-50 transition">
                            {{-- 完了チェックマーク --}}
                            <div class="flex-shrink-0 mr-4">
                                @if($isLessonCompleted)
                                    <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-6 h-6 rounded-full border-2 border-gray-300"></div>
                                @endif
                            </div>

                            {{-- レッスン情報 --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium {{ $isLessonCompleted ? 'text-gray-500 line-through' : 'text-gray-800' }}">
                                    {{ $lesson->page->title }}
                                </p>
                                @if($lesson->page->excerpt)
                                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $lesson->page->excerpt }}</p>
                                @endif
                            </div>

                            {{-- 所要時間 --}}
                            @if($lesson->estimated_minutes)
                                <span class="text-xs text-gray-400 ml-3">{{ $lesson->estimated_minutes }}分</span>
                            @endif

                            <svg class="w-4 h-4 text-gray-300 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
@endsection
