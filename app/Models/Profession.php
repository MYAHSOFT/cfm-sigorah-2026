<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profession extends Model
{
    use HasFactory;

    protected $table = "referentiel_professions";

    protected $primaryKey = "id_profession";

    public $incrementing = false;

    protected $keyType = "string";

    public $timestamps = false;

}
