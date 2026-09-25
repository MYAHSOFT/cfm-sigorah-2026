<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandePretGroupe extends Model
{
    use HasFactory;

    protected $table = "cf_demande_pret_groupes";

    protected $primaryKey = "id_demande";

    protected $guarded = [];

    public function groupe()
    {
        return $this->belongsTo(Tiers::class, 'tiers_id');
    }

    public function demande()
    {
        return $this->belongsTo(DemandePret::class, 'ref_dde');
    }
}
