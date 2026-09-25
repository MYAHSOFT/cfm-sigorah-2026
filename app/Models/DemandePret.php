<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandePret extends Model
{
    use HasFactory;

    protected $table = "cd_demandes";

    protected $primaryKey = "id_demande";

    protected $keyType = "string";

    public $incrementing = false;

    protected $guarded = [];

    public function tiers()
    {
        return $this->belongsTo(Tiers::class, 'tiers_id');
    }

    public function demandeGP()
    {
        return $this->hasOne(DemandePretGroupe::class, 'ref_dde');
    }

}
