<?php

namespace App\Models\Lending;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tableau d'amortissement d'un contrat (ex cd_calendriers).
 */
class ScheduleLoan extends Model
{
    use HasFactory;

    protected $table = "loan_schedules";

    protected $primaryKey = "id_oper";

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date_oper' => 'date',
            'principal' => 'decimal:2',
            'interest' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'cumul_remb' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContractLoan::class, 'contract_id', 'id_contract');
    }
}
