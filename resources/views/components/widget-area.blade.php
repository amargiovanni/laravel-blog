@props(['area', 'class' => ''])

@if($hasWidgets())
<aside {{ $attributes->merge(['class' => 'widget-area widget-area-' . $area . ' ' . $class]) }}>
    @foreach($widgets as $widget)
        <div class="widget-wrapper" data-widget-id="{{ $widget->id }}">
            {!! $renderWidget($widget) !!}
        </div>
    @endforeach
</aside>
@endif
