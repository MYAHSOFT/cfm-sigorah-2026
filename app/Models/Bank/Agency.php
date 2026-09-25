<?php

namespace App\Models\Bank;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    use HasFactory;

    protected $table = "bank_agencies";

    protected $primaryKey = "id_agency";

    protected $keyType = "string";

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function caisse(): HasMany
    {
        return $this->hasMany(Teller::class, 'agency_id', 'id_agency');
    }
}
