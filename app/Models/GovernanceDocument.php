<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
	'title',
	'reference_no',
	'document_type',
	'related_resolution_id',
	'related_minute_id',
	'category',
	'version',
	'status',
	'issued_at',
	'effective_at',
	'expires_at',
	'file_path',
	'notes',
	'uploaded_by',
])]
class GovernanceDocument extends Model
{
	use HasFactory;

	public const CATEGORY_SAFETY = 'safety';
	public const CATEGORY_FINANCE = 'finance';
	public const CATEGORY_HR = 'hr';
	public const CATEGORY_BOARD = 'board';
	public const CATEGORY_COMPLIANCE = 'compliance';
	public const CATEGORY_OPERATIONS = 'operations';
	public const CATEGORY_MEMBERSHIP = 'membership';
	public const CATEGORY_LEGAL = 'legal';

	/**
	 * Cast date and numeric metadata to expected value types.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'version' => 'integer',
			'issued_at' => 'datetime',
			'effective_at' => 'datetime',
			'expires_at' => 'datetime',
		];
	}

	/**
	 * @return array<int, string>
	 */
	public static function allowedCategories(): array
	{
		return [
			self::CATEGORY_SAFETY,
			self::CATEGORY_FINANCE,
			self::CATEGORY_HR,
			self::CATEGORY_BOARD,
			self::CATEGORY_COMPLIANCE,
			self::CATEGORY_OPERATIONS,
			self::CATEGORY_MEMBERSHIP,
			self::CATEGORY_LEGAL,
		];
	}

	public function uploader(): BelongsTo
	{
		return $this->belongsTo(User::class, 'uploaded_by');
	}

	public function relatedMinute(): BelongsTo
	{
		return $this->belongsTo(self::class, 'related_minute_id');
	}

	public function minute(): BelongsTo
	{
		return $this->relatedMinute();
	}

	public function relatedResolution(): BelongsTo
	{
		return $this->belongsTo(self::class, 'related_resolution_id');
	}

	public function resolution(): BelongsTo
	{
		return $this->relatedResolution();
	}

	public function records(): HasMany
	{
		return $this->hasMany(GovernanceRecord::class, 'governance_document_id');
	}

    public static function referenceCodeForType(?string $documentType): string
    {
        $normalized = strtolower((string) ($documentType ?? ''));

        return match ($normalized) {
            'policy' => 'POL',
            'regulation' => 'REG',
            'minutes' => 'MIN',
            'resolution' => 'RES',
            'communication' => 'COM',
            'special_resolution', 'specialresolution' => 'SPR',
            'board_reviewed', 'boardreviewed' => 'BRD',
            default => 'GEN',
        };
    }

    public static function nextReferenceNumber(?string $documentType): string
    {
        $code = self::referenceCodeForType($documentType);
        $year = now()->format('Y');

        $highestSequence = self::query()
            ->when($documentType, fn (Builder $query) => $query->where('document_type', $documentType))
            ->whereYear('created_at', $year)
            ->whereNotNull('reference_no')
            ->pluck('reference_no')
            ->reduce(function (?int $carry, mixed $referenceNo) use ($code, $year): ?int {
                if (! is_string($referenceNo)) {
                    return $carry;
                }

                $normalized = strtoupper($referenceNo);

                if (! preg_match('/^' . preg_quote($code, '/') . '-' . preg_quote((string) $year, '/') . '-(\d+)$/', $normalized, $matches)) {
                    return $carry;
                }

                $sequence = (int) $matches[1];

                return $carry === null ? $sequence : max($carry, $sequence);
            }, null);

        $nextSequence = ($highestSequence ?? 0) + 1;

        return sprintf('%s-%s-%03d', $code, $year, $nextSequence);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }
}
