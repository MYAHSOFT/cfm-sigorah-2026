<?php

namespace App\Models\Association;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Calendrier des réunions d'un cycle (ex cf_calendier_cycles).
 */
class CycleCalendar extends Model
{
    use HasFactory;

    protected $table = "ass_cycle_calendars";

    protected $primaryKey = "id_operation";

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'operation_date' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class, 'cycle_id', 'id_cycle');
    }
}
