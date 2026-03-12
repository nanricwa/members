@if($content->download && $content->download->is_active)
<div class="bg-gray-50 rounded-lg p-4 flex items-center justify-between border">
    <div>
        <h4 class="font-medium text-gray-800">{{ $content->download->title }}</h4>
        @if($content->download->description)
            <p class="text-sm text-gray-500 mt-1">{{ $content->download->description }}</p>
        @endif
        <p class="text-xs text-gray-400 mt-1">{{ $content->download->formatted_file_size }}</p>
    </div>
    <a href="{{ route('member.download', $content->download->id) }}"
       class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition text-sm font-medium flex-shrink-0">
        ダウンロード
    </a>
</div>
@endif
