<?php

namespace App\Models\Association;

use App\Models\Lending\ContractLoan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Échéancier d'un contrat de groupement (ex cf_echeanciers).
 */
class GroupSchedule extends Model
{
    use HasFactory;

    protected $table = "ass_schedules";

    protected $primaryKey = "id_operation";

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'operation_date' => 'date',
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContractLoan::class, 'contract_id', 'id_contract');
    }
}
