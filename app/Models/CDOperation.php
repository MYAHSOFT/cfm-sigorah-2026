<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CDOperation extends Model
{
    use HasFactory;

    protected $table = "cd_operations";

    protected $primaryKey = "id_oper";

    protected $guarded = [];

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    public function contrat()
    {
        return $this->belongsTo(ContratPret::class, 'ref_pret');
    }

}
