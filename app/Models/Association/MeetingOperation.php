<?php

namespace App\Models\Association;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Collecte en réunion (ex cf_operations). `customer_id` = crm_members.folio.
 */
class MeetingOperation extends Model
{
    use HasFactory;

    protected $table = "ass_operations";

    protected $primaryKey = "id_operation";

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at_local' => 'datetime',
            'operation_date' => 'date',
            'meeting_date' => 'date',
            'repayment_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'withdrawal_amount' => 'decimal:2',
            'penalty' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'folio_customer');
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Cycle::class, 'cycle_id', 'id_cycle');
    }
}
