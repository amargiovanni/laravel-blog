<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Content')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state): void {
                                        if (! $get('slug') && $state) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Page::class, 'slug', ignoreRecord: true)
                                    ->rules(['alpha_dash']),
                                Forms\Components\RichEditor::make('content')
                                    ->columnSpanFull()
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('page-attachments'),
                                Forms\Components\Textarea::make('excerpt')
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->helperText('Leave empty to auto-generate from content'),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('SEO')
                            ->schema([
                                Forms\Components\TextInput::make('meta_title')
                                    ->maxLength(60)
                                    ->helperText('Recommended: 50-60 characters'),
                                Forms\Components\Textarea::make('meta_description')
                                    ->maxLength(160)
                                    ->rows(3)
                                    ->helperText('Recommended: 150-160 characters'),
                                Forms\Components\TextInput::make('focus_keyword')
                                    ->maxLength(100),
                            ])
                            ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'scheduled' => 'Scheduled',
                                        'published' => 'Published',
                                    ])
                                    ->required()
                                    ->default('draft')
                                    ->live(),
                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Publish date')
                                    ->visible(fn (Forms\Get $get): bool => in_array($get('status'), ['scheduled', 'published'])),
                                Forms\Components\Select::make('author_id')
                                    ->relationship('author', 'name')
                                    ->required()
                                    ->default(fn () => auth()->id())
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Forms\Components\Section::make('Hierarchy')
                            ->schema([
                                Forms\Components\Select::make('parent_id')
                                    ->label('Parent Page')
                                    ->relationship(
                                        'parent',
                                        'title',
                                        fn (Builder $query, ?Page $record) => $query
                                            ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('No parent (root level)'),
                                Forms\Components\TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Lower numbers appear first'),
                            ]),

                        Forms\Components\Section::make('Settings')
                            ->schema([
                                Forms\Components\Select::make('template')
                                    ->options([
                                        'default' => 'Default',
                                        'full-width' => 'Full Width',
                                        'sidebar' => 'With Sidebar',
                                        'landing' => 'Landing Page',
                                    ])
                                    ->default('default')
                                    ->required(),
                                Forms\Components\Select::make('featured_image_id')
                                    ->label('Featured image')
                                    ->relationship('featuredImage', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('parent.title')
                    ->label('Parent')
                    ->sortable()
                    ->placeholder('Root'),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Author')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'published' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('template')
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'published' => 'Published',
                    ]),
                Tables\Filters\SelectFilter::make('author')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('template')
                    ->options([
                        'default' => 'Default',
                        'full-width' => 'Full Width',
                        'sidebar' => 'With Sidebar',
                        'landing' => 'Landing Page',
                    ]),
                Tables\Filters\SelectFilter::make('parent')
                    ->relationship('parent', 'title')
                    ->searchable()
                    ->preload()
                    ->label('Parent Page'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'view' => Pages\ViewPage::route('/{record}'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'content', 'excerpt'];
    }
}
