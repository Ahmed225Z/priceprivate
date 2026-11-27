<?php

declare(strict_types=1);

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\Settings\Pages\ManageSettings;
use App\Models\ActivityLog;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingResource extends AdminResource
{
    protected static ?string $model = Setting::class;

    protected static ?string $modelLabel = 'إعداد';

    protected static ?string $pluralModelLabel = 'الإعدادات';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->paginated(false)->columns([
            TextColumn::make('key')->label('المفتاح')->searchable(),
            TextColumn::make('value')->label('القيمة')->formatStateUsing(fn ($s) => is_scalar($s) ? (string) $s : json_encode($s, JSON_UNESCAPED_UNICODE)),
        ])->recordActions([
            Action::make('edit')->label('تعديل')
                ->fillForm(fn (Setting $r) => ['value' => is_scalar($r->value) ? (string) $r->value : json_encode($r->value, JSON_UNESCAPED_UNICODE)])
                ->schema([TextInput::make('value')->label('القيمة')->required()])
                ->action(function (Setting $record, array $data) {
                    $v = $data['value'];
                    $value = is_numeric($v) ? $v + 0 : (in_array($v, ['true', 'false'], true) ? $v === 'true' : $v);
                    Setting::put($record->key, $value);
                    ActivityLog::record('تعديل إعداد '.$record->key, $record, ['value' => $value], 'setting.updated');
                }),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSettings::route('/')];
    }
}
