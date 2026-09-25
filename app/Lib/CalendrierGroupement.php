<?php
namespace App\Lib;

use App\Repositories\CycleActiviteRepository;
use App\Repositories\DemandePretGroupeRepository;

class CalendrierGroupement
{

    public static function dateRemboursement($date_prev_octroi, $jour_reunion, $start = 0): array
    {

        $props = session()->has('cf_calendrier_tmp') ?
                    json_decode(session('cf_calendrier_tmp')) :
                    (object)["date_octroi"=>$date_prev_octroi];

        $first_date = new \DateTime($props->date_octroi);

        $year = $first_date->format("Y");
        $month = $first_date->format("n");
        $day = $first_date->format("j");

        $dayes = [];

        if($start == 1){
            $dayes[0] = $first_date->format("Y-m-d");
        }

        $last_months = [
            1=>"31",
            2=>"28",
            3=>"31",
            4=>"30",
            5=>"31",
            6=>"30",
            7=>"31",
            8=>"31",
            9=>"30",
            10=>"31",
            11=>"30",
            12=>"31",
        ];

        if($year%4 == 0){
            $last_months[2] = "29";
        }


        for($i=0;$i<120;$i++){

            $day += 1;

            if($day >= $last_months[$month]){

                $day = 0;

                $month +=1;

                if($month > 12){

                    $month = 1;

                    $year +=1;

                    if($year%4 == 0){
                        $last_months[2] = "29";
                    }
                }
            }

            $echeance = new \DateTime("$year-$month-$day");

            if($echeance->format("N") == $jour_reunion){
                $dayes[] = $echeance->format("Y-m-d");
            }

        }

        return $dayes;

    }

    public static function getByPret($dossier, $mtt_echeancier, $first_remb = 0)
    {

        $data = [];

        if($first_remb == 0){
            $first_remb = $mtt_echeancier;
        }

        $dateOctroi = !empty($dossier->date_octroi_effectif) ? $dossier->date_octroi_effectif : $dossier->date_prev_octroi;

        $cals = self::dateRemboursement($dateOctroi, $dossier->jour_reunion); //La liste des dates requis pour remboursement

        $start =  $dossier->mode_reunion;


        $nb_remb = $dossier->nb_reunion;

        $key = $start;

        $data[0]= (object)[
            "id_oper"   =>0,
            "date_oper"   =>$cals[$start-1],
            "montant"   =>$first_remb,
        ];

        for($i=1 ; $i < $nb_remb ; $i++){

            $key += $start;

            $data[$i] = (object)[
                "id_oper"   =>$i,
                "date_oper"   =>$cals[$key-1],
                "montant" =>$mtt_echeancier
            ];

        }

        return $data;
    }
    /**
     * Calcul de calendrier de remboursement à partir de date du prermier remboursement
     */
    public static function getByOctroi($mttEcheancier, $request, $firstRemb = 0)
    {

        $data = [];

        if($firstRemb == 0){
            $firstRemb = $mttEcheancier;
        }

        $dateFirstRemb = $request->dateFirstRemb;

        $modReunion = $request->mode;

        $jourReunion = $request->jour;

        // $dateOctroi = !empty($dossier->date_octroi_effectif) ? $dossier->date_octroi_effectif : $dossier->date_prev_octroi;

        $cals = self::dateRemboursement($dateFirstRemb, $jourReunion, 1); //La liste des dates requis pour remboursement


        // $start =  $dossier->mode_reunion;


        $nb_remb = key_exists($modReunion, config('groupement.nb_reunion'))? config('groupement.nb_reunion')[$modReunion]:0;

        $key = 0;

        $data[0]= (object)[
            "id_oper"   =>0,
            "date_oper"   =>$dateFirstRemb,
            "montant"   =>$firstRemb,
        ];

        for($i=1 ; $i < $nb_remb ; $i++){

            $key += $modReunion;

            $data[] = (object)[
                "id_oper"   =>$i,
                "date_oper"   =>$cals[$key],
                "montant" =>$mttEcheancier
            ];

        }

        return $data;
    }

    public static function getRembPerMember($dossier, $_demande, $decision = 'E')
    {

        $rembs = [];

        $nb_remb = $dossier->nb_reunion;

        $taux_remb = config('groupement.taux_interet');

        $demandes = $_demande->getMembres($dossier->id_dossier, $decision);

        // dd($demandes);

        //Calcul de remboursement de chaque membre du groupe
        foreach ($demandes as $dde) {

            $brute = ceil(($dde->mtt_recommande*$taux_remb)/$nb_remb);

            //Récupérationd de dizene
            $tens = substr((string) $brute,-2);

            //Soustraction de dizene
            $remb = $brute - (int)$tens;

            //Ajustement du premier remboursement avec le dizene
            $first_remb = $remb + ((int)$tens*$nb_remb);

            //Suppression de dizene dans le premier remboursement pour ajustement
            $first_remb = $first_remb - ((int)(substr((string)$first_remb,-2)));

            //Collection des remboursements de tous les membres du groupement
            $rembs[$dde->id_tiers] = [
                'first_remb'    =>$first_remb,
                'remb'    =>$remb, // C'est la suite de remboursement après first_remb
            ];

        }

        return $rembs;

    }
    public static function getRembPerMemberByOctroi($dossier, $_demande, $request)
    {

        $rembs = [];

        $nb_remb = config('groupement.nb_reunion')[$request->mode];

        $taux_remb = config('groupement.taux_interet') + 1;

        $demandes = $_demande->getMembres($dossier->id_dossier, 'A');

        //Calcul de remboursement de chaque membre du groupe
        foreach ($demandes as $dde) {

            $brute = ceil(($dde->mtt_recommande*$taux_remb)/$nb_remb);

            //Récupérationd de dizene
            $tens = substr((string) $brute,-2);

            //Soustraction de dizene
            $remb = $brute - (int)$tens;

            //Ajustement du premier remboursement avec le dizene
            $first_remb = $remb + ((int)$tens*$nb_remb);

            //Suppression de dizene dans le premier remboursement pour ajustement
            $first_remb = $first_remb - ((int)(substr((string)$first_remb,-2)));

            //Collection des remboursements de tous les membres du groupement
            $rembs[$dde->id_tiers] = [
                'first_remb'    =>$first_remb,
                'remb'    =>$remb, // C'est la suite de remboursement après first_remb
            ];

        }

        return $rembs;

    }

    public static function echeancierParMembre($dossier, $_demande, $decision = 'E')
    {

        $data = [];

        $rembs = self::getRembPerMember($dossier, $_demande, $decision);

        foreach ($rembs as $key=>$remb) {

            $data[$key] = self::getByPret($dossier, $remb['remb'], $remb['first_remb']);

        }

        return $data;
    }

    public static function getGroupe($dossier, $_demande, $decision = 'E')
    {

        $sum_first_remb = 0;
        $sum_remb = 0;

        foreach (self::getRembPerMember($dossier, $_demande, $decision) as $remb) {
            $sum_first_remb += $remb['first_remb'];
            $sum_remb += $remb['remb'];
        }

        return  self::getByPret($dossier, $sum_remb, $sum_first_remb);

    }

    public static function getGroupeByOctroi($dossier, $_demande, $request)
    {

        $sum_first_remb = 0;

        $sum_remb = 0;

        $remboursements = self::getRembPerMemberByOctroi($dossier, $_demande, $request);

        foreach ($remboursements as $remb) {
            $sum_first_remb += $remb['first_remb'];
            $sum_remb += $remb['remb'];
        }

        return  self::getByOctroi($sum_remb,$request,$sum_first_remb);

    }
}
