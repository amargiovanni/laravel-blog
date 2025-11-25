@props(['location' => 'header', 'class' => ''])

@if($hasItems())
<nav {{ $attributes->merge(['class' => 'navigation navigation-' . $location . ' ' . $class]) }}>
    <ul class="nav-list">
        @foreach($items as $item)
            <li class="nav-item {{ $item->hasChildren() ? 'has-children' : '' }} {{ $item->css_class ?? '' }}">
                <a
                    href="{{ $item->getUrl() }}"
                    target="{{ $item->target }}"
                    @if($item->title_attribute) title="{{ $item->title_attribute }}" @endif
                    class="nav-link"
                >
                    {{ $item->getDisplayLabel() }}
                    @if($item->hasChildren())
                        <span class="dropdown-indicator">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </span>
                    @endif
                </a>

                @if($item->children->isNotEmpty())
                    <ul class="nav-dropdown">
                        @foreach($item->children as $child)
                            <li class="nav-item {{ $child->hasChildren() ? 'has-children' : '' }} {{ $child->css_class ?? '' }}">
                                <a
                                    href="{{ $child->getUrl() }}"
                                    target="{{ $child->target }}"
                                    @if($child->title_attribute) title="{{ $child->title_attribute }}" @endif
                                    class="nav-link"
                                >
                                    {{ $child->getDisplayLabel() }}
                                </a>

                                @if($child->children->isNotEmpty())
                                    <ul class="nav-dropdown nav-dropdown-nested">
                                        @foreach($child->children as $grandchild)
                                            <li class="nav-item {{ $grandchild->css_class ?? '' }}">
                                                <a
                                                    href="{{ $grandchild->getUrl() }}"
                                                    target="{{ $grandchild->target }}"
                                                    @if($grandchild->title_attribute) title="{{ $grandchild->title_attribute }}" @endif
                                                    class="nav-link"
                                                >
                                                    {{ $grandchild->getDisplayLabel() }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
</nav>
@endif
