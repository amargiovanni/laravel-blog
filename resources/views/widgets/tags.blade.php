<div class="widget widget-tags">
    @if($title)
        <h3 class="widget-title">{{ $title }}</h3>
    @endif

    @if($tags->isEmpty())
        <p class="widget-empty">No tags yet.</p>
    @else
        <div class="tag-cloud">
            @foreach($tags as $tag)
                <a
                    href="{{ route('tags.show', $tag->slug) }}"
                    class="tag-link"
                    style="font-size: {{ $tag->size }}rem"
                    title="{{ $tag->posts_count }} {{ Str::plural('post', $tag->posts_count) }}"
                >
                    {{ $tag->name }}
                </a>
            @endforeach
        </div>
    @endif
</div>
