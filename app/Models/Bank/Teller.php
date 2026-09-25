<?php

namespace App\Models\Bank;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teller extends Model
{
    use HasFactory;

    protected $table = "bank_tellers";

    protected $primaryKey = "id_teller";

    protected $keyType = "string";

    public $incrementing = false;

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

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id', 'id_agency');
    }
}
