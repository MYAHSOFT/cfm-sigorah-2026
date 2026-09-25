<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    use HasFactory;

    protected $table = "agences";

    protected $primaryKey = "id_agence";

    protected $keyType = "string";

    public $incrementing = false;

    protected $guarded = [];

    public function caisse()
    {
        return $this->hasMany(Caisse::class, 'id_agence');
    }
}
