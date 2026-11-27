<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

/** SRS §13: every admin action is written here. */
class ActivityLog extends Model
{
    protected $table = 'activity_log';

    protected $fillable = ['log_name', 'description', 'event', 'subject_type', 'subject_id', 'causer_type', 'causer_id', 'properties'];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /** The real admin is the causer, even while impersonating a merchant. */
    public static function record(string $description, ?Model $subject = null, array $properties = [], ?string $event = null): self
    {
        $causerId = session('impersonator_id') ?? Auth::id();

        return static::query()->create([
            'log_name' => 'admin',
            'description' => $description,
            'event' => $event,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'causer_type' => $causerId ? (new User)->getMorphClass() : null,
            'causer_id' => $causerId,
            'properties' => $properties ?: null,
        ]);
    }
}
