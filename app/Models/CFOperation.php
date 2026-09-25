<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFOperation extends Model
{
    use HasFactory;

    protected $table = "cf_operations";

    protected $primaryKey = "id_oper";

    protected $guarded = [];

    public function tiers()
    {
        return $this->belongsTo(Tiers::class, 'tiers_id');
    }

    public function dossier()
    {
        return $this->belongsTo(CFDossier::class, 'dossier_id');
    }

}
