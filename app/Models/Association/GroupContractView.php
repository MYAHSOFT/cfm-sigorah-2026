<?php

namespace App\Models\Association;

use App\Models\Bank\Employe;
use App\Models\Lending\ContractLoan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vue lecture seule : contrats de prêt par groupement (ex cf_contrat_for_groupe).
 */
class GroupContractView extends Model
{
    use HasFactory;

    protected $table = "ass_group_contracts_view";

    protected $primaryKey = "contract_id";

    protected $keyType = "int";

    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'received_amount' => 'decimal:2',
        ];
    }

    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'id_group', 'id_group');
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'agent_id', 'id_employe');
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Cycle::class, 'cycle_id', 'id_cycle');
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContractLoan::class, 'contract_id', 'id_contract');
    }
}
