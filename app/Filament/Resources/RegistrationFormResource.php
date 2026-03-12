<?php

namespace App\Filament\Resources;

use App\Enums\FormType;
use App\Enums\PaymentGateway;
use App\Filament\Resources\RegistrationFormResource\Pages;
use App\Models\CustomField;
use App\Models\RegistrationForm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RegistrationFormResource extends Resource
{
    protected static ?string $model = RegistrationForm::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = '会員管理';

    protected static ?string $modelLabel = '登録フォーム';

    protected static ?string $pluralModelLabel = '登録フォーム';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('フォーム設定')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('基本設定')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('フォーム名')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('slug')
                                    ->label('スラッグ')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->alphaDash()
                                    ->helperText('登録URL: /register/{スラッグ}'),

                                Forms\Components\Select::make('type')
                                    ->label('フォームタイプ')
                                    ->options(FormType::class)
                                    ->required()
                                    ->default('free')
                                    ->live(),

                                Forms\Components\Select::make('plan_id')
                                    ->label('付与するプラン')
                                    ->relationship('plan', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Textarea::make('description')
                                    ->label('説明')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\CheckboxList::make('customFields')
                                    ->label('フォームに含めるカスタムフィールド')
                                    ->relationship('customFields', 'name')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('レイアウト')
                            ->schema([
                                Forms\Components\FileUpload::make('header_image')
                                    ->label('ヘッダー画像')
                                    ->image()
                                    ->directory('form-headers'),

                                Forms\Components\RichEditor::make('body_html')
                                    ->label('フォーム説明文（HTML）')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('button_text')
                                    ->label('ボタンテキスト')
                                    ->default('登録する')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('custom_css')
                                    ->label('カスタムCSS')
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make('決済設定')
                            ->schema([
                                Forms\Components\Select::make('payment_gateway')
                                    ->label('決済ゲートウェイ')
                                    ->options(PaymentGateway::class)
                                    ->default('none'),

                                Forms\Components\TextInput::make('amount')
                                    ->label('金額')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('円'),

                                Forms\Components\TextInput::make('trial_days')
                                    ->label('トライアル日数')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('日'),
                            ])->columns(3)
                            ->visible(fn (Forms\Get $get): bool => in_array($get('type'), ['paid_once', 'paid_recurring'])),

                        Forms\Components\Tabs\Tab::make('制限設定')
                            ->schema([
                                Forms\Components\TextInput::make('capacity')
                                    ->label('定員')
                                    ->numeric()
                                    ->nullable()
                                    ->helperText('空欄の場合は無制限'),

                                Forms\Components\DateTimePicker::make('opens_at')
                                    ->label('受付開始日時'),

                                Forms\Components\DateTimePicker::make('closes_at')
                                    ->label('受付終了日時'),
                            ])->columns(3),

                        Forms\Components\Tabs\Tab::make('完了設定')
                            ->schema([
                                Forms\Components\Textarea::make('thanks_message')
                                    ->label('完了メッセージ')
                                    ->rows(3),

                                Forms\Components\TextInput::make('redirect_url')
                                    ->label('リダイレクトURL')
                                    ->url()
                                    ->placeholder('https://...')
                                    ->helperText('設定すると完了メッセージの代わりにリダイレクト'),

                                Forms\Components\TextInput::make('completion_email_subject')
                                    ->label('完了メール件名')
                                    ->maxLength(255),

                                Forms\Components\RichEditor::make('completion_email_body')
                                    ->label('完了メール本文')
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ])->columnSpanFull(),

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
                    ->label('フォーム名')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->formatStateUsing(fn ($state) => "/register/{$state}")
                    ->color('primary')
                    ->copyable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('タイプ')
                    ->badge(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('付与プラン'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('金額')
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state) . '円' : '-'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('有効')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('タイプ')
                    ->options(FormType::class),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('有効'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('プレビュー')
                    ->icon('heroicon-o-eye')
                    ->url(fn (RegistrationForm $record): string => "/register/{$record->slug}")
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListRegistrationForms::route('/'),
            'create' => Pages\CreateRegistrationForm::route('/create'),
            'edit' => Pages\EditRegistrationForm::route('/{record}/edit'),
        ];
    }
}
