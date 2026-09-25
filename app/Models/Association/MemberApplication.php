<?php

namespace App\Models\Association;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Demande de crédit d'un membre dans un cycle (ex cf_demandes).
 * `customer_id` = crm_members.folio.
 */
class MemberApplication extends Model
{
    use HasFactory;

    protected $table = "ass_applications";

    protected $primaryKey = "id_application";

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at_local' => 'datetime',
            'principal_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'folio_customer');
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
