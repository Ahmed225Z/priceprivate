<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscriptions;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\Subscriptions\Pages\ManageSubscriptions;
use App\Models\Subscription;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionResource extends AdminResource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $modelLabel = 'اشتراك';

    protected static ?string $pluralModelLabel = 'الاشتراكات';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ends_at')
            ->modifyQueryUsing(fn ($query) => $query->with(['merchant', 'plan']))
            ->columns([
                TextColumn::make('merchant.store_name')->label('التاجر')->searchable(),
                TextColumn::make('plan.name')->label('الخطة'),
                TextColumn::make('status')->label('الحالة')->badge(),
                TextColumn::make('ends_at')->label('بينتهي')->dateTime()->sortable(),
                TextColumn::make('grace_ends_at')->label('نهاية السماح')->dateTime(),
            ])
            ->filters([SelectFilter::make('status')->options(['active' => 'نشط', 'grace' => 'سماح', 'expired' => 'منتهي', 'cancelled' => 'ملغي'])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSubscriptions::route('/')];
    }
}
