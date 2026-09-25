<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFDemandePret extends Model
{
    use HasFactory;

    protected $table = "cf_demandes";

    protected $primaryKey = "id_demande";

    protected $guarded = [];
    
}
