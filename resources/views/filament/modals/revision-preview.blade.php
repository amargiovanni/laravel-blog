<div class="space-y-6">
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <span class="font-medium text-gray-500 dark:text-gray-400">Author:</span>
            <span class="ml-2">{{ $revision->user?->name ?? 'Unknown' }}</span>
        </div>
        <div>
            <span class="font-medium text-gray-500 dark:text-gray-400">Created:</span>
            <span class="ml-2">{{ $revision->created_at->format('M j, Y \a\t H:i') }}</span>
        </div>
        <div>
            <span class="font-medium text-gray-500 dark:text-gray-400">Type:</span>
            <span class="ml-2">{{ $revision->is_autosave ? 'Autosave' : 'Manual Save' }}</span>
        </div>
        <div>
            <span class="font-medium text-gray-500 dark:text-gray-400">Status:</span>
            <span class="ml-2">{{ $revision->getMetadata('status', 'draft') }}</span>
        </div>
    </div>

    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h4 class="font-medium text-gray-900 dark:text-white mb-2">Title</h4>
        <p class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
            {{ $revision->title }}
        </p>
    </div>

    @if($revision->excerpt)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Excerpt</h4>
            <p class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                {{ $revision->excerpt }}
            </p>
        </div>
    @endif

    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h4 class="font-medium text-gray-900 dark:text-white mb-2">Content</h4>
        <div class="prose prose-sm dark:prose-invert max-w-none bg-gray-50 dark:bg-gray-800 rounded-lg p-4 max-h-96 overflow-y-auto">
            {!! $revision->content !!}
        </div>
    </div>

    @if($revision->metadata)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Metadata</h4>
            <dl class="grid grid-cols-2 gap-2 text-sm">
                @foreach($revision->metadata as $key => $value)
                    <dt class="font-medium text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $key)) }}:</dt>
                    <dd class="text-gray-700 dark:text-gray-300">
                        @if(is_array($value))
                            {{ implode(', ', $value) }}
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                @endforeach
            </dl>
        </div>
    @endif
</div>
