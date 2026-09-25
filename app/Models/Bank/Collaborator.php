<?php

namespace App\Models\Bank;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collaborator extends Model
{
    use HasFactory;

    protected $table = "employe_collaborateurs";

    protected $primaryKey = "id_collaborateur";

    protected $keyType = "string";

    public $incrementing = false;

    protected $guarded = [];

}
