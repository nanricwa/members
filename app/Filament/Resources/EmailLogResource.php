<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailLogResource\Pages;
use App\Models\EmailLog;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailLogResource extends Resource
{
    protected static ?string $model = EmailLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'メール管理';

    protected static ?string $modelLabel = 'メール送信ログ';

    protected static ?string $pluralModelLabel = 'メール送信ログ';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('メール詳細')
                    ->schema([
                        Infolists\Components\TextEntry::make('member.name')
                            ->label('会員名'),
                        Infolists\Components\TextEntry::make('member.email')
                            ->label('送信先'),
                        Infolists\Components\TextEntry::make('email_type')
                            ->label('種別')
                            ->formatStateUsing(fn (string $state) => match ($state) {
                                'registration_complete' => '登録完了',
                                'newsletter' => 'メルマガ',
                                'automation' => '自動送信',
                                default => $state,
                            }),
                        Infolists\Components\TextEntry::make('subject')
                            ->label('件名'),
                        Infolists\Components\TextEntry::make('body_preview')
                            ->label('本文プレビュー')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('status')
                            ->label('ステータス')
                            ->badge(),
                        Infolists\Components\TextEntry::make('error_message')
                            ->label('エラー')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('sent_at')
                            ->label('送信日時')
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
                Tables\Columns\TextColumn::make('member.name')
                    ->label('会員名')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email_type')
                    ->label('種別')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'registration_complete' => '登録完了',
                        'newsletter' => 'メルマガ',
                        'automation' => '自動送信',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'registration_complete' => 'info',
                        'newsletter' => 'success',
                        'automation' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('subject')
                    ->label('件名')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('status')
                    ->label('ステータス')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'queued' => 'gray',
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'queued' => '待機中',
                        'sent' => '送信済',
                        'failed' => '失敗',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('送信日時')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('作成日')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('email_type')
                    ->label('種別')
                    ->options([
                        'registration_complete' => '登録完了',
                        'newsletter' => 'メルマガ',
                        'automation' => '自動送信',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('ステータス')
                    ->options([
                        'queued' => '待機中',
                        'sent' => '送信済',
                        'failed' => '失敗',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailLogs::route('/'),
            'view' => Pages\ViewEmailLog::route('/{record}'),
        ];
    }
}
