<?php
namespace App\Lib;

use DB;
use App\Models\Agence;
use App\Models\Caisse;
use App\Models\Employe;
use App\Models\Fonction;
use App\Models\Profession;
use App\Models\NatureJournal;
use App\Models\ProduitCredit;
use App\Models\CodeAnalytique;
use App\Models\FormeJuridique;

class Combobox
{

    public static function fonction ()
    {

        $fonctions = Fonction::pluck('libelle_fonction','id_fonction');

        $data [] = "-- SELECTIONNER UNE FONCTION --";

        foreach ($fonctions as $key => $value) {

            $data[$key] = $value;
        }

        return $data;

    }

    public static function profession ($listFonction = null)
    {

        $professions = Profession::where(function($query) use($listFonction){
                                if(is_array($listFonction)){
                                    if(count($listFonction) > 0){
                                        $query->whereIn('id_profession', $listFonction);
                                    }
                                }else{
                                    if(!empty($listFonction)){
                                        $query->where('id_profession', $listFonction);
                                    }
                                }
                            })
                            ->pluck('libelle','id_profession');

        $data = [];

        foreach ($professions as $key => $label) {

            $data[$key] = "$key : $label";
        }

        return $data;

    }

    public static function juridiqueForme ()
    {

        $professions = FormeJuridique::pluck('libelle','id_juridique');

        $data = [];

        foreach ($professions as $key => $value) {

            $data[$key] = $value;
        }

        return $data;

    }

    public static function caisse ()
    {

        $professions = Caisse::where('agence_id', session('agence'))
                            ->pluck('id_caisse','id_caisse');

        $data = [];

        foreach ($professions as $key => $value) {

            $data[$key] = $value;
        }

        return $data;

    }

    public static function agence ()
    {

        $professions = Agence::pluck('nom_agence','id_agence');

        $data = [];

        foreach ($professions as $key => $value) {

            $data[$key] = $value;
        }

        return $data;

    }

    public static function tauxInteret ($service)
    {

        $taux_interets = DB::table('taux_interets')
                            ->where('service',$service)
                            ->get();

        $data = [];

        foreach ($taux_interets as $taux) {

            $percent = $taux->taux*100;

            $data["$taux->taux"] = $percent."%";
        }

        return $data;

    }

    public static function produitCredit ()
    {

        $produits = ProduitCredit::orderBy('classement_id')->get();

        $data = [];

        foreach ($produits as $produit) {

            $data[$produit->id_produit] = $produit->classement_id.' : '.$produit->libelle;

        }

        return $data;

    }

    public static function employe ($fonction)
    {

        $employes = Employe::where('agence_id', session('agence'))
                            ->where('fonction_id', $fonction)
                            ->orderBy('nom_employe')
                            ->get();

        $data = [];

        foreach ($employes as $employe) {

            $data[$employe->id_employe] = $employe->nom_employe.' '.$employe->prenom_employe;
        }

        return $data;

    }

    public static function codeAnalytique ($id_compte)
    {

        $comptes = CodeAnalytique::where('agence_id', session('agence'))
                            ->where('compte_id', $id_compte)
                            ->get();

        $data = [];

        foreach ($comptes as $compte) {

            $data[$compte->id_analytique] = $compte->libelle_analytique;
        }

        return $data;

    }

    public static function natureJournal ()
    {

        $natures = NatureJournal::get();

        $data = [];

        foreach ($natures as $nature) {

            $data[$nature->id_nature] = $nature->libelle;

        }

        return $data;

    }

    public static function month()
    {
        return [
            1=>"JANVIER",
            2=>"FEVRIER",
            3=>"MARS",
            4=>"AVRIL",
            5=>"MAI",
            6=>"JUIN",
            7=>"JUILLET",
            8=>"AOUT",
            9=>"SEPTEMBRE",
            10=>"OCTOBRE",
            11=>"NOVEMBRE",
            12=>"DECEMBRE",
        ];
    }

    public static function nbMonth($start = null)
    {

        $nb_month = [
            1=>"1 MOIS",
            2=>"2 MOIS",
            3=>"3 MOIS",
            4=>"4 MOIS",
            5=>"5 MOIS",
            6=>"6 MOIS",
            7=>"7 MOIS",
            8=>"8 MOIS",
            9=>"9 MOIS",
            10=>"10 MOIS",
            11=>"11 MOIS",
            12=>"12 MOIS",
            13=>"13 MOIS",
            14=>"14 MOIS",
            15=>"15 MOIS",
            16=>"16 MOIS",
            17=>"17 MOIS",
            18=>"18 MOIS",
            19=>"19 MOIS",
            20=>"20 MOIS",
            21=>"21 MOIS",
            22=>"22 MOIS",
            23=>"23 MOIS",
            24=>"24 MOIS",
        ];

        if(!empty($start)){

            foreach ($nb_month as $key=>$label) {

                if($key < $start){

                    unset($nb_month[$key]);

                }

            }

        }

        return $nb_month;

    }

    public static function days()
    {
        return [
            "1" =>"LUNDI",
            "2" =>"MARDI",
            "3" =>"MERCREDI",
            "4" =>"JEUDI",
            "5" =>"VENDREDI",
            "6" =>"SAMEDI",
        ];

    }

    public static function status()
    {
        return [
            'A' =>'accordé',
            'E' =>'en cours',
            'R' =>'Refusé',
            'D' =>'Abandonné',
        ];

    }



}
