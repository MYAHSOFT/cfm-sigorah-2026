<?php

namespace App\Models\Saving;

use App\Models\Bank\Teller;
use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountSaving extends Model
{
    use HasFactory;

    protected $table = "sav_accounts";

    protected $primaryKey = "id_account";

    public $incrementing = false;

    protected $keyType = "int";

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'opened_at' => 'date',
            'closed_at' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'folio', 'folio_customer');
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(ProductSaving::class, 'product_id', 'id_product');
    }

    public function caisse(): BelongsTo
    {
        return $this->belongsTo(Teller::class, 'teller_id', 'id_teller');
    }
}
