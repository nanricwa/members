<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use App\Models\Course;
use App\Models\MemberLessonProgress;
use App\Services\CourseProgressService;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CourseProgressRelationManager extends RelationManager
{
    protected static string $relationship = 'lessonProgress';

    protected static ?string $title = 'コース進捗';

    protected static ?string $modelLabel = 'コース進捗';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('courseLesson.module.course.title')
                    ->label('コース名'),

                Tables\Columns\TextColumn::make('courseLesson.module.title')
                    ->label('モジュール'),

                Tables\Columns\TextColumn::make('courseLesson.page.title')
                    ->label('レッスン'),

                Tables\Columns\IconColumn::make('is_completed')
                    ->label('完了')
                    ->boolean(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('完了日時')
                    ->dateTime('Y/m/d H:i')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('last_accessed_at')
                    ->label('最終アクセス')
                    ->dateTime('Y/m/d H:i')
                    ->placeholder('-'),
            ])
            ->defaultSort('last_accessed_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_completed')
                    ->label('完了状態'),
            ])
            ->actions([])
            ->bulkActions([]);
    }
}
