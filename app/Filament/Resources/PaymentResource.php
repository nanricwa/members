<?php

namespace App\Filament\Resources;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = '決済管理';

    protected static ?string $modelLabel = '決済';

    protected static ?string $pluralModelLabel = '決済履歴';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('決済情報')
                    ->schema([
                        Infolists\Components\TextEntry::make('member.name')
                            ->label('会員名'),
                        Infolists\Components\TextEntry::make('member.email')
                            ->label('メール'),
                        Infolists\Components\TextEntry::make('plan.name')
                            ->label('プラン'),
                        Infolists\Components\TextEntry::make('registrationForm.name')
                            ->label('登録フォーム')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('gateway')
                            ->label('決済方法')
                            ->formatStateUsing(fn ($state) => $state?->label() ?? '-'),
                        Infolists\Components\TextEntry::make('amount')
                            ->label('金額')
                            ->money('JPY'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('ステータス')
                            ->badge(),
                        Infolists\Components\TextEntry::make('description')
                            ->label('説明')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('gateway_payment_id')
                            ->label('ゲートウェイID')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('paid_at')
                            ->label('決済日時')
                            ->dateTime('Y/m/d H:i')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('作成日時')
                            ->dateTime('Y/m/d H:i'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('member.name')
                    ->label('会員名')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('プラン')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gateway')
                    ->label('決済方法')
                    ->badge(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('金額')
                    ->formatStateUsing(fn ($state) => number_format($state) . '円')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('ステータス')
                    ->badge(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('決済日時')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('作成日')
                    ->dateTime('Y/m/d')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('ステータス')
                    ->options(PaymentStatus::class),

                Tables\Filters\SelectFilter::make('gateway')
                    ->label('決済方法')
                    ->options(PaymentGateway::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
