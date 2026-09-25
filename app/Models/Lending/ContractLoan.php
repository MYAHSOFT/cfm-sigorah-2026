<?php

namespace App\Models\Lending;

use App\Models\Association\GroupLoanContract;
use App\Models\Association\GroupSchedule;
use App\Models\Saving\AccountSaving;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Contrat de prêt (ex cd_contrats). Le client s'obtient via demande->tiers.
 */
class ContractLoan extends Model
{
    use HasFactory;

    protected $table = "loan_contracts";

    protected $primaryKey = "id_contract";

    protected $keyType = "int";

    public $incrementing = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'contract_date' => 'date',
            'principal_amount' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'interest_amount' => 'decimal:2',
            'fees' => 'decimal:4',
            'first_repayment_date' => 'date',
            'maturity_date' => 'date',
            'financial_requirement' => 'array',
            'amount_withheld_at_disbursement' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(ApplicationLoan::class, 'application_id', 'id_application');
    }

    public function compte(): BelongsTo
    {
        return $this->belongsTo(AccountSaving::class, 'account_id', 'id_account');
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(ProductLoan::class, 'product_id', 'id_product');
    }

    public function contratGP(): HasOne
    {
        return $this->hasOne(GroupLoanContract::class, 'contract_id', 'id_contract');
    }

    public function operation(): HasMany
    {
        return $this->hasMany(TransactionLoan::class, 'contract_id', 'id_contract');
    }

    public function calendrier(): HasMany
    {
        return $this->hasMany(ScheduleLoan::class, 'contract_id', 'id_contract');
    }

    public function echeancier(): HasMany
    {
        return $this->hasMany(GroupSchedule::class, 'contract_id', 'id_contract');
    }
}
