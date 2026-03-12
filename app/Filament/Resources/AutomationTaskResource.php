<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AutomationTaskResource\Pages;
use App\Models\AutomationTask;
use App\Models\Plan;
use App\Services\AutomationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AutomationTaskResource extends Resource
{
    protected static ?string $model = AutomationTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = '自動化';

    protected static ?string $modelLabel = '自動タスク';

    protected static ?string $pluralModelLabel = '自動タスク';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本情報')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('タスク名')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('説明')
                            ->rows(2),

                        Forms\Components\Toggle::make('is_active')
                            ->label('有効')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('トリガー条件')
                    ->schema([
                        Forms\Components\Select::make('trigger_type')
                            ->label('トリガータイプ')
                            ->options([
                                'member_registered_days_ago' => '登録からN日後',
                                'plan_expires_in_days' => 'プラン期限がN日以内',
                                'plan_expired' => 'プラン期限切れ',
                                'member_inactive_days' => 'N日間ログインなし',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('trigger_value.days')
                            ->label('日数')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn (Forms\Get $get) => in_array($get('trigger_type'), [
                                'member_registered_days_ago',
                                'plan_expires_in_days',
                                'member_inactive_days',
                            ])),

                        Forms\Components\Select::make('trigger_value.plan_id')
                            ->label('対象プラン')
                            ->options(Plan::pluck('name', 'id'))
                            ->placeholder('全プラン')
                            ->visible(fn (Forms\Get $get) => in_array($get('trigger_type'), [
                                'plan_expires_in_days',
                                'plan_expired',
                            ])),
                    ]),

                Forms\Components\Section::make('アクション')
                    ->schema([
                        Forms\Components\Select::make('action_type')
                            ->label('アクションタイプ')
                            ->options([
                                'send_email' => 'メール送信',
                                'change_plan' => 'プラン変更',
                                'change_status' => 'ステータス変更',
                            ])
                            ->required()
                            ->live(),

                        // メール送信アクション
                        Forms\Components\TextInput::make('action_value.subject')
                            ->label('メール件名')
                            ->visible(fn (Forms\Get $get) => $get('action_type') === 'send_email'),

                        Forms\Components\RichEditor::make('action_value.body_html')
                            ->label('メール本文')
                            ->visible(fn (Forms\Get $get) => $get('action_type') === 'send_email')
                            ->columnSpanFull(),

                        // プラン変更アクション
                        Forms\Components\Select::make('action_value.from_plan_id')
                            ->label('変更元プラン')
                            ->options(Plan::pluck('name', 'id'))
                            ->placeholder('なし（新規付与のみ）')
                            ->visible(fn (Forms\Get $get) => $get('action_type') === 'change_plan'),

                        Forms\Components\Select::make('action_value.to_plan_id')
                            ->label('変更先プラン')
                            ->options(Plan::pluck('name', 'id'))
                            ->required(fn (Forms\Get $get) => $get('action_type') === 'change_plan')
                            ->visible(fn (Forms\Get $get) => $get('action_type') === 'change_plan'),

                        // ステータス変更アクション
                        Forms\Components\Select::make('action_value.status')
                            ->label('変更先ステータス')
                            ->options([
                                'active' => '有効',
                                'suspended' => '停止中',
                            ])
                            ->required(fn (Forms\Get $get) => $get('action_type') === 'change_status')
                            ->visible(fn (Forms\Get $get) => $get('action_type') === 'change_status'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('タスク名')
                    ->searchable(),

                Tables\Columns\TextColumn::make('trigger_type')
                    ->label('トリガー')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'member_registered_days_ago' => '登録N日後',
                        'plan_expires_in_days' => 'プラン期限N日前',
                        'plan_expired' => 'プラン期限切れ',
                        'member_inactive_days' => 'N日間ログインなし',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('action_type')
                    ->label('アクション')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'send_email' => 'メール送信',
                        'change_plan' => 'プラン変更',
                        'change_status' => 'ステータス変更',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'send_email' => 'info',
                        'change_plan' => 'warning',
                        'change_status' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('有効')
                    ->boolean(),

                Tables\Columns\TextColumn::make('last_executed_at')
                    ->label('最終実行')
                    ->dateTime('Y/m/d H:i')
                    ->placeholder('未実行'),

                Tables\Columns\TextColumn::make('logs_count')
                    ->label('実行回数')
                    ->counts('logs'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('有効/無効'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('execute')
                    ->label('手動実行')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('タスクを手動実行')
                    ->modalDescription('この自動タスクを今すぐ実行しますか？')
                    ->action(function (AutomationTask $record) {
                        try {
                            $service = app(AutomationService::class);
                            $count = $service->processTask($record);
                            $record->update(['last_executed_at' => now()]);

                            Notification::make()
                                ->success()
                                ->title('実行完了')
                                ->body("{$count}件のアクションを実行しました。")
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('実行失敗')
                                ->body('エラー: ' . $e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAutomationTasks::route('/'),
            'create' => Pages\CreateAutomationTask::route('/create'),
            'edit' => Pages\EditAutomationTask::route('/{record}/edit'),
        ];
    }
}
