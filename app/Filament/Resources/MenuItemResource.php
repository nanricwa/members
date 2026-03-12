<?php

namespace App\Filament\Resources;

use App\Enums\MenuItemType;
use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationGroup = 'コンテンツ管理';

    protected static ?string $modelLabel = 'メニュー';

    protected static ?string $pluralModelLabel = 'メニュー';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('メニュー項目')
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->label('表示名')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('タイプ')
                            ->options(MenuItemType::class)
                            ->required()
                            ->default('page')
                            ->live(),

                        Forms\Components\Select::make('target_id')
                            ->label('リンク先カテゴリ')
                            ->options(fn () => Category::ordered()->pluck('name', 'id'))
                            ->searchable()
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'category'),

                        Forms\Components\Select::make('target_id')
                            ->label('リンク先ページ')
                            ->options(fn () => Page::orderBy('title')->pluck('title', 'id'))
                            ->searchable()
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'page'),

                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->url()
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'url'),

                        Forms\Components\TextInput::make('icon')
                            ->label('アイコン')
                            ->placeholder('heroicon-o-home')
                            ->maxLength(255),

                        Forms\Components\Select::make('parent_id')
                            ->label('親メニュー')
                            ->relationship('parent', 'label')
                            ->nullable()
                            ->searchable(),
                    ])->columns(2),

                Forms\Components\Section::make('表示設定')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('並び順')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_visible')
                            ->label('表示')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('順')
                    ->sortable()
                    ->width('60px'),

                Tables\Columns\TextColumn::make('label')
                    ->label('表示名')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('タイプ')
                    ->badge(),

                Tables\Columns\TextColumn::make('parent.label')
                    ->label('親メニュー')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label('表示')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit' => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
