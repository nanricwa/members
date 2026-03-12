<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use App\Enums\GrantedBy;
use App\Enums\MemberPlanStatus;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PlansRelationManager extends RelationManager
{
    protected static string $relationship = 'plans';

    protected static ?string $title = 'プラン';

    protected static ?string $modelLabel = 'プラン';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('plan_id')
                    ->label('プラン')
                    ->options(Plan::active()->ordered()->pluck('name', 'id'))
                    ->required()
                    ->disabledOn('edit'),

                Forms\Components\Select::make('status')
                    ->label('ステータス')
                    ->options(MemberPlanStatus::class)
                    ->default('active')
                    ->required(),

                Forms\Components\Select::make('granted_by')
                    ->label('付与元')
                    ->options(GrantedBy::class)
                    ->default('admin')
                    ->required(),

                Forms\Components\DateTimePicker::make('started_at')
                    ->label('開始日時')
                    ->default(now()),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('有効期限')
                    ->nullable(),

                Forms\Components\Textarea::make('note')
                    ->label('メモ')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('プラン名'),

                Tables\Columns\TextColumn::make('pivot.status')
                    ->label('ステータス')
                    ->badge()
                    ->formatStateUsing(fn ($state) => MemberPlanStatus::tryFrom($state)?->label() ?? $state)
                    ->color(fn ($state) => MemberPlanStatus::tryFrom($state)?->color() ?? 'gray'),

                Tables\Columns\TextColumn::make('pivot.granted_by')
                    ->label('付与元')
                    ->formatStateUsing(fn ($state) => GrantedBy::tryFrom($state)?->label() ?? $state),

                Tables\Columns\TextColumn::make('pivot.started_at')
                    ->label('開始日')
                    ->dateTime('Y/m/d'),

                Tables\Columns\TextColumn::make('pivot.expires_at')
                    ->label('有効期限')
                    ->dateTime('Y/m/d')
                    ->placeholder('無期限'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('プランを付与')
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('プラン'),
                        Forms\Components\Select::make('status')
                            ->label('ステータス')
                            ->options(MemberPlanStatus::class)
                            ->default('active')
                            ->required(),
                        Forms\Components\Select::make('granted_by')
                            ->label('付与元')
                            ->options(GrantedBy::class)
                            ->default('admin')
                            ->required(),
                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('開始日時')
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('有効期限'),
                        Forms\Components\Textarea::make('note')
                            ->label('メモ')
                            ->rows(2),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('剥奪'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
