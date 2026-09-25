<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitEpargne extends Model
{
    use HasFactory;

    protected $table = "ep_produits";

    protected $primaryKey = "id_produit";

    public $incrementing = false;

    protected $keyType = "string";

    public $timestamps = false;

    public function compte()
    {
        return $this->hasMany(Compte::class, 'id_produit');
    }

}
