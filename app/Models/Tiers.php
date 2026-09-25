<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tiers extends Model
{
    use HasFactory;

    protected $table = "tiers";

    protected $primaryKey = "id_tiers";

    public $incrementing = false;

    protected $guarded = [];

    public function demande()
    {
        return $this->hasMany(DemandePret::class, 'id_tiers');
    }

    public function contratPret()
    {
        return $this->hasMany(ContratPret::class, 'id_tiers');
    }

    public function operationGroupe()
    {
        return $this->hasMany(operationGroupe::class, 'id_tiers');
    }



}
