<?php
namespace App\Repositories;

use App\Models\Association\CycleCalendar;

class CFCalendierCyclesRepository
{

    public function paginate($id_dossier, $per_page)
    {

        return CycleCalendar::where('dossier_id', $id_dossier)
                        ->paginate($per_page);
    }

    public function get($id_dossier)
    {

        return CycleCalendar::where('dossier_id', $id_dossier)
                        ->get();
    }

    public function sumMontant($id_dossier)
    {
        return CycleCalendar::where('dossier_id', $id_dossier)->sum('montant');

    }

}
