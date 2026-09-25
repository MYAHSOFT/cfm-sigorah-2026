<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendrierCycle extends Model
{
    use HasFactory;

    protected $table = "cf_calendier_cycles";

    protected $primaryKey = "id_oper";

    public $timestamps = false;

    protected $guarded = [];

    public function cycle()
    {
        return $this->belongsTo(CycleActivite::class, 'cycle_id');
    }
}
