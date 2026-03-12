<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'コンテンツ管理';

    protected static ?string $modelLabel = 'カテゴリ';

    protected static ?string $pluralModelLabel = 'カテゴリ';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本情報')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('カテゴリ名')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->label('スラッグ')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->alphaDash(),

                        Forms\Components\Select::make('parent_id')
                            ->label('親カテゴリ')
                            ->relationship('parent', 'name')
                            ->nullable()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('icon')
                            ->label('アイコン')
                            ->placeholder('heroicon-o-book-open')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('説明')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('アクセス制御')
                    ->schema([
                        Forms\Components\CheckboxList::make('plans')
                            ->label('閲覧可能なプラン')
                            ->relationship('plans', 'name')
                            ->helperText('選択しない場合、すべての会員がアクセス可能です'),
                    ]),

                Forms\Components\Section::make('表示設定')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('並び順')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_published')
                            ->label('公開')
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

                Tables\Columns\TextColumn::make('name')
                    ->label('カテゴリ名')
                    ->searchable()
                    ->description(fn (Category $record): ?string => $record->parent?->name ? '└ ' . $record->parent->name : null),

                Tables\Columns\TextColumn::make('slug')
                    ->label('スラッグ')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('pages_count')
                    ->label('ページ数')
                    ->counts('pages'),

                Tables\Columns\TextColumn::make('plans.name')
                    ->label('プラン制限')
                    ->badge()
                    ->placeholder('制限なし'),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('公開')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('公開'),

                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('親カテゴリ')
                    ->relationship('parent', 'name')
                    ->placeholder('すべて'),
            ])
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
