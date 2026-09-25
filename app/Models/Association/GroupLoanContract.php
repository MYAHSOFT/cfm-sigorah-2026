<?php

namespace App\Models\Association;

use App\Models\Lending\ContractLoan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lien groupe/cycle ↔ loan_contracts (ex cf_contrat_pret_groupes).
 */
class GroupLoanContract extends Model
{
    use HasFactory;

    protected $table = "ass_loan_contracts";

    protected $primaryKey = "id_loan_contract";

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'received_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContractLoan::class, 'contract_id', 'id_contract');
    }

    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id_group');
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Cycle::class, 'cycle_id', 'id_cycle');
    }
}
