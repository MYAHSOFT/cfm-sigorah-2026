<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupeSolideMembre extends Model
{
    use HasFactory;

    protected $table = "cf_membres";

    protected $primaryKey = ["tiers_id","groupe_id"];

    public $incrementing = false;

    protected $guarded = [];
}
