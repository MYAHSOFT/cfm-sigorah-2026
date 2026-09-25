<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFEncours extends Model
{
    use HasFactory;

    protected $table = "cf_encours_at4";

    protected $primaryKey = "id_oper";

    protected $guarded = [];

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'animatrice_id');
    }

    public function contrat()
    {
        return $this->belongsTo(ContratPret::class, 'ref_pret');
    }
}
