<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\Reports\Pages\ManageReports;
use App\Models\ActivityLog;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Report;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportResource extends AdminResource
{
    protected static ?string $model = Report::class;

    protected static ?string $modelLabel = 'بلاغ';

    protected static ?string $pluralModelLabel = 'البلاغات';

    /** Moderators work reports; everything else is admin-only. */
    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isModerator();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Textarea::make('action_note')->label('ملاحظة')]);
    }

    public static function table(Table $table): Table
    {
        $handle = fn (string $label, string $status, string $color) => Action::make('mark_'.$status)
            ->label($label)->color($color)
            ->schema([Textarea::make('action_note')->label('ملاحظة')])
            ->action(function (Report $record, array $data) use ($status) {
                $record->forceFill([
                    'status' => $status,
                    'action_note' => $data['action_note'] ?? null,
                    'handled_by' => auth()->id(),
                    'handled_at' => now(),
                ])->save();
                ActivityLog::record('معالجة بلاغ: '.$status, $record, ['note' => $data['action_note'] ?? null], 'report.'.$status);
            });

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reason')->label('السبب')->badge(),
                TextColumn::make('reportable_type')->label('النوع')->formatStateUsing(fn ($s) => $s === (new Product)->getMorphClass() || str_ends_with((string) $s, 'Product') ? 'منتج' : 'محل'),
                TextColumn::make('reportable_id')->label('رقم'),
                TextColumn::make('note')->label('ملاحظة المُبلّغ')->limit(60),
                TextColumn::make('status')->label('الحالة')->badge(),
                TextColumn::make('created_at')->label('التاريخ')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options(['new' => 'جديد', 'reviewed' => 'اتراجع', 'actioned' => 'اتنفّذ', 'dismissed' => 'اترفض'])->default('new'),
            ])
            ->recordActions([
                $handle('اتراجع', Report::STATUS_REVIEWED, 'gray'),
                $handle('اترفض', Report::STATUS_DISMISSED, 'gray'),
                $handle('إخفاء المحتوى', Report::STATUS_ACTIONED, 'danger')
                    ->before(function (Report $record) {
                        $target = $record->reportable;
                        if ($target instanceof Product) {
                            $target->forceFill(['is_visible' => false])->save();
                            $target->merchant?->bumpCacheVersion();
                        } elseif ($target instanceof Merchant) {
                            $target->forceFill(['status' => 'suspended'])->save();
                            $target->bumpCacheVersion();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageReports::route('/')];
    }
}
