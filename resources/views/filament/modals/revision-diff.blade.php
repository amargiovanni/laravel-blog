<div class="space-y-6">
    <div class="flex items-center justify-between text-sm">
        <div class="flex items-center gap-4">
            <span class="font-medium text-red-600 dark:text-red-400">
                Revision #{{ $revision->revision_number }} ({{ $revision->created_at->format('M j, Y H:i') }})
            </span>
            <span class="text-gray-400">→</span>
            <span class="font-medium text-green-600 dark:text-green-400">
                Current Version
            </span>
        </div>
    </div>

    @if($diff['title'])
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Title Changes</h4>
            <div class="revision-diff-container bg-gray-50 dark:bg-gray-800 rounded-lg p-4 overflow-x-auto">
                {!! $diff['title'] !!}
            </div>
        </div>
    @else
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Title</h4>
            <p class="text-gray-500 dark:text-gray-400 italic">No changes</p>
        </div>
    @endif

    @if($diff['excerpt'])
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Excerpt Changes</h4>
            <div class="revision-diff-container bg-gray-50 dark:bg-gray-800 rounded-lg p-4 overflow-x-auto">
                {!! $diff['excerpt'] !!}
            </div>
        </div>
    @else
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Excerpt</h4>
            <p class="text-gray-500 dark:text-gray-400 italic">No changes</p>
        </div>
    @endif

    @if($diff['content'])
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Content Changes</h4>
            <div class="revision-diff-container bg-gray-50 dark:bg-gray-800 rounded-lg p-4 overflow-x-auto max-h-96 overflow-y-auto">
                {!! $diff['content'] !!}
            </div>
        </div>
    @else
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Content</h4>
            <p class="text-gray-500 dark:text-gray-400 italic">No changes</p>
        </div>
    @endif
</div>

<style>
    .revision-diff-container table {
        width: 100%;
        border-collapse: collapse;
        font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Monaco, Consolas, monospace;
        font-size: 0.75rem;
    }
    .revision-diff-container th,
    .revision-diff-container td {
        padding: 0.25rem 0.5rem;
        border: 1px solid rgb(229 231 235);
        vertical-align: top;
    }
    .dark .revision-diff-container th,
    .dark .revision-diff-container td {
        border-color: rgb(55 65 81);
    }
    .revision-diff-container .ChangeDelete {
        background-color: rgb(254 226 226);
    }
    .dark .revision-diff-container .ChangeDelete {
        background-color: rgb(127 29 29 / 0.3);
    }
    .revision-diff-container .ChangeInsert {
        background-color: rgb(220 252 231);
    }
    .dark .revision-diff-container .ChangeInsert {
        background-color: rgb(20 83 45 / 0.3);
    }
    .revision-diff-container .ChangeReplace {
        background-color: rgb(254 249 195);
    }
    .dark .revision-diff-container .ChangeReplace {
        background-color: rgb(113 63 18 / 0.3);
    }
    .revision-diff-container del {
        background-color: rgb(248 113 113);
        text-decoration: line-through;
    }
    .dark .revision-diff-container del {
        background-color: rgb(185 28 28 / 0.5);
    }
    .revision-diff-container ins {
        background-color: rgb(74 222 128);
        text-decoration: none;
    }
    .dark .revision-diff-container ins {
        background-color: rgb(22 101 52 / 0.5);
    }
</style>
