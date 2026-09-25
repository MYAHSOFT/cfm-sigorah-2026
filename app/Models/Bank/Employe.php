<?php

namespace App\Models\Bank;

use App\Models\Association\Cycle;
use App\Models\Association\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employe extends Model
{
    use HasFactory;

    protected $table = "bank_employes";

    protected $primaryKey = "id_employe";

    protected $keyType = "string";

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'notifications_enabled' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'name', 'id_employe');
    }

    public function groupe(): HasMany
    {
        return $this->hasMany(Group::class, 'agent_id', 'id_employe');
    }

    public function cycles(): HasMany
    {
        return $this->hasMany(Cycle::class, 'facilitator_id', 'id_employe');
    }
}
