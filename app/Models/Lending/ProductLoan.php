<?php

namespace App\Models\Lending;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductLoan extends Model
{
    use HasFactory;

    protected $table = "loan_products";

    protected $primaryKey = "id_product";

    public $incrementing = false;

    protected $keyType = "string";

    protected function casts(): array
    {
        return [
            'nominal_rate' => 'decimal:4',
            'penalty_rate_daily' => 'decimal:4',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function contrat(): HasMany
    {
        return $this->hasMany(ContractLoan::class, 'product_id', 'id_product');
    }
}
