<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFEcheancier extends Model
{
    use HasFactory;

    protected $table = "cf_echeanciers";

    protected $primaryKey = "id_oper";

    public $timestamps = false;

    protected $guarded = [];

    public function contrat()
    {
        return $this->belongsTo(ContratPret::class, 'ref_pret');
    }
}
