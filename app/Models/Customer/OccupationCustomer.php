<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccupationCustomer extends Model
{
    use HasFactory;

    protected $table = "crm_occupations";

    protected $primaryKey = "id_occupation";

    public $incrementing = false;

    protected $keyType = "string";

    public $timestamps = false;

}
