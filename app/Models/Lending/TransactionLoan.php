<?php

namespace App\Models\Lending;

use App\Models\Accounting\JournalAccounting;
use App\Models\Bank\Employe;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionLoan extends Model
{
    use HasFactory;

    protected $table = "loan_transactions";

    protected $primaryKey = "id_oper";

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'paid_at_local' => 'datetime',
            'date_oper' => 'datetime',
            'disbursement' => 'decimal:2',
            'repayment' => 'decimal:2',
            'interest' => 'decimal:2',
            'penalty' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'modified_by', 'id_employe');
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContractLoan::class, 'contract_id', 'id_contract');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(JournalAccounting::class, 'journal_id', 'id_journal');
    }
}
