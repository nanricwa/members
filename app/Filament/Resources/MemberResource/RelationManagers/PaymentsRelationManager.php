<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use App\Enums\PaymentStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = '決済履歴';

    protected static ?string $modelLabel = '決済';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('プラン'),

                Tables\Columns\TextColumn::make('gateway')
                    ->label('決済方法')
                    ->badge(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('金額')
                    ->formatStateUsing(fn ($state) => number_format($state) . '円'),

                Tables\Columns\TextColumn::make('status')
                    ->label('ステータス')
                    ->badge(),

                Tables\Columns\TextColumn::make('description')
                    ->label('説明')
                    ->limit(30)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('決済日時')
                    ->dateTime('Y/m/d H:i')
                    ->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.payments.view', $record)),
            ]);
    }
}
