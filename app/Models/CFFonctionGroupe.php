<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFFonctionGroupe extends Model
{
    use HasFactory;

    protected $table = "cf_fonction_membre_groupement";

    protected $primaryKey = "id_fonction";

    public $incrementing = false;

    protected $keyType = "string";

    public $timestamps = false;

}
