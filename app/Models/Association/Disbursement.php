<?php

namespace App\Models\Association;

use App\Models\Accounting\JournalAccounting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Décaissement d'un cycle (ex cf_decaissements).
 */
class Disbursement extends Model
{
    use HasFactory;

    protected $table = "ass_disbursements";

    protected $primaryKey = "id_operation";

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'operation_date' => 'date',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Cycle::class, 'cycle_id', 'id_cycle');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(JournalAccounting::class, 'journal_id', 'id_journal');
    }
}
