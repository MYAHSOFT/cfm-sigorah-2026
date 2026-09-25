<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendrier extends Model
{
    use HasFactory;

    protected $table = "cd_calendriers";

    protected $primaryKey = "id_oper";

    public $timestamps = false;

    protected $guarded = [];

    public function contrat()
    {
        return $this->belongsTo(ContratPret::class, 'ref_pret');
    }
}
