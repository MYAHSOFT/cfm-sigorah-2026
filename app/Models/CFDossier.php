<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFDossier extends Model
{
    use HasFactory;

    protected $table = "cf_dossiers";

    protected $primaryKey = "id_dossier";

    public $incrementing = false;

    protected $keyType = "string";

    public $timestamps = false;

    protected $guarded = [];

    public function groupe()
    {
        return $this->belongsTo(GroupeSolide::class, 'groupe_id');
    }

    public function calendrier()
    {
        return $this->hasMany(CalendrierCycle::class, 'id_cycle');
    }
}
