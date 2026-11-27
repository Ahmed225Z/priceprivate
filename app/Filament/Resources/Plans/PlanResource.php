<?php

declare(strict_types=1);

namespace App\Filament\Resources\Plans;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\Plans\Pages\ManagePlans;
use App\Models\ActivityLog;
use App\Models\Plan;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlanResource extends AdminResource
{
    protected static ?string $model = Plan::class;

    protected static ?string $modelLabel = 'خطة';

    protected static ?string $pluralModelLabel = 'الخطط';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('الاسم')->required(),
            TextInput::make('price')->label('السعر')->numeric()->minValue(0)->required(),
            TextInput::make('duration_days')->label('المدة (أيام)')->numeric()->minValue(1)->required(),
            TextInput::make('product_limit')->label('حد المنتجات (فاضي = غير محدود)')->numeric()->minValue(0),
            Toggle::make('is_active')->label('مفعّلة'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code'),
            TextColumn::make('name')->label('الاسم'),
            TextColumn::make('price')->label('السعر')->money('EGP'),
            TextColumn::make('product_limit')->label('حد المنتجات')->placeholder('غير محدود'),
            IconColumn::make('is_active')->label('مفعّلة')->boolean(),
        ])->recordActions([
            EditAction::make()->after(fn (Plan $r) => ActivityLog::record('تعديل خطة', $r, $r->getChanges(), 'plan.updated')),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePlans::route('/')];
    }
}
