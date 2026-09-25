<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collaborateur extends Model
{
    use HasFactory;

    protected $table = "employe_collaborateurs";

    protected $primaryKey = "id_collaborateur";

    protected $keyType = "string";

    public $incrementing = false;

    protected $guarded = [];

}
