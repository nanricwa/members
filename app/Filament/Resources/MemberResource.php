<?php

namespace App\Filament\Resources;

use App\Enums\MemberPlanStatus;
use App\Enums\MemberStatus;
use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\CustomField;
use App\Models\Member;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = '会員管理';

    protected static ?string $modelLabel = '会員';

    protected static ?string $pluralModelLabel = '会員';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本情報')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('メールアドレス')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('パスワード')
                            ->password()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name')
                            ->label('氏名')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name_kana')
                            ->label('氏名（カナ）')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('ステータス')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('ステータス')
                            ->options(MemberStatus::class)
                            ->required()
                            ->default('active'),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('メール確認日時'),

                        Forms\Components\Textarea::make('note')
                            ->label('メモ')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('カスタムフィールド')
                    ->schema(fn () => static::getCustomFieldFormComponents())
                    ->visible(fn () => CustomField::active()->exists()),
            ]);
    }

    public static function getCustomFieldFormComponents(): array
    {
        $fields = CustomField::active()->ordered()->get();
        $components = [];

        foreach ($fields as $field) {
            $component = match ($field->type->value) {
                'text' => Forms\Components\TextInput::make("custom_fields.{$field->slug}")
                    ->label($field->name),
                'textarea' => Forms\Components\Textarea::make("custom_fields.{$field->slug}")
                    ->label($field->name)
                    ->rows(3),
                'select' => Forms\Components\Select::make("custom_fields.{$field->slug}")
                    ->label($field->name)
                    ->options(array_combine($field->options ?? [], $field->options ?? [])),
                'checkbox' => Forms\Components\CheckboxList::make("custom_fields.{$field->slug}")
                    ->label($field->name)
                    ->options(array_combine($field->options ?? [], $field->options ?? [])),
                'radio' => Forms\Components\Radio::make("custom_fields.{$field->slug}")
                    ->label($field->name)
                    ->options(array_combine($field->options ?? [], $field->options ?? [])),
                'date' => Forms\Components\DatePicker::make("custom_fields.{$field->slug}")
                    ->label($field->name),
                'number' => Forms\Components\TextInput::make("custom_fields.{$field->slug}")
                    ->label($field->name)
                    ->numeric(),
                default => Forms\Components\TextInput::make("custom_fields.{$field->slug}")
                    ->label($field->name),
            };

            if ($field->is_required) {
                $component->required();
            }

            $components[] = $component;
        }

        return $components;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('氏名')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('メール')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('ステータス')
                    ->badge(),

                Tables\Columns\TextColumn::make('plans_count')
                    ->label('プラン数')
                    ->counts('activePlans')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('最終ログイン')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('登録日')
                    ->dateTime('Y/m/d')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('ステータス')
                    ->options(MemberStatus::class),

                Tables\Filters\SelectFilter::make('plan')
                    ->label('プラン')
                    ->relationship('activePlans', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('changeStatus')
                        ->label('ステータス一括変更')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('ステータス')
                                ->options(MemberStatus::class)
                                ->required(),
                        ])
                        ->action(function ($records, array $data): void {
                            $records->each(fn ($record) => $record->update(['status' => $data['status']]));
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PlansRelationManager::class,
            RelationManagers\PaymentsRelationManager::class,
            RelationManagers\SubscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
