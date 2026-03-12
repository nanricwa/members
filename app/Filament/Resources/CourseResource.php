<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'コンテンツ管理';

    protected static ?string $modelLabel = 'コース';

    protected static ?string $pluralModelLabel = 'コース';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('コース設定')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('基本情報')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('コース名')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('slug')
                                    ->label('スラッグ')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->alphaDash(),

                                Forms\Components\Textarea::make('description')
                                    ->label('コース説明')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('thumbnail')
                                    ->label('サムネイル')
                                    ->image()
                                    ->directory('course-thumbnails'),

                                Forms\Components\TextInput::make('estimated_minutes')
                                    ->label('想定所要時間（分）')
                                    ->numeric()
                                    ->placeholder('例: 120'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('モジュール・レッスン')
                            ->schema([
                                Forms\Components\Repeater::make('modules')
                                    ->label('モジュール（章）')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('モジュール名')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\Textarea::make('description')
                                            ->label('説明')
                                            ->rows(2),

                                        Forms\Components\Repeater::make('lessons')
                                            ->label('レッスン')
                                            ->relationship()
                                            ->schema([
                                                Forms\Components\Select::make('page_id')
                                                    ->label('ページ')
                                                    ->options(Page::pluck('title', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),

                                                Forms\Components\TextInput::make('estimated_minutes')
                                                    ->label('所要時間（分）')
                                                    ->numeric()
                                                    ->placeholder('例: 15'),
                                            ])
                                            ->orderColumn('sort_order')
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string =>
                                                isset($state['page_id']) ? Page::find($state['page_id'])?->title : 'レッスン'
                                            )
                                            ->columns(2),
                                    ])
                                    ->orderColumn('sort_order')
                                    ->collapsible()
                                    ->cloneable()
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'モジュール'),
                            ]),

                        Forms\Components\Tabs\Tab::make('公開設定')
                            ->schema([
                                Forms\Components\Toggle::make('is_published')
                                    ->label('公開')
                                    ->default(false),

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
                                    ->helperText('選択しない場合、すべての会員がアクセス可能です'),
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
                    ->label('コース名')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('modules_count')
                    ->label('モジュール数')
                    ->counts('modules'),

                Tables\Columns\TextColumn::make('estimated_minutes')
                    ->label('所要時間')
                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state}分" : '-'),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('公開')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('作成日')
                    ->dateTime('Y/m/d')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
