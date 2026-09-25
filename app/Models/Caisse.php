<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caisse extends Model
{
    use HasFactory;

    protected $table = "caisses";

    protected $primaryKey = "id_caisse";

    protected $keyType = "string";

    public $incrementing = false;

    protected $guarded = [];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

}
