<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compte extends Model
{
    use HasFactory;

    protected $table = "ep_comptes";

    protected $primaryKey = "id_compte";

    public $incrementing = false;

    protected $keyType = "string";

    public $timestamps = false;

    protected $guarded = [];

    public function tiers()
    {
        return $this->belongsTo(Tiers::class, 'tiers_id');
    }

    public function produit()
    {
        return $this->belongsTo(ProduitEpargne::class, 'produit_id');
    }

    public function mandataire()
    {
        return $this->hasMany(CompteMandataire::class, 'compte_id');
    }


}
