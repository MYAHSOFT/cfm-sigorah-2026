<?php

namespace App\Models\Lending;

use App\Models\Association\GroupLoanApplication;
use App\Models\Bank\Employe;
use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApplicationLoan extends Model
{
    use HasFactory;

    protected $table = "loan_applications";

    protected $primaryKey = "id_application";

    protected $keyType = "int";

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at_local' => 'datetime',
            'date_requested' => 'date',
            'amount_requested' => 'decimal:2',
            'amount_approved' => 'decimal:2',
            'decision_at' => 'datetime',
            'lifecycle' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'folio', 'folio_customer');
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'employe_id', 'id_employe');
    }

    public function contrat(): HasOne
    {
        return $this->hasOne(ContractLoan::class, 'application_id', 'id_application');
    }

    public function demandeGP(): HasOne
    {
        return $this->hasOne(GroupLoanApplication::class, 'application_id', 'id_application');
    }
}
