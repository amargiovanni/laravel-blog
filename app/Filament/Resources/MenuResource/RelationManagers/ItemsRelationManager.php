<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuResource\RelationManagers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'allItems';

    protected static ?string $title = 'Menu Items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('parent_id')
                    ->label('Parent Item')
                    ->options(fn () => $this->getOwnerRecord()
                        ->allItems()
                        ->whereNull('parent_id')
                        ->pluck('label', 'id'))
                    ->searchable()
                    ->placeholder('None (top level)'),
                Forms\Components\Select::make('linkable_type')
                    ->label('Link Type')
                    ->options([
                        '' => 'Custom URL',
                        Page::class => 'Page',
                        Post::class => 'Post',
                        Category::class => 'Category',
                        Tag::class => 'Tag',
                    ])
                    ->default('')
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('linkable_id', null)),
                Forms\Components\Select::make('linkable_id')
                    ->label('Select Content')
                    ->options(function (Forms\Get $get) {
                        $type = $get('linkable_type');

                        if (! $type) {
                            return [];
                        }

                        return match ($type) {
                            Page::class => Page::published()->pluck('title', 'id'),
                            Post::class => Post::published()->pluck('title', 'id'),
                            Category::class => Category::pluck('name', 'id'),
                            Tag::class => Tag::pluck('name', 'id'),
                            default => [],
                        };
                    })
                    ->searchable()
                    ->visible(fn (Forms\Get $get) => ! empty($get('linkable_type'))),
                Forms\Components\TextInput::make('url')
                    ->label('Custom URL')
                    ->url()
                    ->visible(fn (Forms\Get $get) => empty($get('linkable_type')))
                    ->placeholder('https://example.com'),
                Forms\Components\Select::make('target')
                    ->options([
                        '_self' => 'Same Window',
                        '_blank' => 'New Tab',
                    ])
                    ->default('_self')
                    ->required(),
                Forms\Components\TextInput::make('css_class')
                    ->label('CSS Class')
                    ->maxLength(255)
                    ->placeholder('e.g., btn btn-primary'),
                Forms\Components\TextInput::make('title_attribute')
                    ->label('Title Attribute')
                    ->maxLength(255)
                    ->helperText('Tooltip text on hover'),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.label')
                    ->label('Parent')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('linkable_type')
                    ->label('Type')
                    ->formatStateUsing(function (?string $state, MenuItem $record): string {
                        if ($record->url) {
                            return 'Custom URL';
                        }

                        return match ($state) {
                            Page::class => 'Page',
                            Post::class => 'Post',
                            Category::class => 'Category',
                            Tag::class => 'Tag',
                            default => 'Unknown',
                        };
                    })
                    ->badge()
                    ->color(fn (?string $state, MenuItem $record): string => match (true) {
                        $record->url !== null => 'gray',
                        $state === Page::class => 'success',
                        $state === Post::class => 'info',
                        $state === Category::class => 'warning',
                        $state === Tag::class => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('target')
                    ->formatStateUsing(fn (string $state): string => $state === '_blank' ? 'New Tab' : 'Same Window')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Item'),
                Tables\Actions\Action::make('addPage')
                    ->label('Add Page')
                    ->icon('heroicon-o-document')
                    ->form([
                        Forms\Components\Select::make('page_id')
                            ->label('Select Page')
                            ->options(Page::published()->pluck('title', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $page = Page::find($data['page_id']);
                        $maxOrder = $this->getOwnerRecord()->allItems()->max('sort_order') ?? -1;

                        $this->getOwnerRecord()->allItems()->create([
                            'label' => $page->title,
                            'linkable_type' => Page::class,
                            'linkable_id' => $page->id,
                            'sort_order' => $maxOrder + 1,
                        ]);
                    }),
                Tables\Actions\Action::make('addCategory')
                    ->label('Add Category')
                    ->icon('heroicon-o-folder')
                    ->form([
                        Forms\Components\Select::make('category_id')
                            ->label('Select Category')
                            ->options(Category::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $category = Category::find($data['category_id']);
                        $maxOrder = $this->getOwnerRecord()->allItems()->max('sort_order') ?? -1;

                        $this->getOwnerRecord()->allItems()->create([
                            'label' => $category->name,
                            'linkable_type' => Category::class,
                            'linkable_id' => $category->id,
                            'sort_order' => $maxOrder + 1,
                        ]);
                    }),
                Tables\Actions\Action::make('addCustomLink')
                    ->label('Add Custom Link')
                    ->icon('heroicon-o-link')
                    ->form([
                        Forms\Components\TextInput::make('label')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->url()
                            ->placeholder('https://example.com'),
                        Forms\Components\Select::make('target')
                            ->options([
                                '_self' => 'Same Window',
                                '_blank' => 'New Tab',
                            ])
                            ->default('_self'),
                    ])
                    ->action(function (array $data): void {
                        $maxOrder = $this->getOwnerRecord()->allItems()->max('sort_order') ?? -1;

                        $this->getOwnerRecord()->allItems()->create([
                            'label' => $data['label'],
                            'url' => $data['url'],
                            'target' => $data['target'] ?? '_self',
                            'sort_order' => $maxOrder + 1,
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
