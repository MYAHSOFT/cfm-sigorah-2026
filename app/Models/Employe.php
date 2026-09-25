<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    use HasFactory;

    protected $table = "employes";

    protected $primaryKey = "id_employe";

    protected $keyType = "string";

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(User::class, 'name');
    }

    public function groupe()
    {
        return $this->hasMany(GroupeSolide::class, 'animtrice_id');
    }

}
