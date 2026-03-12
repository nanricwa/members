<?php

namespace App\Filament\Resources;

use App\Enums\PlanType;
use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = '基本設定';

    protected static ?string $modelLabel = 'プラン';

    protected static ?string $pluralModelLabel = 'プラン';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本情報')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('プラン名')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->label('スラッグ')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->alphaDash(),

                        Forms\Components\Select::make('type')
                            ->label('タイプ')
                            ->options(PlanType::class)
                            ->required()
                            ->default('free'),

                        Forms\Components\Textarea::make('description')
                            ->label('説明')
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('料金設定')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('金額')
                            ->numeric()
                            ->default(0)
                            ->suffix('円'),

                        Forms\Components\TextInput::make('currency')
                            ->label('通貨')
                            ->default('JPY')
                            ->maxLength(3),

                        Forms\Components\TextInput::make('trial_days')
                            ->label('トライアル日数')
                            ->numeric()
                            ->default(0)
                            ->suffix('日'),

                        Forms\Components\TextInput::make('duration_days')
                            ->label('有効期間')
                            ->numeric()
                            ->nullable()
                            ->suffix('日')
                            ->helperText('空欄の場合は無期限'),
                    ])->columns(2),

                Forms\Components\Section::make('表示設定')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('並び順')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('有効')
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
                    ->label('プラン名')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('スラッグ')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('type')
                    ->label('タイプ')
                    ->badge(),

                Tables\Columns\TextColumn::make('price')
                    ->label('金額')
                    ->formatStateUsing(fn ($state) => number_format($state) . '円'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('有効')
                    ->boolean(),

                Tables\Columns\TextColumn::make('members_count')
                    ->label('会員数')
                    ->counts('members'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('タイプ')
                    ->options(PlanType::class),

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
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
