<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduitCredit extends Model
{
    use HasFactory;

    protected $table = "cd_produits";

    protected $primaryKey = "id_produit";

    public $incrementing = false;

    protected $keyType = "string";

    public $timestamps = false;

    public function contrat()
    {
        return $this->hasMany(ContratPret::class, 'id_produit');
    }
}
