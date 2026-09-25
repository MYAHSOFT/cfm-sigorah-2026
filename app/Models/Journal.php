<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $table = "cp_journaux";
    protected $primaryKey = "id_journal";
    protected $keyType = "string";
    public $incrementing = false;
    protected $guarded = [];

    public function caisse()
    {
        return $this->belongsTo(Caisse::class, 'caisse_id');
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    public function exercice()
    {
        return $this->belongsTo(Exercice::class, 'exo_id');
    }

    public function nature()
    {
        return $this->belongsTo(NatureJournal::class, 'type_journal');

    }

    public function ecriture()
    {
        return $this->hasMany(Ecriture::class, 'id_journal');
    }

    public function epargne()
    {
        return $this->hasMany(EPOperation::class, 'id_journal');
    }
}
