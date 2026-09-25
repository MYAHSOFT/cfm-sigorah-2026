<?php
namespace App\Repositories;

use App\Models\CalendrierCycle;

class CFCalendierCyclesRepository
{

    public function paginate($id_dossier, $per_page)
    {

        return CalendrierCycle::where('dossier_id', $id_dossier)
                        ->paginate($per_page);
    }

    public function get($id_dossier)
    {

        return CalendrierCycle::where('dossier_id', $id_dossier)
                        ->get();
    }

    public function sumMontant($id_dossier)
    {
        return CalendrierCycle::where('dossier_id', $id_dossier)->sum('montant');

    }

}
