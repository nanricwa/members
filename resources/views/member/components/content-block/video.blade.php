@if($content->embed_url)
<div class="aspect-video rounded-lg overflow-hidden bg-black">
    <iframe src="{{ $content->embed_url }}"
            class="w-full h-full"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen></iframe>
</div>
@else
<div class="bg-gray-100 rounded-lg p-4 text-center text-gray-500">
    動画を読み込めません
</div>
@endif
