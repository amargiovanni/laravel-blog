<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\WidgetInstance;
use App\Services\WidgetRegistry;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class WidgetManager extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $widgetData = [];

    public ?string $editingWidgetId = null;

    public int $refreshKey = 0;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Appearance';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Widgets';

    protected static string $view = 'filament.pages.widget-manager';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function getWidgetAreas(): Collection
    {
        return app(WidgetRegistry::class)->getWidgetAreas();
    }

    public function getAvailableWidgets(): Collection
    {
        return app(WidgetRegistry::class)->getAvailableWidgets();
    }

    public function getWidgetsForArea(string $area): Collection
    {
        return WidgetInstance::forArea($area)->get();
    }

    public function addWidget(string $area, string $type): void
    {
        $registry = app(WidgetRegistry::class);
        $maxOrder = WidgetInstance::where('area', $area)->max('sort_order') ?? -1;

        WidgetInstance::create([
            'area' => $area,
            'widget_type' => $type,
            'title' => null,
            'settings' => $registry->getDefaultSettings($type),
            'sort_order' => $maxOrder + 1,
        ]);

        Notification::make()
            ->title('Widget added')
            ->success()
            ->send();

        $this->refreshKey++;
    }

    public function editWidget(int $widgetId): void
    {
        $this->editingWidgetId = (string) $widgetId;
        $widget = WidgetInstance::find($widgetId);

        if ($widget) {
            $this->widgetData = [
                'title' => $widget->title,
                ...($widget->settings ?? []),
            ];
        }
    }

    public function saveWidget(): void
    {
        if (! $this->editingWidgetId) {
            return;
        }

        $widget = WidgetInstance::find($this->editingWidgetId);

        if (! $widget) {
            return;
        }

        $data = $this->widgetData;
        $title = $data['title'] ?? null;
        unset($data['title']);

        $widget->update([
            'title' => $title,
            'settings' => $data,
        ]);

        $this->editingWidgetId = null;
        $this->widgetData = [];

        Notification::make()
            ->title('Widget saved')
            ->success()
            ->send();
    }

    public function cancelEdit(): void
    {
        $this->editingWidgetId = null;
        $this->widgetData = [];
    }

    public function deleteWidget(int $widgetId): void
    {
        WidgetInstance::destroy($widgetId);

        Notification::make()
            ->title('Widget deleted')
            ->success()
            ->send();

        $this->refreshKey++;
    }

    public function reorderWidgets(string $area, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            WidgetInstance::where('id', $id)->update(['sort_order' => $index]);
        }

        WidgetInstance::clearWidgetCache();

        $this->refreshKey++;
    }

    public function moveWidget(int $widgetId, string $newArea): void
    {
        $widget = WidgetInstance::find($widgetId);

        if (! $widget) {
            return;
        }

        $maxOrder = WidgetInstance::where('area', $newArea)->max('sort_order') ?? -1;

        $widget->update([
            'area' => $newArea,
            'sort_order' => $maxOrder + 1,
        ]);

        Notification::make()
            ->title('Widget moved')
            ->success()
            ->send();

        $this->refreshKey++;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Widget Title')
                    ->placeholder('Leave empty to use default'),
            ])
            ->statePath('widgetData');
    }

    public function getEditingWidget(): ?WidgetInstance
    {
        if (! $this->editingWidgetId) {
            return null;
        }

        return WidgetInstance::find($this->editingWidgetId);
    }

    public function getSettingsFields(): array
    {
        $widget = $this->getEditingWidget();

        if (! $widget) {
            return [];
        }

        return app(WidgetRegistry::class)->getSettingsFields($widget->widget_type);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
