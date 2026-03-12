<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use App\Enums\SubscriptionStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $title = 'サブスクリプション';

    protected static ?string $modelLabel = 'サブスクリプション';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('プラン'),

                Tables\Columns\TextColumn::make('gateway')
                    ->label('決済方法')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('ステータス')
                    ->badge(),

                Tables\Columns\TextColumn::make('current_period_end')
                    ->label('次回更新日')
                    ->dateTime('Y/m/d')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('cancelled_at')
                    ->label('キャンセル日')
                    ->dateTime('Y/m/d')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('開始日')
                    ->dateTime('Y/m/d'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.subscriptions.view', $record)),
            ]);
    }
}
