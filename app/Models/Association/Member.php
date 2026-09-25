<?php

namespace App\Models\Association;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Membre d'un groupement (ex cf_membres). `customer_id` = crm_customers.id_customer.
 */
class Member extends Model
{
    use HasFactory;

    protected $table = "ass_members";

    protected $primaryKey = "customer_id";

    protected $keyType = "int";

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id_customer');
    }

    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id_group');
    }

    public function fonction(): BelongsTo
    {
        return $this->belongsTo(MemberRole::class, 'member_role', 'id_role');
    }
}
