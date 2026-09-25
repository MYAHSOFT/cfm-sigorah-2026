<?php

namespace App\Models\Association;

use App\Models\Bank\Employe;
use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Groupement (ex cf_groupe_solidarites). `id_group` = crm_members.folio du groupe.
 */
class Group extends Model
{
    use HasFactory;

    protected $table = "ass_groups";

    protected $primaryKey = "id_group";

    protected $keyType = "int";

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'agent_id', 'id_employe');
    }

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_group', 'folio_customer');
    }

    public function membres(): HasMany
    {
        return $this->hasMany(Member::class, 'group_id', 'id_group');
    }

    public function cycles(): HasMany
    {
        return $this->hasMany(Cycle::class, 'group_id', 'id_group');
    }

    public function demandesGP(): HasMany
    {
        return $this->hasMany(GroupLoanApplication::class, 'group_id', 'id_group');
    }

    public function contratsGP(): HasMany
    {
        return $this->hasMany(GroupLoanContract::class, 'group_id', 'id_group');
    }
}
