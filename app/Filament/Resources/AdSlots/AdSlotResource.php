<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdSlots;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\AdSlots\Pages\ManageAdSlots;
use App\Models\ActivityLog;
use App\Models\AdSlot;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdSlotResource extends AdminResource
{
    protected static ?string $model = AdSlot::class;

    protected static ?string $modelLabel = 'مكان إعلان';

    protected static ?string $pluralModelLabel = 'أماكن الإعلانات';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('الاسم')->required(),
            Toggle::make('is_active')->label('مفعّل'),
            Select::make('type')->label('النوع')->options(['adsense' => 'AdSense', 'direct' => 'إعلان مباشر'])->required(),
            Textarea::make('adsense_code')->label('كود AdSense'),
            TextInput::make('target_url')->label('رابط الإعلان المباشر')->url(),
            TextInput::make('min_height')->label('الارتفاع المحجوز (px) لمنع CLS')->numeric()->minValue(50)->required(),
            DateTimePicker::make('starts_at')->label('يبدأ'),
            DateTimePicker::make('ends_at')->label('ينتهي'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('المكان'),
            TextColumn::make('type')->label('النوع')->badge(),
            IconColumn::make('is_active')->label('مفعّل')->boolean(),
            TextColumn::make('min_height')->label('الارتفاع'),
            TextColumn::make('impressions')->label('ظهور'),
        ])->recordActions([
            EditAction::make()->after(fn (AdSlot $r) => ActivityLog::record('تعديل مكان إعلان', $r, $r->getChanges(), 'adslot.updated')),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAdSlots::route('/')];
    }
}
