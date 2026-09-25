<?php

namespace App\Models\Association;

use App\Models\Bank\Employe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cycle d'activité d'un groupement (ex cf_dossiers / cf_cycle_activites).
 */
class Cycle extends Model
{
    use HasFactory;

    protected $table = "ass_cycles";

    protected $primaryKey = "id_cycle";

    public $incrementing = false;

    protected $keyType = "string";

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'cycle_start_date' => 'date',
            'cycle_end_date' => 'date',
            'planned_disbursement_date' => 'date',
            'actual_disbursement_date' => 'date',
            'planned_repayment_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id_group');
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'facilitator_id', 'id_employe');
    }

    public function calendrier(): HasMany
    {
        return $this->hasMany(CycleCalendar::class, 'cycle_id', 'id_cycle');
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(MemberApplication::class, 'cycle_id', 'id_cycle');
    }

    public function demandesGP(): HasMany
    {
        return $this->hasMany(GroupLoanApplication::class, 'cycle_id', 'id_cycle');
    }

    public function contratsGP(): HasMany
    {
        return $this->hasMany(GroupLoanContract::class, 'cycle_id', 'id_cycle');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(MeetingOperation::class, 'cycle_id', 'id_cycle');
    }

    public function decaissements(): HasMany
    {
        return $this->hasMany(Disbursement::class, 'cycle_id', 'id_cycle');
    }
}
