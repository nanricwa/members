@extends('member.layouts.app')

@section('title', $page->title)

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h1 class="text-2xl font-bold text-gray-800">{{ $page->title }}</h1>
        @if($page->category)
            <a href="{{ route('member.category', $page->category->slug) }}"
               class="text-sm text-blue-600 hover:underline mt-1 inline-block">
                ← {{ $page->category->name }}
            </a>
        @endif
    </div>

    <div class="p-6 space-y-6">
        @foreach($page->contents as $content)
            @include('member.components.content-block.' . $content->type->value, ['content' => $content])
        @endforeach
    </div>
</div>
@endsection
