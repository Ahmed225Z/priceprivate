<?php

declare(strict_types=1);

namespace App\Filament\Resources\Merchants;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\Merchants\Pages\ManageMerchants;
use App\Models\ActivityLog;
use App\Models\Merchant;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MerchantResource extends AdminResource
{
    protected static ?string $model = Merchant::class;

    protected static ?string $modelLabel = 'تاجر';

    protected static ?string $pluralModelLabel = 'التجار';

    protected static ?string $recordTitleAttribute = 'store_name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('store_name')->label('اسم المحل')->required()->maxLength(120),
            TextInput::make('slug')->label('الرابط')->disabled(),
            Select::make('status')->label('الحالة')->options(['active' => 'نشط', 'suspended' => 'موقوف'])->required(),
            Toggle::make('is_verified')->label('موثّق'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('store_name')->label('المحل')->searchable(),
                TextColumn::make('slug')->label('الرابط')->searchable(),
                TextColumn::make('governorate')->label('المحافظة')->toggleable(),
                TextColumn::make('products_count')->label('المنتجات')->sortable(),
                TextColumn::make('status')->label('الحالة')->badge(),
                IconColumn::make('is_verified')->label('موثّق')->boolean(),
                TextColumn::make('prices_updated_at')->label('آخر تحديث سعر')->since()->sortable(),
                TextColumn::make('created_at')->label('اتسجّل')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options(['active' => 'نشط', 'suspended' => 'موقوف']),
            ])
            ->recordActions([
                EditAction::make()->after(fn (Merchant $record) => ActivityLog::record('تعديل تاجر', $record, $record->getChanges(), 'merchant.updated')),
                Action::make('toggleSuspend')
                    ->label(fn (Merchant $r) => $r->status === 'suspended' ? 'إعادة تفعيل' : 'إيقاف')
                    ->color(fn (Merchant $r) => $r->status === 'suspended' ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(function (Merchant $record) {
                        $new = $record->status === 'suspended' ? 'active' : 'suspended';
                        $record->forceFill(['status' => $new])->save();
                        $record->bumpCacheVersion();
                        ActivityLog::record($new === 'suspended' ? 'إيقاف تاجر' : 'إعادة تفعيل تاجر', $record, [], 'merchant.'.$new);
                    }),
                Action::make('impersonate')
                    ->label('دخول كتاجر')
                    ->icon('heroicon-o-eye')
                    ->requiresConfirmation()
                    ->action(function (Merchant $record) {
                        app(\App\Actions\Impersonation::class)->start(auth()->user(), $record->user);

                        return redirect('/dashboard');
                    })
                    ->visible(fn () => auth()->user()?->isAdmin() && ! session()->has('impersonator_id')),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageMerchants::route('/')];
    }
}
