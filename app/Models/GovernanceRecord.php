<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
	'governance_document_id',
	'action',
	'remarks',
	'recorded_at',
	'recorded_by',
])]
class GovernanceRecord extends Model
{
	use HasFactory;

	public const ACTION_CREATED = 'created';
	public const ACTION_REVIEWED = 'reviewed';
	public const ACTION_APPROVED = 'approved';
	public const ACTION_PUBLISHED = 'published';
	public const ACTION_UPDATED = 'updated';
	public const ACTION_ARCHIVED = 'archived';
	public const ACTION_ACTIVATED = 'activated';
	public const ACTION_EXPIRED = 'expired';

	/**
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'recorded_at' => 'datetime',
		];
	}

	/**
	 * @return array<int, string>
	 */
	public static function allowedActions(): array
	{
		return [
			self::ACTION_CREATED,
			self::ACTION_REVIEWED,
			self::ACTION_APPROVED,
			self::ACTION_PUBLISHED,
			self::ACTION_UPDATED,
			self::ACTION_ARCHIVED,
			self::ACTION_ACTIVATED,
			self::ACTION_EXPIRED,
		];
	}

	public static function actionForDocumentStatus(?string $status): string
	{
		return match ($status) {
			'active' => self::ACTION_ACTIVATED,
			'archived' => self::ACTION_ARCHIVED,
			'expired' => self::ACTION_EXPIRED,
			default => self::ACTION_UPDATED,
		};
	}

	public function document(): BelongsTo
	{
		return $this->belongsTo(GovernanceDocument::class, 'governance_document_id');
	}

	public function recorder(): BelongsTo
	{
		return $this->belongsTo(User::class, 'recorded_by');
	}

	public function scopeLatestFirst(Builder $query): void
	{
		$query->orderByDesc('recorded_at')->orderByDesc('id');
	}
}
