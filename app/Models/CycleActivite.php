<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CycleActivite extends Model
{
    use HasFactory;

    protected $table = "cf_cycle_activites";

    protected $primaryKey = "id_cycle";

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
