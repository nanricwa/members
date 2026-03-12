<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterResource\Pages;
use App\Models\Newsletter;
use App\Models\Plan;
use App\Services\EmailService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsletterResource extends Resource
{
    protected static ?string $model = Newsletter::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'メール管理';

    protected static ?string $modelLabel = 'メルマガ';

    protected static ?string $pluralModelLabel = 'メルマガ';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('メルマガ内容')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('件名')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\RichEditor::make('body_html')
                            ->label('本文')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('配信設定')
                    ->schema([
                        Forms\Components\Select::make('target_type')
                            ->label('対象')
                            ->options([
                                'all' => '全会員',
                                'plan' => 'プラン指定',
                                'status' => 'ステータス指定',
                            ])
                            ->default('all')
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('target_value')
                            ->label('対象プラン')
                            ->options(Plan::pluck('name', 'id'))
                            ->multiple()
                            ->visible(fn (Forms\Get $get) => $get('target_type') === 'plan'),

                        Forms\Components\Select::make('target_value')
                            ->label('対象ステータス')
                            ->options([
                                'active' => '有効',
                                'pending' => '保留中',
                                'suspended' => '停止中',
                            ])
                            ->multiple()
                            ->visible(fn (Forms\Get $get) => $get('target_type') === 'status'),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('配信予定日時')
                            ->helperText('空欄の場合は手動配信'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label('件名')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('status')
                    ->label('ステータス')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'info',
                        'sending' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'draft' => '下書き',
                        'scheduled' => '予約済',
                        'sending' => '配信中',
                        'sent' => '配信完了',
                        'failed' => '失敗',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('target_type')
                    ->label('対象')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'all' => '全会員',
                        'plan' => 'プラン指定',
                        'status' => 'ステータス指定',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('total_recipients')
                    ->label('配信数')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('sent_count')
                    ->label('成功')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('failed_count')
                    ->label('失敗')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('予約日時')
                    ->dateTime('Y/m/d H:i')
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
                    ->options([
                        'draft' => '下書き',
                        'scheduled' => '予約済',
                        'sending' => '配信中',
                        'sent' => '配信完了',
                        'failed' => '失敗',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('send')
                    ->label('配信開始')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Newsletter $record) => in_array($record->status, ['draft', 'scheduled']))
                    ->requiresConfirmation()
                    ->modalHeading('メルマガを配信')
                    ->modalDescription('この内容で対象会員にメルマガを配信しますか？')
                    ->action(function (Newsletter $record) {
                        try {
                            app(EmailService::class)->sendNewsletter($record);

                            Notification::make()
                                ->success()
                                ->title('配信を開始しました')
                                ->body('メルマガの配信をキューに追加しました。')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('配信失敗')
                                ->body('エラー: ' . $e->getMessage())
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('preview')
                    ->label('プレビュー')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (Newsletter $record) => $record->subject)
                    ->modalContent(fn (Newsletter $record) => view('emails.newsletter', ['bodyHtml' => $record->body_html]))
                    ->modalSubmitAction(false),
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
            'index' => Pages\ListNewsletters::route('/'),
            'create' => Pages\CreateNewsletter::route('/create'),
            'edit' => Pages\EditNewsletter::route('/{record}/edit'),
        ];
    }
}
