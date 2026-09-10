<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REVOKED = 'revoked';

    public const DEFAULT_EXPIRY_DAYS = 7;

    protected $fillable = [
        'tutor_id',
        'code',
        'student_name',
        'subject',
        'price',
        'status',
        'expires_at',
        'student_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->isPending() && ! $this->isExpired();
    }

    public function isAcceptableBy(User $student): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($this->student_id !== null && $this->student_id !== $student->id) {
            return false;
        }

        return $student->role === 'student';
    }

    public static function normalizeCode(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }

    public static function findByCode(?string $code): ?self
    {
        $normalized = self::normalizeCode($code);

        if ($normalized === '') {
            return null;
        }

        return self::query()
            ->whereRaw('UPPER(TRIM(code)) = ?', [$normalized])
            ->first();
    }

    /**
     * @param  Builder<Invitation>  $query
     * @return Builder<Invitation>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * @param  Builder<Invitation>  $query
     * @return Builder<Invitation>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->pending()
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (self::query()->whereRaw('UPPER(code) = ?', [$code])->exists());

        return $code;
    }
}
