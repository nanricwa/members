<?php

namespace App\Filament\Resources;

use App\Enums\PaymentGateway;
use App\Enums\SubscriptionStatus;
use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use App\Services\Payment\PaymentGatewayFactory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = '決済管理';

    protected static ?string $modelLabel = 'サブスクリプション';

    protected static ?string $pluralModelLabel = 'サブスクリプション';

    protected static ?int $navigationSort = 2;

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
                Infolists\Components\Section::make('サブスクリプション情報')
                    ->schema([
                        Infolists\Components\TextEntry::make('member.name')
                            ->label('会員名'),
                        Infolists\Components\TextEntry::make('member.email')
                            ->label('メール'),
                        Infolists\Components\TextEntry::make('plan.name')
                            ->label('プラン'),
                        Infolists\Components\TextEntry::make('gateway')
                            ->label('決済方法')
                            ->formatStateUsing(fn ($state) => $state?->label() ?? '-'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('ステータス')
                            ->badge(),
                        Infolists\Components\TextEntry::make('gateway_subscription_id')
                            ->label('ゲートウェイID')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('current_period_start')
                            ->label('現在期間開始')
                            ->dateTime('Y/m/d H:i')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('current_period_end')
                            ->label('現在期間終了')
                            ->dateTime('Y/m/d H:i')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('trial_ends_at')
                            ->label('トライアル終了')
                            ->dateTime('Y/m/d H:i')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('cancelled_at')
                            ->label('キャンセル日')
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

                Tables\Columns\TextColumn::make('status')
                    ->label('ステータス')
                    ->badge(),

                Tables\Columns\TextColumn::make('current_period_end')
                    ->label('次回更新日')
                    ->dateTime('Y/m/d')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('cancelled_at')
                    ->label('キャンセル日')
                    ->dateTime('Y/m/d')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('開始日')
                    ->dateTime('Y/m/d')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('ステータス')
                    ->options(SubscriptionStatus::class),

                Tables\Filters\SelectFilter::make('gateway')
                    ->label('決済方法')
                    ->options(PaymentGateway::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('cancel')
                    ->label('キャンセル')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Subscription $record) => $record->isActive() && !$record->cancelled_at)
                    ->requiresConfirmation()
                    ->modalHeading('サブスクリプションをキャンセル')
                    ->modalDescription('このサブスクリプションをキャンセルしますか？現在の期間終了時にプランが停止されます。')
                    ->action(function (Subscription $record) {
                        try {
                            $gateway = PaymentGatewayFactory::create($record->gateway);
                            $cancelled = $gateway->cancelSubscription($record);

                            if ($cancelled) {
                                $record->update(['cancelled_at' => now()]);
                                Notification::make()
                                    ->success()
                                    ->title('キャンセルしました')
                                    ->body('サブスクリプションのキャンセルをリクエストしました。')
                                    ->send();
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title('キャンセル失敗')
                                    ->body('キャンセル処理に失敗しました。')
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('エラー')
                                ->body('キャンセル処理中にエラーが発生しました: ' . $e->getMessage())
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'view' => Pages\ViewSubscription::route('/{record}'),
        ];
    }
}
