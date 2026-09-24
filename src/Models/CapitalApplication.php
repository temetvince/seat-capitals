<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Web\Models\User;

/**
 * A request by a SeAT user to build one capital hull for one of their characters.
 *
 * Rows are only ever created and updated through `ApplicationWorkflow`, which
 * owns the status transitions. Reading them directly is fine.
 *
 * @property int $id
 * @property int $user_id the SeAT user who applied
 * @property int $character_id the character that will own the hull
 * @property int $type_id the hull's SDE type
 * @property string $justification
 * @property ApplicationStatus $status
 * @property int|null $reviewer_user_id the SeAT user who decided, null while pending
 * @property string|null $decision_note
 * @property \Illuminate\Support\Carbon|null $decided_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $user null once the applicant's SeAT account is deleted
 * @property-read CharacterInfo|null $character null once the character leaves SeAT
 * @property-read InvType|null $type null when the SDE no longer lists the hull
 * @property-read User|null $reviewer
 *
 * @package temetvince\SeatCapitals\Models
 */
class CapitalApplication extends Model
{
    protected $table = 'seat_capitals_applications';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'character_id',
        'type_id',
        'justification',
        'status',
        'reviewer_user_id',
        'decision_note',
        'decided_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => ApplicationStatus::class,
        'decided_at' => 'datetime',
    ];

    /**
     * The applicant.
     *
     * @return BelongsTo<User, CapitalApplication>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The character that will own the hull.
     *
     * @return BelongsTo<CharacterInfo, CapitalApplication>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(CharacterInfo::class, 'character_id', 'character_id');
    }

    /**
     * The requested hull.
     *
     * @return BelongsTo<InvType, CapitalApplication>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(InvType::class, 'type_id', 'typeID');
    }

    /**
     * The user who approved or denied the application, if any.
     *
     * @return BelongsTo<User, CapitalApplication>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    /**
     * Restrict a query to applications still awaiting a decision.
     *
     * @param  Builder<CapitalApplication>  $query
     * @return Builder<CapitalApplication>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ApplicationStatus::Pending->value);
    }

    /**
     * Whether the application is still awaiting a decision.
     */
    public function isPending(): bool
    {
        return $this->status === ApplicationStatus::Pending;
    }
}
