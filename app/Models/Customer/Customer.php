<?php

namespace App\Models\Customer;

use App\Models\Association\Group;
use App\Models\Association\MeetingOperation;
use App\Models\Association\Member;
use App\Models\Lending\ApplicationLoan;
use App\Models\Saving\AccountSaving;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Client (ex tiers). `folio_customer` = crm_members.folio, clé utilisée par
 * les demandes, comptes et opérations de groupement.
 */
class Customer extends Model
{
    use HasFactory;

    protected $table = "crm_customers";

    protected $primaryKey = "id_customer";

    protected $keyType = "int";

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'date_of_birth' => 'date',
            'date_of_cin' => 'date',
            'kyc_completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(OccupationCustomer::class, 'occupation_id', 'id_occupation');
    }

    public function demande(): HasMany
    {
        return $this->hasMany(ApplicationLoan::class, 'folio', 'folio_customer');
    }

    public function compte(): HasMany
    {
        return $this->hasMany(AccountSaving::class, 'folio', 'folio_customer');
    }

    public function operationGroupe(): HasMany
    {
        return $this->hasMany(MeetingOperation::class, 'customer_id', 'folio_customer');
    }

    public function membreGroupe(): HasOne
    {
        return $this->hasOne(Member::class, 'customer_id', 'id_customer');
    }

    public function groupe(): HasOne
    {
        return $this->hasOne(Group::class, 'id_group', 'folio_customer');
    }
}
