<div class="widget widget-recent-posts">
    @if($title)
        <h3 class="widget-title">{{ $title }}</h3>
    @endif

    @if($posts->isEmpty())
        <p class="widget-empty">No posts yet.</p>
    @else
        <ul class="recent-posts-list">
            @foreach($posts as $post)
                <li class="recent-post-item">
                    @if($showThumbnail && $post->featuredImage)
                        <div class="recent-post-thumbnail">
                            <a href="{{ route('posts.show', $post->slug) }}">
                                <img src="{{ $post->featuredImage->url }}" alt="{{ $post->title }}">
                            </a>
                        </div>
                    @endif
                    <div class="recent-post-content">
                        <a href="{{ route('posts.show', $post->slug) }}" class="recent-post-title">
                            {{ $post->title }}
                        </a>
                        @if($showDate)
                            <span class="recent-post-date">
                                {{ $post->published_at->format('M j, Y') }}
                            </span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
