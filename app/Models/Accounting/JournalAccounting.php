<?php

namespace App\Models\Accounting;

use App\Models\Association\Disbursement;
use App\Models\Bank\Employe;
use App\Models\Bank\Teller;
use App\Models\Lending\TransactionLoan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalAccounting extends Model
{
    use HasFactory;

    protected $table = "acc_journals";
    protected $primaryKey = "id_journal";
    protected $keyType = "string";
    public $incrementing = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date_oper' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function caisse(): BelongsTo
    {
        return $this->belongsTo(Teller::class, 'teller_id', 'id_teller');
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'employe_id', 'id_employe');
    }

    public function operationCredit(): HasMany
    {
        return $this->hasMany(TransactionLoan::class, 'journal_id', 'id_journal');
    }

    public function decaissement(): HasMany
    {
        return $this->hasMany(Disbursement::class, 'journal_id', 'id_journal');
    }
}
