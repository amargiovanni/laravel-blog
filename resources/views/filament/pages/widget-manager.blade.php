<x-filament-panels::page>
    <div
        x-data="widgetManager()"
        class="grid grid-cols-1 lg:grid-cols-4 gap-6"
    >
        {{-- Available Widgets Panel --}}
        <div class="lg:col-span-1">
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header p-6 pb-4">
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Available Widgets
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Click to add to an area
                    </p>
                </div>
                <div class="fi-section-content p-6 pt-0">
                    <div class="space-y-2">
                        @foreach($this->getAvailableWidgets() as $type => $widget)
                            <div
                                x-data="{ open: false }"
                                class="relative"
                            >
                                <button
                                    @click="open = !open"
                                    type="button"
                                    class="w-full flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                >
                                    <div class="text-left">
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $widget['name'] }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $widget['description'] }}
                                        </div>
                                    </div>
                                    <x-heroicon-m-plus class="w-5 h-5 text-gray-400" />
                                </button>

                                <div
                                    x-show="open"
                                    @click.away="open = false"
                                    x-transition
                                    class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10"
                                >
                                    <div class="p-2">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 px-2">Add to area:</p>
                                        @foreach($this->getWidgetAreas() as $areaKey => $area)
                                            <button
                                                wire:click="addWidget('{{ $areaKey }}', '{{ $type }}')"
                                                @click="open = false"
                                                type="button"
                                                class="w-full text-left px-3 py-2 text-sm rounded-md hover:bg-gray-100 dark:hover:bg-gray-700"
                                            >
                                                {{ $area['name'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Widget Areas --}}
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($this->getWidgetAreas() as $areaKey => $area)
                    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="fi-section-header p-6 pb-4">
                            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                {{ $area['name'] }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $area['description'] }}
                            </p>
                        </div>
                        <div class="fi-section-content p-6 pt-0">
                            @php $widgets = $this->getWidgetsForArea($areaKey); @endphp

                            <div
                                class="widget-sortable-area min-h-[80px] space-y-2 {{ $widgets->isEmpty() ? 'border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg' : '' }}"
                                data-area="{{ $areaKey }}"
                            >
                                @if($widgets->isEmpty())
                                    <div class="empty-placeholder text-center py-8 text-gray-500 dark:text-gray-400">
                                        <x-heroicon-o-squares-2x2 class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                        <p class="text-sm">Drop widgets here</p>
                                    </div>
                                @else
                                    @foreach($widgets as $widget)
                                        <div
                                            class="widget-item flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 cursor-move"
                                            data-widget-id="{{ $widget->id }}"
                                        >
                                            <div class="flex items-center gap-3">
                                                <x-heroicon-o-bars-2 class="w-4 h-4 text-gray-400 drag-handle" />
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white text-sm">
                                                        {{ $widget->getDisplayTitle() }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ config("widgets.types.{$widget->widget_type}.name", $widget->widget_type) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <button
                                                    wire:click="editWidget({{ $widget->id }})"
                                                    type="button"
                                                    class="p-1.5 hover:bg-gray-200 dark:hover:bg-gray-700 rounded text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                                                    title="Edit"
                                                >
                                                    <x-heroicon-m-pencil class="w-4 h-4" />
                                                </button>
                                                <button
                                                    wire:click="deleteWidget({{ $widget->id }})"
                                                    wire:confirm="Are you sure you want to remove this widget?"
                                                    type="button"
                                                    class="p-1.5 hover:bg-red-100 dark:hover:bg-red-900/30 rounded text-red-500 hover:text-red-700"
                                                    title="Remove"
                                                >
                                                    <x-heroicon-m-x-mark class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Edit Widget Modal --}}
    @if($this->editingWidgetId)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-on:close-modal.window="open = false"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75" @click="$wire.cancelEdit()"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Edit Widget: {{ $this->getEditingWidget()?->getDisplayTitle() }}
                    </h3>

                    <form wire:submit="saveWidget">
                        {{ $this->form }}

                        @php $settingsFields = $this->getSettingsFields(); @endphp
                        @if(!empty($settingsFields))
                            <div class="mt-4 space-y-4">
                                @foreach($settingsFields as $field)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ $field['label'] }}
                                        </label>

                                        @if($field['type'] === 'text')
                                            <input
                                                type="text"
                                                wire:model="widgetData.{{ $field['name'] }}"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm"
                                            >
                                        @elseif($field['type'] === 'number')
                                            <input
                                                type="number"
                                                wire:model="widgetData.{{ $field['name'] }}"
                                                min="{{ $field['min'] ?? '' }}"
                                                max="{{ $field['max'] ?? '' }}"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm"
                                            >
                                        @elseif($field['type'] === 'textarea')
                                            <textarea
                                                wire:model="widgetData.{{ $field['name'] }}"
                                                rows="{{ $field['rows'] ?? 3 }}"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm"
                                            ></textarea>
                                        @elseif($field['type'] === 'select')
                                            <select
                                                wire:model="widgetData.{{ $field['name'] }}"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm"
                                            >
                                                @foreach($field['options'] as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($field['type'] === 'toggle')
                                            <button
                                                type="button"
                                                wire:click="$set('widgetData.{{ $field['name'] }}', !$wire.widgetData['{{ $field['name'] }}'])"
                                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ ($widgetData[$field['name']] ?? false) ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}"
                                                role="switch"
                                                aria-checked="{{ ($widgetData[$field['name']] ?? false) ? 'true' : 'false' }}"
                                            >
                                                <span
                                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($widgetData[$field['name']] ?? false) ? 'translate-x-5' : 'translate-x-0' }}"
                                                ></span>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-6 flex justify-end gap-3">
                            <button
                                type="button"
                                wire:click="cancelEdit"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg"
                            >
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        function widgetManager() {
            return {
                sortables: [],

                init() {
                    // Wait for Sortable to load
                    const checkSortable = setInterval(() => {
                        if (typeof Sortable !== 'undefined') {
                            clearInterval(checkSortable);
                            this.initSortable();
                        }
                    }, 100);
                },

                initSortable() {
                    const areas = document.querySelectorAll('.widget-sortable-area');
                    const component = this;

                    areas.forEach(area => {
                        const sortable = new Sortable(area, {
                            group: 'widgets',
                            animation: 150,
                            draggable: '.widget-item',
                            ghostClass: 'opacity-50',
                            chosenClass: 'ring-2',
                            dragClass: 'shadow-lg',
                            onStart: (evt) => {
                                // Hide empty placeholders
                                document.querySelectorAll('.empty-placeholder').forEach(el => {
                                    el.style.display = 'none';
                                });
                                // Add drop zone visual
                                document.querySelectorAll('.widget-sortable-area').forEach(el => {
                                    el.style.border = '2px dashed #8b5cf6';
                                    el.style.borderRadius = '8px';
                                });
                            },
                            onEnd: (evt) => {
                                // Remove drop zone visual
                                document.querySelectorAll('.widget-sortable-area').forEach(el => {
                                    el.style.border = '';
                                    el.style.borderRadius = '';
                                });
                                // Show empty placeholders again
                                document.querySelectorAll('.empty-placeholder').forEach(el => {
                                    el.style.display = '';
                                });

                                const widgetId = evt.item.dataset.widgetId;
                                const newArea = evt.to.dataset.area;
                                const oldArea = evt.from.dataset.area;

                                // Get all widget IDs in the new area
                                const orderedIds = Array.from(evt.to.querySelectorAll('.widget-item'))
                                    .map(el => parseInt(el.dataset.widgetId));

                                if (oldArea !== newArea) {
                                    // Widget moved to a different area
                                    @this.call('moveWidget', parseInt(widgetId), newArea);
                                }

                                // Reorder widgets in the target area
                                @this.call('reorderWidgets', newArea, orderedIds);

                                // If moved from another area, also reorder the source
                                if (oldArea !== newArea) {
                                    const sourceOrderedIds = Array.from(evt.from.querySelectorAll('.widget-item'))
                                        .map(el => parseInt(el.dataset.widgetId));
                                    if (sourceOrderedIds.length > 0) {
                                        @this.call('reorderWidgets', oldArea, sourceOrderedIds);
                                    }
                                }
                            }
                        });

                        this.sortables.push(sortable);
                    });

                    console.log('SortableJS initialized for', areas.length, 'areas');
                }
            }
        }
    </script>
</x-filament-panels::page>
