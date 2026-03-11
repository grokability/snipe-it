<?php

namespace App\Custom\Models;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftHandover extends Model
{
    protected $table = 'shift_handovers';

    protected $fillable = [
        'shift_type',
        'shift_date',
        'location_id',
        'outgoing_user_id',
        'incoming_user_id',
        'outgoing_signature',
        'incoming_signature',
        'outgoing_notes',
        'incoming_notes',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'shift_date'   => 'date',
        'completed_at' => 'datetime',
    ];

    // ── Relationships ──

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function outgoingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'outgoing_user_id');
    }

    public function incomingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'incoming_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShiftHandoverItem::class);
    }

    // ── Helpers ──

    /**
     * Return the current shift type based on the hour.
     *
     * 06:00–13:59 → '06-14'
     * 14:00–21:59 → '14-22'
     * 22:00–05:59 → '22-06'
     */
    public static function getCurrentShift(): string
    {
        $hour = (int) now()->format('H');

        if ($hour >= 6 && $hour < 14) {
            return '06-14';
        }

        if ($hour >= 14 && $hour < 22) {
            return '14-22';
        }

        return '22-06';
    }
}
