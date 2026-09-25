<?php

namespace App\Models\Association;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fonction d'un membre dans le groupement (ex cf_fonction_membre_groupement).
 */
class MemberRole extends Model
{
    use HasFactory;

    protected $table = "ass_member_roles";

    protected $primaryKey = "id_role";

    public $incrementing = false;

    protected $keyType = "string";

    public $timestamps = false;

    protected $guarded = [];

    public function membres(): HasMany
    {
        return $this->hasMany(Member::class, 'member_role', 'id_role');
    }
}
