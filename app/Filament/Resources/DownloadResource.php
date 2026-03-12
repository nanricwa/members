<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DownloadResource\Pages;
use App\Models\Download;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DownloadResource extends Resource
{
    protected static ?string $model = Download::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'コンテンツ管理';

    protected static ?string $modelLabel = 'ダウンロード';

    protected static ?string $pluralModelLabel = 'ダウンロード';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本情報')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('タイトル')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('説明')
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('ファイル')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('ファイル')
                            ->required()
                            ->directory('downloads')
                            ->disk('local')
                            ->visibility('private')
                            ->maxSize(102400) // 100MB
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    // ファイル情報は保存時にmodelイベントで設定
                                }
                            }),

                        Forms\Components\TextInput::make('original_filename')
                            ->label('表示ファイル名')
                            ->maxLength(255)
                            ->helperText('ダウンロード時のファイル名'),
                    ]),

                Forms\Components\Section::make('制限設定')
                    ->schema([
                        Forms\Components\TextInput::make('download_limit')
                            ->label('ダウンロード上限')
                            ->numeric()
                            ->nullable()
                            ->helperText('空欄の場合は無制限'),

                        Forms\Components\TextInput::make('total_downloads')
                            ->label('総ダウンロード数')
                            ->numeric()
                            ->default(0)
                            ->disabled(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('有効')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('タイトル')
                    ->searchable(),

                Tables\Columns\TextColumn::make('original_filename')
                    ->label('ファイル名')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('formatted_file_size')
                    ->label('サイズ'),

                Tables\Columns\TextColumn::make('total_downloads')
                    ->label('DL数')
                    ->sortable(),

                Tables\Columns\TextColumn::make('download_limit')
                    ->label('上限')
                    ->placeholder('無制限'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('有効')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('有効'),
            ])
            ->actions([
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
            'index' => Pages\ListDownloads::route('/'),
            'create' => Pages\CreateDownload::route('/create'),
            'edit' => Pages\EditDownload::route('/{record}/edit'),
        ];
    }
}
