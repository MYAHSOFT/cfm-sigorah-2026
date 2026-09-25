<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupeSolide extends Model
{
    use HasFactory;

    protected $table = "cf_groupe_solidarites";

    protected $primaryKey = "id_groupe";

    protected $keyType = "string";

    public $incrementing = false;

    protected $guarded = [];

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'animatrice_id');

    }


}
