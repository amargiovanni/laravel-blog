<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                            @if($widgets->isEmpty())
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                                    <x-heroicon-o-squares-2x2 class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                    <p class="text-sm">No widgets yet</p>
                                </div>
                            @else
                                <div class="space-y-2">
                                    @foreach($widgets as $widget)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg group">
                                            <div class="flex items-center gap-3">
                                                <x-heroicon-o-bars-2 class="w-4 h-4 text-gray-400 cursor-move" />
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white text-sm">
                                                        {{ $widget->getDisplayTitle() }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ config("widgets.types.{$widget->widget_type}.name", $widget->widget_type) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                                <button
                                                    wire:click="editWidget({{ $widget->id }})"
                                                    type="button"
                                                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded"
                                                    title="Edit"
                                                >
                                                    <x-heroicon-m-pencil class="w-4 h-4 text-gray-500" />
                                                </button>
                                                <button
                                                    wire:click="deleteWidget({{ $widget->id }})"
                                                    wire:confirm="Are you sure you want to delete this widget?"
                                                    type="button"
                                                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded"
                                                    title="Delete"
                                                >
                                                    <x-heroicon-m-trash class="w-4 h-4 text-red-500" />
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
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
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    wire:model="widgetData.{{ $field['name'] }}"
                                                    class="sr-only peer"
                                                >
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                            </label>
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
</x-filament-panels::page>
