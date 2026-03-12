<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AutomationLogResource\Pages;
use App\Models\AutomationLog;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AutomationLogResource extends Resource
{
    protected static ?string $model = AutomationLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = '自動化';

    protected static ?string $modelLabel = '自動化ログ';

    protected static ?string $pluralModelLabel = '自動化ログ';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('実行詳細')
                    ->schema([
                        Infolists\Components\TextEntry::make('automationTask.name')
                            ->label('タスク名'),
                        Infolists\Components\TextEntry::make('member.name')
                            ->label('会員名')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('member.email')
                            ->label('メール')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('action_type')
                            ->label('アクション')
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'send_email' => 'メール送信',
                                'change_plan' => 'プラン変更',
                                'change_status' => 'ステータス変更',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('status')
                            ->label('ステータス')
                            ->badge(),
                        Infolists\Components\TextEntry::make('action_detail')
                            ->label('アクション詳細')
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $state)
                            ->placeholder('-')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('error_message')
                            ->label('エラー')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('executed_at')
                            ->label('実行日時')
                            ->dateTime('Y/m/d H:i:s'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('automationTask.name')
                    ->label('タスク名')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('member.name')
                    ->label('会員名')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('action_type')
                    ->label('アクション')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'send_email' => 'メール送信',
                        'change_plan' => 'プラン変更',
                        'change_status' => 'ステータス変更',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('ステータス')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'skipped' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'success' => '成功',
                        'failed' => '失敗',
                        'skipped' => 'スキップ',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('executed_at')
                    ->label('実行日時')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->defaultSort('executed_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('ステータス')
                    ->options([
                        'success' => '成功',
                        'failed' => '失敗',
                        'skipped' => 'スキップ',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAutomationLogs::route('/'),
            'view' => Pages\ViewAutomationLog::route('/{record}'),
        ];
    }
}
