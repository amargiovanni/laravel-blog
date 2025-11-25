<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\LlmsTxtService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class GeoSettings extends Page implements HasForms
{
    use InteractsWithForms;

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'GEO (AI Optimization)';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.geo-settings';

    /**
     * Default GEO values.
     *
     * @var array<string, mixed>
     */
    protected array $defaults = [
        'llms_enabled' => true,
        'llms_include_posts' => true,
        'jsonld_enabled' => true,
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage geo settings') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'llms_enabled' => Setting::get('geo.llms_enabled', $this->defaults['llms_enabled']),
            'llms_include_posts' => Setting::get('geo.llms_include_posts', $this->defaults['llms_include_posts']),
            'jsonld_enabled' => Setting::get('geo.jsonld_enabled', $this->defaults['jsonld_enabled']),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('llms.txt Configuration')
                    ->description('Configure the llms.txt file for AI crawler optimization')
                    ->schema([
                        Forms\Components\Toggle::make('llms_enabled')
                            ->label('Enable llms.txt')
                            ->helperText('Generate and serve /llms.txt for AI crawlers')
                            ->live(),
                        Forms\Components\Toggle::make('llms_include_posts')
                            ->label('Include Blog Posts')
                            ->helperText('Include published posts in the llms.txt file')
                            ->visible(fn ($get): bool => (bool) $get('llms_enabled')),
                        Forms\Components\Placeholder::make('llms_url')
                            ->label('llms.txt URL')
                            ->content(fn (): string => url('/llms.txt'))
                            ->visible(fn ($get): bool => (bool) $get('llms_enabled')),
                    ]),

                Forms\Components\Section::make('JSON-LD Structured Data')
                    ->description('Configure structured data for search engines and AI')
                    ->schema([
                        Forms\Components\Toggle::make('jsonld_enabled')
                            ->label('Enable JSON-LD')
                            ->helperText('Add structured data to pages for better SEO and AI understanding'),
                    ]),

                Forms\Components\Section::make('llms.txt Preview')
                    ->description('Current llms.txt content')
                    ->schema([
                        Forms\Components\Placeholder::make('llms_preview')
                            ->label('')
                            ->content(fn (): string => $this->getLlmsPreview())
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Validation Status')
                    ->description('llms.txt validation results')
                    ->schema([
                        Forms\Components\Placeholder::make('validation_status')
                            ->label('')
                            ->content(fn (): \Illuminate\Support\HtmlString => $this->getValidationStatus())
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set("geo.{$key}", $value, 'geo');
        }

        // Clear llms.txt cache
        Cache::forget('llms.txt');

        Notification::make()
            ->success()
            ->title('GEO settings saved')
            ->body('Your GEO settings have been updated successfully.')
            ->send();
    }

    public function regenerateLlmsTxt(): void
    {
        Cache::forget('llms.txt');

        Notification::make()
            ->success()
            ->title('llms.txt regenerated')
            ->body('The llms.txt cache has been cleared and will be regenerated on next request.')
            ->send();
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
            Action::make('regenerateLlmsTxt')
                ->label('Regenerate llms.txt')
                ->color('gray')
                ->action('regenerateLlmsTxt'),
        ];
    }

    protected function getLlmsPreview(): string
    {
        $service = app(LlmsTxtService::class);
        $content = $service->generate();

        if (empty($content)) {
            return 'llms.txt generation is disabled.';
        }

        // Wrap in pre tag for proper formatting
        return '<pre class="text-sm bg-gray-100 dark:bg-gray-800 p-4 rounded overflow-auto max-h-96">'.e($content).'</pre>';
    }

    protected function getValidationStatus(): \Illuminate\Support\HtmlString
    {
        $service = app(LlmsTxtService::class);
        $validation = $service->validate();

        if ($validation['valid']) {
            return new \Illuminate\Support\HtmlString(
                '<div class="flex items-center gap-2 text-green-600 dark:text-green-400">'.
                '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'.
                '<span>llms.txt is valid</span>'.
                '</div>'
            );
        }

        $errorHtml = '<div class="space-y-2">';
        $errorHtml .= '<div class="flex items-center gap-2 text-red-600 dark:text-red-400">';
        $errorHtml .= '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';
        $errorHtml .= '<span>Validation errors found:</span>';
        $errorHtml .= '</div>';
        $errorHtml .= '<ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400">';

        foreach ($validation['errors'] as $error) {
            $errorHtml .= '<li>'.e($error).'</li>';
        }

        $errorHtml .= '</ul></div>';

        return new \Illuminate\Support\HtmlString($errorHtml);
    }
}
