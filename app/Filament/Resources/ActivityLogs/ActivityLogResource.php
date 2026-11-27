<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\ActivityLogs\Pages\ManageActivityLogs;
use App\Models\ActivityLog;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityLogResource extends AdminResource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $modelLabel = 'سجل نشاط';

    protected static ?string $pluralModelLabel = 'سجل النشاط';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')->columns([
            TextColumn::make('created_at')->label('الوقت')->dateTime()->sortable(),
            TextColumn::make('description')->label('الإجراء')->searchable(),
            TextColumn::make('causer_id')->label('المنفّذ (user id)'),
            TextColumn::make('subject_type')->label('على')->formatStateUsing(fn ($s) => class_basename((string) $s)),
            TextColumn::make('subject_id')->label('رقم'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageActivityLogs::route('/')];
    }
}
