<?php

namespace App\Models\Lending;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Encours de crédit (ex cf_encours_at4).
 */
class LoanReportOutstanding extends Model
{
    use HasFactory;

    protected $table = "loan_report_outstandings";

    protected $primaryKey = "id_operation";

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'report_reference_date' => 'date',
            'contract_date' => 'date',
            'maturity_date' => 'date',
            'disbursement_date' => 'date',
            'first_arrears_date' => 'date',
            'last_operation_date' => 'date',
            'guarantee_breakdown' => 'array',
        ];
    }

    public function tiers(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'folio', 'folio_customer');
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContractLoan::class, 'contract_id', 'id_contract');
    }
}
