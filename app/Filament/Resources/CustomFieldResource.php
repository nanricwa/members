<?php

namespace App\Filament\Resources;

use App\Enums\CustomFieldType;
use App\Filament\Resources\CustomFieldResource\Pages;
use App\Models\CustomField;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomFieldResource extends Resource
{
    protected static ?string $model = CustomField::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = '基本設定';

    protected static ?string $modelLabel = 'カスタムフィールド';

    protected static ?string $pluralModelLabel = 'カスタムフィールド';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本情報')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('フィールド名')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->label('スラッグ')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->alphaDash(),

                        Forms\Components\Select::make('type')
                            ->label('フィールドタイプ')
                            ->options(CustomFieldType::class)
                            ->required()
                            ->default('text')
                            ->live(),

                        Forms\Components\TagsInput::make('options')
                            ->label('選択肢')
                            ->helperText('選択肢をEnterで追加')
                            ->visible(fn (Forms\Get $get): bool => in_array($get('type'), ['select', 'radio', 'checkbox'])),
                    ])->columns(2),

                Forms\Components\Section::make('設定')
                    ->schema([
                        Forms\Components\Toggle::make('is_required')
                            ->label('必須')
                            ->default(false),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('並び順')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('有効')
                            ->default(true),
                    ])->columns(3),
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
                    ->label('フィールド名')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('スラッグ')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('type')
                    ->label('タイプ')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('必須')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('有効')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('タイプ')
                    ->options(CustomFieldType::class),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('有効'),
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
            'index' => Pages\ListCustomFields::route('/'),
            'create' => Pages\CreateCustomField::route('/create'),
            'edit' => Pages\EditCustomField::route('/{record}/edit'),
        ];
    }
}
