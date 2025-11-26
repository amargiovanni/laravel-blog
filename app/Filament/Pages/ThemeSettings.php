<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ThemeSettings extends Page implements HasForms
{
    use InteractsWithForms;

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Theme';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.theme-settings';

    /**
     * Default theme values.
     *
     * @var array<string, string>
     */
    protected array $defaults = [
        'primary_color' => '#3b82f6',
        'secondary_color' => '#10b981',
        'accent_color' => '#8b5cf6',
        'text_color' => '#1f2937',
        'background_color' => '#ffffff',
        'footer_text' => '',
        'logo' => '',
        'favicon' => '',
        'site_title' => '',
        'site_subtitle' => '',
        'search_title' => 'Search',
        'search_placeholder' => 'Search posts, categories, tags...',
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage theme settings') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'primary_color' => Setting::get('theme.primary_color', $this->defaults['primary_color']),
            'secondary_color' => Setting::get('theme.secondary_color', $this->defaults['secondary_color']),
            'accent_color' => Setting::get('theme.accent_color', $this->defaults['accent_color']),
            'text_color' => Setting::get('theme.text_color', $this->defaults['text_color']),
            'background_color' => Setting::get('theme.background_color', $this->defaults['background_color']),
            'footer_text' => Setting::get('theme.footer_text', $this->defaults['footer_text']),
            'logo' => Setting::get('theme.logo', $this->defaults['logo']),
            'favicon' => Setting::get('theme.favicon', $this->defaults['favicon']),
            'site_title' => Setting::get('theme.site_title', $this->defaults['site_title']),
            'site_subtitle' => Setting::get('theme.site_subtitle', $this->defaults['site_subtitle']),
            'search_title' => Setting::get('theme.search_title', $this->defaults['search_title']),
            'search_placeholder' => Setting::get('theme.search_placeholder', $this->defaults['search_placeholder']),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Brand Identity')
                    ->schema([
                        Forms\Components\FileUpload::make('logo')
                            ->label('Site Logo')
                            ->image()
                            ->disk('public')
                            ->directory('theme')
                            ->visibility('public')
                            ->imageEditor()
                            ->helperText('Recommended size: 200x60 pixels'),
                        Forms\Components\FileUpload::make('favicon')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('theme')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/x-icon', 'image/png', 'image/svg+xml'])
                            ->helperText('Recommended size: 32x32 pixels'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Homepage')
                    ->schema([
                        Forms\Components\TextInput::make('site_title')
                            ->label('Site Title')
                            ->placeholder(config('blog.name', config('app.name')))
                            ->helperText('Main title displayed on homepage. Leave empty to use blog name.'),
                        Forms\Components\TextInput::make('site_subtitle')
                            ->label('Site Subtitle')
                            ->placeholder(config('blog.description', 'A Laravel-powered blog'))
                            ->helperText('Subtitle displayed below the main title. Leave empty to use blog description.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Color Scheme')
                    ->schema([
                        Forms\Components\ColorPicker::make('primary_color')
                            ->label('Primary Color')
                            ->helperText('Main brand color used for buttons, links, and accents'),
                        Forms\Components\ColorPicker::make('secondary_color')
                            ->label('Secondary Color')
                            ->helperText('Supporting color for secondary elements'),
                        Forms\Components\ColorPicker::make('accent_color')
                            ->label('Accent Color')
                            ->helperText('Color for highlighting special elements'),
                        Forms\Components\ColorPicker::make('text_color')
                            ->label('Text Color')
                            ->helperText('Primary text color'),
                        Forms\Components\ColorPicker::make('background_color')
                            ->label('Background Color')
                            ->helperText('Main background color'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Search Page')
                    ->schema([
                        Forms\Components\TextInput::make('search_title')
                            ->label('Page Title')
                            ->placeholder('Search')
                            ->helperText('The title displayed on the search page'),
                        Forms\Components\TextInput::make('search_placeholder')
                            ->label('Search Placeholder')
                            ->placeholder('Search posts, categories, tags...')
                            ->helperText('Placeholder text in the search input field'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Footer')
                    ->schema([
                        Forms\Components\Textarea::make('footer_text')
                            ->label('Footer Text')
                            ->rows(3)
                            ->helperText('HTML allowed. Use {year} for current year.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set("theme.{$key}", $value, 'theme');
        }

        Notification::make()
            ->success()
            ->title('Theme settings saved')
            ->body('Your theme settings have been updated successfully.')
            ->send();
    }

    public function resetToDefaults(): void
    {
        foreach ($this->defaults as $key => $value) {
            Setting::set("theme.{$key}", $value, 'theme');
        }

        $this->form->fill($this->defaults);

        Notification::make()
            ->success()
            ->title('Theme reset')
            ->body('Theme settings have been reset to defaults.')
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
            Action::make('resetToDefaults')
                ->label('Reset to Defaults')
                ->color('gray')
                ->requiresConfirmation()
                ->action('resetToDefaults'),
        ];
    }
}
