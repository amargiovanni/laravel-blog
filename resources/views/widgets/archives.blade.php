<div class="widget widget-archives">
    @if($title)
        <h3 class="widget-title">{{ $title }}</h3>
    @endif

    @if(empty($archives))
        <p class="widget-empty">No archives yet.</p>
    @else
        <ul class="archives-list">
            @foreach($archives as $archive)
                <li class="archive-item">
                    <a href="{{ $archive['url'] }}" class="archive-link">
                        {{ $archive['label'] }}
                        @if($showCount)
                            <span class="archive-count">({{ $archive['count'] }})</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
