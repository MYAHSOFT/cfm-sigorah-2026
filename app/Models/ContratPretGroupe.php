<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratPretGroupe extends Model
{
    use HasFactory;

    protected $table = "cf_contrat_pret_groupes";

    protected $primaryKey = "ref_pret";

    protected $keyType = "string";

    public $incrementing = false;

    protected $guarded = [];

    public function contrat()
    {
        return $this->belongsTo(ContratPret::class, 'ref_pret');
    }

}
