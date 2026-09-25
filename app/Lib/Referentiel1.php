<?php

namespace App\Lib;

use App\Models\Tiers;
use App\Models\Caisse;
use App\Models\Journal;
use App\Models\CFDossier;
use App\Models\ContratPret;
use App\Models\DemandePret;
use App\Models\GroupeSolide;
use DB;
class Referentiel
{

    /**
     * Calucl ID tiers à partir du dernier enregistrement
     *
     * @param string $id_caisse
     * @return int
     */

    public static function tiers(string $id_caisse) : int
    {

        $caisse = Caisse::where('id_caisse', $id_caisse)->first();

        $id = 0;

        if(!empty($caisse->id_caisse)){

            $id_initial = (int)$caisse->id_caisse.'00001';

            $max_id = Tiers::where('caisse_id', $id_caisse)->max('id_tiers');

            if(empty($max_id)){

                $id = $id_initial;

            }else{

                $new_id = $max_id + 1;

                $test = true;

                while ($test == true) {

                    $tiers = Tiers::where('id_tiers', $new_id)->first();

                    if(empty($tiers->id_tiers)){

                        $test = false;

                        break;

                    }

                    $new_id = $new_id + 1;

                }

            }

        }

        return $new_id;

    }

    public static function groupeId(string $id_caisse) : int
    {

        $new_id = (int)$id_caisse."001";

        $test = true;

        while ($test == true) {


            $groupe = GroupeSolide::where('id_groupe', $new_id)->first();

            if(empty($groupe->id_groupe)){

                $test = false;

                break;

            }

            $new_id = $new_id + 1;

        }

        return $new_id;

    }
    

    public static function demandeId(string $caisseId, $dateDemande) : int
    {

        $year = (new \DateTime($dateDemande))->format('Y');

        $y = (new \DateTime($dateDemande))->format('y');

        $lastId = DemandePret::where('caisse_id', $caisseId)
                                ->whereYear('date_demande', $year)
                                ->max('id_demande');

        $newId = $lastId > 0 ? $lastId + 1 : $caisseId.$y.'00001';

        $test = true;

        while ($test == true) {

            $demande = DemandePret::where('id_demande', $newId)->first();

            if(empty($demande->id_demande)){

                $test = false;

                break;

            }else{

                $newId = $newId + 1;

            }

        }

        return $newId;

    }

    public static function contratPretId(string $id_caisse, $date_contrat) : string
    {

        $date_time = new \DateTime($date_contrat);

        $start = ContratPret::where('caisse_id', $id_caisse)
                    ->whereYear('date_contrat', $date_time->format('Y'))
                    ->max('ref_pret');

        $new_id = (int)($id_caisse.$date_time->format('y').'00001');

        if($start > 0){

            $test = true;

            while ($test == true) {

                $start++;

                $new_id = $start;

                $contrat = ContratPret::where('ref_pret', $new_id)->first();

                if(empty($contrat->ref_pret)){

                    $test = false;

                    break;

                }

            }
        }

        return (string)$new_id;

    }

    public static function dossierId(string $id_groupe, $debut_cycle) : string
    {

        $date_time = new \DateTime($debut_cycle);

        $nb_dossier = CFDossier::where('groupe_id', $id_groupe)
                    ->whereYear('debut_cycle', $date_time->format('Y'))
                    ->count();

        $new_id = (int)$id_groupe.$date_time->format('y').'01';

        if($nb_dossier > 0){

            $test = true;

            $start = substr(100+$nb_dossier,-2);

            $new_id = (int)$id_groupe.$date_time->format('y').$start;

            while ($test == true) {

                $dossier = CFDossier::where('id_dossier', $new_id)->first();

                if(empty($dossier->id_dossier)){

                    $test = false;

                    break;

                }

                $new_id = $new_id + 1;

            }
        }

        return (string)$new_id;

    }


    public static function refJournal($type_journal,$date_oper, $caisse_id)
    {

        $ref_robe = "";

        $date = new \DateTime($date_oper);

        $nb = Journal::where('caisse_id', $caisse_id)
                  ->where('type_journal', $type_journal)
                  ->whereYear('date_oper', $date->format('Y'))
                  ->count();

        if ($nb == 0) {

            $ref_robe = $caisse_id.strtoupper($type_journal).$date->format('y'). "0001";

        } else {

            $serie = 1;

            $test = false;

            while ($test == false) {

                $next = substr(10000 + $nb + $serie, 1, 4);

                $ref_robe = $caisse_id.strtoupper($type_journal).$date->format('y') . $next;

                $nb_robe = Journal::where('id_journal', $ref_robe)->count();

                if ($nb_robe == false) {

                    $test = true;
                }

                $serie++;
            }
        }

        return $ref_robe;


    }

    public static function codeCommuneCin($cin)
    {

        $code_cummune = "";

        $code_cin = substr($cin,0,3);

        $commune = DB::table('localite_cin')->where('code_cin', $code_cin)->first();

        if(!empty($commune->id_commune)){
            $code_cummune = $commune->id_commune;
        }

        return $code_cummune;

    }

    public static function codeCommuneResidence($id_caisse)
    {

        $code_cummune = "";

        $caisse = DB::table('caisses')->where('id_caisse', $id_caisse)->first();

        if(!empty($caisse->code_commune)){
            $code_cummune = $caisse->code_commune;
        }

        return $code_cummune;

    }

    public static function demission()
    {
        return [
            '1' =>"Affectation",
            '2' =>"Service Insatisfaisant",
            '3' =>"Décé",
        ];
    }

}
