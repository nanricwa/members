@extends('member.layouts.app')

@section('title', 'コース一覧')

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">コース一覧</h1>

@if($courses->isEmpty())
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500">利用可能なコースはありません。</p>
    </div>
@else
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach($courses as $course)
            <a href="{{ route('member.course', $course->slug) }}"
               class="bg-white rounded-lg shadow hover:shadow-md transition block overflow-hidden">
                @if($course->thumbnail)
                    <img src="{{ Storage::url($course->thumbnail) }}" alt="{{ $course->title }}"
                         class="w-full h-40 object-cover">
                @else
                    <div class="w-full h-40 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                @endif
                <div class="p-5">
                    <h3 class="font-bold text-gray-800 mb-1">{{ $course->title }}</h3>
                    @if($course->description)
                        <p class="text-sm text-gray-600 mb-3">{{ Str::limit($course->description, 80) }}</p>
                    @endif

                    <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                        <span>{{ $course->total_lessons_count }}レッスン</span>
                        @if($course->estimated_minutes)
                            <span>約{{ $course->estimated_minutes }}分</span>
                        @endif
                    </div>

                    {{-- 進捗バー --}}
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-1">
                        <div class="bg-blue-600 h-2 rounded-full transition-all"
                             style="width: {{ $course->progress_percentage }}%"></div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500">{{ $course->progress_percentage }}% 完了</span>
                        <span class="text-xs font-medium {{ $course->progress_percentage > 0 ? 'text-blue-600' : 'text-gray-400' }}">
                            {{ $course->progress_percentage > 0 ? '続ける' : '開始する' }} &rarr;
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
