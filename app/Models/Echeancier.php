<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Echeancier extends Model
{
    use HasFactory;

    protected $table = "cf_echeanciers";

    protected $primaryKey = "id_oper";

    public $timestamps = false;

    protected $guarded = [];

}
