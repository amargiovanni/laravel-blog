@props(['area', 'class' => ''])

@if($hasWidgets())
<div {{ $attributes->merge(['class' => 'widget-area widget-area-' . $area . ' space-y-6 ' . $class]) }}>
    @foreach($widgets as $widget)
        <div class="widget-wrapper" data-widget-id="{{ $widget->id }}">
            {!! $renderWidget($widget) !!}
        </div>
    @endforeach
</div>
@endif
