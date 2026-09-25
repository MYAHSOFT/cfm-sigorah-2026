<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratPret extends Model
{
    use HasFactory;

    protected $table = "cd_contrats";

    protected $primaryKey = "id_pret";

    protected $keyType = "string";

    public $incrementing = false;

    protected $guarded = [];

    public function tiers()
    {
        return $this->belongsTo(Tiers::class, 'tiers_id');
    }

    public function demande()
    {
        return $this->hasOne(DemandePret::class, 'ref_dde');
    }

    public function contratGP()
    {
        return $this->hasOne(ContratPretGroupes::class, 'ref_pret');
    }

    public function produit()
    {
        return $this->belongsTo(ProduitCredit::class, 'produit_id');
    }

    public function operation()
    {
        return $this->hasMany(CDOperation::class, 'ref_pret');
    }

    public function calendrier()
    {
        return $this->hasMany(Calendrier::class, 'ref_pret');
    }
}
