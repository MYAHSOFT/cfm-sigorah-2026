<?php

namespace App\Models\Association;

use App\Models\Lending\ApplicationLoan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lien groupe/cycle ↔ loan_applications (ex cf_demande_pret_groupes).
 */
class GroupLoanApplication extends Model
{
    use HasFactory;

    protected $table = "ass_loan_applications";

    protected $primaryKey = "id_loan_application";

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'received_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id_group');
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Cycle::class, 'cycle_id', 'id_cycle');
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(ApplicationLoan::class, 'application_id', 'id_application');
    }
}
