<?php

namespace App\Filament\Resources;

use App\Enums\ContentBlockType;
use App\Enums\PageType;
use App\Enums\VideoProvider;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Category;
use App\Models\Download;
use App\Models\Page;
use App\Models\PageContent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'コンテンツ管理';

    protected static ?string $modelLabel = 'ページ';

    protected static ?string $pluralModelLabel = 'ページ';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('ページ設定')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('基本情報')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('タイトル')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('slug')
                                    ->label('スラッグ')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->alphaDash(),

                                Forms\Components\Select::make('category_id')
                                    ->label('カテゴリ')
                                    ->relationship('category', 'name')
                                    ->nullable()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('type')
                                    ->label('ページタイプ')
                                    ->options(PageType::class)
                                    ->default('text')
                                    ->required(),

                                Forms\Components\Textarea::make('excerpt')
                                    ->label('概要')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('コンテンツ')
                            ->schema([
                                Forms\Components\Repeater::make('contents')
                                    ->label('コンテンツブロック')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label('ブロックタイプ')
                                            ->options(ContentBlockType::class)
                                            ->required()
                                            ->live()
                                            ->default('text'),

                                        Forms\Components\RichEditor::make('body')
                                            ->label('テキスト')
                                            ->visible(fn (Forms\Get $get): bool => in_array($get('type'), ['text', 'heading', 'html']))
                                            ->columnSpanFull(),

                                        Forms\Components\Group::make([
                                            Forms\Components\TextInput::make('video_url')
                                                ->label('動画URL')
                                                ->url()
                                                ->placeholder('https://www.youtube.com/watch?v=...'),

                                            Forms\Components\Select::make('video_provider')
                                                ->label('プロバイダ')
                                                ->options(VideoProvider::class),
                                        ])
                                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'video')
                                            ->columns(2),

                                        Forms\Components\Select::make('download_id')
                                            ->label('ダウンロードファイル')
                                            ->options(Download::active()->pluck('title', 'id'))
                                            ->searchable()
                                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'download'),
                                    ])
                                    ->orderColumn('sort_order')
                                    ->collapsible()
                                    ->cloneable()
                                    ->itemLabel(fn (array $state): ?string => ContentBlockType::tryFrom($state['type'] ?? '')?->label() ?? 'ブロック'),
                            ]),

                        Forms\Components\Tabs\Tab::make('公開設定')
                            ->schema([
                                Forms\Components\Toggle::make('is_published')
                                    ->label('公開')
                                    ->default(false),

                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('公開開始日時'),

                                Forms\Components\DateTimePicker::make('unpublished_at')
                                    ->label('公開終了日時'),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('並び順')
                                    ->numeric()
                                    ->default(0),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('アクセス制御')
                            ->schema([
                                Forms\Components\CheckboxList::make('plans')
                                    ->label('閲覧可能なプラン')
                                    ->relationship('plans', 'name')
                                    ->helperText('選択しない場合、カテゴリのプラン設定を継承します'),
                            ]),

                        Forms\Components\Tabs\Tab::make('SEO')
                            ->schema([
                                Forms\Components\TextInput::make('meta_title')
                                    ->label('メタタイトル')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('meta_description')
                                    ->label('メタディスクリプション')
                                    ->rows(2),

                                Forms\Components\FileUpload::make('thumbnail')
                                    ->label('サムネイル')
                                    ->image()
                                    ->directory('thumbnails'),
                            ]),
                    ])->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('title')
                    ->label('タイトル')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('カテゴリ')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('type')
                    ->label('タイプ')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('公開')
                    ->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('公開日')
                    ->dateTime('Y/m/d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('contents_count')
                    ->label('ブロック数')
                    ->counts('contents'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('カテゴリ')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('type')
                    ->label('タイプ')
                    ->options(PageType::class),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('公開'),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
