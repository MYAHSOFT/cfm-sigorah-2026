<?php

namespace App\Models\Saving;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSaving extends Model
{
    use HasFactory;

    protected $table = "sav_products";

    protected $primaryKey = "id_product";

    public $incrementing = false;

    protected $keyType = "string";

    protected function casts(): array
    {
        return [
            'nominal_rate' => 'decimal:4',
            'min_balance' => 'decimal:2',
            'min_deposit' => 'decimal:2',
            'overdraft_allowed' => 'boolean',
            'overdraft_limit_default' => 'decimal:2',
            'is_term' => 'boolean',
            'transaction_type' => 'array',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function compte(): HasMany
    {
        return $this->hasMany(AccountSaving::class, 'product_id', 'id_product');
    }

}
