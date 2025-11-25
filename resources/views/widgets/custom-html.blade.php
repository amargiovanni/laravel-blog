<div class="widget widget-custom-html">
    @if($title)
        <h3 class="widget-title">{{ $title }}</h3>
    @endif

    <div class="custom-html-content">
        {!! $content !!}
    </div>
</div>
