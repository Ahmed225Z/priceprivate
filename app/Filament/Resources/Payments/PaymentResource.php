<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\Payments\Pages\ManagePayments;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\SubscriptionService;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentResource extends AdminResource
{
    protected static ?string $model = Payment::class;

    protected static ?string $modelLabel = 'دفعة';

    protected static ?string $pluralModelLabel = 'المدفوعات';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('merchant.store_name')->label('التاجر')->searchable(),
                TextColumn::make('plan.name')->label('الخطة'),
                TextColumn::make('amount')->label('المبلغ')->money('EGP'),
                TextColumn::make('gateway')->label('البوابة')->badge(),
                TextColumn::make('status')->label('الحالة')->badge(),
                TextColumn::make('paid_at')->label('اتدفع')->dateTime(),
            ])
            ->filters([SelectFilter::make('status')->options(['pending' => 'معلّق', 'paid' => 'مدفوع', 'failed' => 'فشل', 'refunded' => 'مسترد'])])
            ->modifyQueryUsing(fn ($query) => $query->with(['merchant', 'plan']))
            ->recordActions([
                Action::make('manualActivate')
                    ->label('تفعيل يدوي')
                    ->requiresConfirmation()
                    ->visible(fn (Payment $r) => $r->status === Payment::STATUS_PENDING)
                    ->action(function (Payment $record) {
                        $record->forceFill(['status' => Payment::STATUS_PAID, 'gateway' => 'manual', 'paid_at' => now()])->save();
                        app(SubscriptionService::class)->activate($record);
                        app(InvoiceService::class)->generate($record->fresh());
                        ActivityLog::record('تفعيل اشتراك يدوي', $record, ['merchant_id' => $record->merchant_id], 'payment.manual_activate');
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePayments::route('/')];
    }
}
