<div class="widget widget-categories">
    @if($title)
        <h3 class="widget-title">{{ $title }}</h3>
    @endif

    @if($categories->isEmpty())
        <p class="widget-empty">No categories yet.</p>
    @else
        <ul class="categories-list">
            @foreach($categories as $category)
                <li class="category-item">
                    <a href="{{ route('categories.show', $category->slug) }}" class="category-link">
                        {{ $category->name }}
                        @if($showCount)
                            <span class="category-count">({{ $category->posts_count }})</span>
                        @endif
                    </a>
                    @if($hierarchical && $category->children && $category->children->isNotEmpty())
                        <ul class="categories-children">
                            @foreach($category->children as $child)
                                <li class="category-item category-child">
                                    <a href="{{ route('categories.show', $child->slug) }}" class="category-link">
                                        {{ $child->name }}
                                        @if($showCount)
                                            <span class="category-count">({{ $child->posts_count }})</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
