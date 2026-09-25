<?php

namespace App\Lib;

use DB;
use App\Models\Tiers;
use App\Models\Caisse;
use App\Models\Compte;
use App\Models\Journal;
use App\Models\Ecriture;
use App\Models\CFDossier;
use App\Models\PlanCompte;
use App\Models\ContratPret;
use App\Models\DemandePret;
use App\Models\GroupeSolide;
use App\Models\CycleActivite;
use App\Models\DemandeCompte;
use App\Models\ContratEpargne;
use Illuminate\Database\Query\Builder;

class Referentiel
{

    /**
     * Calucl ID tiers à partir du dernier enregistrement
     *
     * @param string $id_caisse
     * @return int
     */

     public static function tiers(string $caisse) : int
     {

        //  $new_id = 0;

         $new_id = (int)$caisse.'00001';

         $max_id = Tiers::where('id_tiers','like',$caisse.'%')
                    ->max('id_tiers');

         if(!empty($max_id)){

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

    /**
     * Calcul l'ID de la nouvelle demande de pret dans une agence
     * @param $id_agence
     * @param $date_dde
     * @return string $id
     */

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

    public static function contratPretId($caisse_id, $dateContrat) : int
    {

        $year = (new \DateTime($dateContrat))->format('Y');

        $y = (new \DateTime($dateContrat))->format('y');

        $lastId = ContratPret::join('cd_demandes', 'cd_contrats.demande_id','cd_demandes.id_demande')
                        ->where('caisse_id', $caisse_id)
                        ->whereYear('date_contrat', $year)
                        ->max('id_pret');

        $newId = $lastId > 0 ? $lastId + 1 : $caisse_id.$y.'00001';

        $test = true;

        while ($test == true) {

            $contrat = ContratPret::where('id_pret', $newId)->first();

            if(empty($contrat->id_pret)){

                $test = false;

                break;

            }else{

                $newId = $newId + 1;

            }

        }

        return $newId;
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

    public static function lastMonth($year = null)
    {

        if(empty($year)){
            $year = date('Y');
        }

        $f = 28;

        if($year%4 == 0){
            $f = 29;
        }

        return [
            1=>"$year-01-31",
            2=>"$year-02-$f",
            3=>"$year-03-31",
            4=>"$year-04-30",
            5=>"$year-05-31",
            6=>"$year-06-30",
            7=>"$year-07-31",
            8=>"$year-08-31",
            9=>"$year-09-30",
            10=>"$year-10-31",
            11=>"$year-11-30",
            12=>"$year-12-31",
        ];


    }


    public static function month()
    {

        return [
            1=>"janvier",
            2=>"février",
            3=>"mars",
            4=>"avril",
            5=>"mai",
            6=>"juin",
            7=>"juillet",
            8=>"août",
            9=>"septembre",
            10=>"octobre",
            11=>"novembre",
            12=>"décembre",
        ];


    }

}
