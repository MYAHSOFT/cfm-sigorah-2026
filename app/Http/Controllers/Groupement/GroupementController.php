<?php

namespace App\Http\Controllers\Groupement;

use App\Models\Caisse;
use App\Models\Employe;
use App\Models\GroupeSolide;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\TiersCFRequest;
use App\Models\CFFonctionGroupe;
use App\Models\Compte;
use App\Repositories\EmployeRepository;
use App\Repositories\GroupeSolideMembreRepository;
use App\Repositories\TiersRepository;
use App\Repositories\GroupeSolideRepository;
use Illuminate\Support\Facades\Validator;

class GroupementController extends Controller
{

    protected $request;

    public function __construct(Request $request)
    {

        $this->request = $request;

    }

    public function home()
    {

        return view('groupement.gp_solidarites.home');

    }

    /**
     * Liste de groupement occupé par une animatrice
     */

    public function index()
    {

        $user = \Auth::user();

        $groupes = GroupeSolide::join('tiers', 'cf_groupe_solidarites.id_groupe','=','tiers.id_tiers')
                        ->where('agent_id', $user->name)
                        ->where(function($query){

                            $id_groupe = !empty($this->request->groupeId) ? $this->request->groupeId : null;

                            if(!empty($id_groupe)){
                                $query->orWhere('num_caisse', '=', $id_groupe);
                            }

                        })
                        ->orderBy('nom_tiers','asc')
                        ->get();

        return view('groupement.groupesolides.index', [
            'groupes' =>$groupes,
            'groupe_id'    =>$this->request->groupeId,
        ]);

    }


    public function search(Request $request)
    {

        session()->put('groupement', json_encode($request->all()));

        $route = route('gp.groupe.index',['list'=>'on']);

        if($request->page == 'membre'){

            $groupe = GroupeSolide::orWhere('id_groupe', $request->codeGroupe)
                            ->orWhere('num_caisse', $request->codeGroupe)
                            ->first();

            $route = route('gp.groupe.membre');

            if(!empty($groupe->id_groupe)){
                $route = route('gp.groupe.membre', $groupe->id_groupe);
            }else{
                session()->flash("warning","Le groupement sélectionné n'existe pas.");
            }

        }

        return redirect($route);

    }

    public function checked($id_groupe)
    {

        $groupe = GroupeSolide::where('id_groupe', $id_groupe)->first();

        if(empty($groupe->id_groupe)){
            return redirect()->route('gt.index');
        }

        session()->put('id_groupe', $groupe->id_groupe);

        return redirect()->route('home');

    }

    public function groupe(
        TiersRepository $_tiers,
        GroupeSolideMembreRepository $_membre_groupe,
        Request $request
    )
    {

        $tiers = (object)[];

        $president = null;

        session()->forget('warning');

        if($request->isMethod('post')){

            $groupe = $_tiers->byIdentifiant($request->search);

            if(!empty($groupe->id_tiers)){
                $tiers = $groupe->type_tiers == "GP" ? $groupe : (object)[];
            }

            if(!empty($groupe->id_tiers)){

                $president = $_membre_groupe->getMembreBureauByFonction($groupe->id_groupe, 'PRD');

            }else{
                session()->flash("warning", "Aucun groupement ne correspond pas à ce Folio");
            }

        }

        return view('groupement.gp_solidarites.search',[
            'title' =>"Création de groupement",
            'request'=>$request,
            'url'   =>route('gp.groupe.new'),
            'tiers' =>$tiers,
            'president' =>$president
        ]);

    }

    public function create(
        TiersRepository $_tiers,
        $id_tiers)
    {

        $tiers = $_tiers->byIdentifiant($id_tiers);

        if(empty($tiers->id_tiers)){
            return abort(404);
        }

        $groupe = GroupeSolide::where('tiers_id', $id_tiers)->first();


        if(!empty($groupe->id_groupe)){
            session()->flash('info', "Ce groupe a été déjà crée. Vous pouvez actuellement y ajouter des membres.");
            return redirect()->route('gp.groupe.show', $groupe->id_groupe);
        }

        return view('groupement.gp_solidarites.create',[
            'tiers' =>$tiers
        ]);

    }

    public function store(Request $request, $id_tiers)
    {

        try {

            // $data['id_groupe'] = \App\Lib\Referentiel::groupeId($request->caisse);

            $data['id_groupe_old'] = $request->caisse.substr(10000 + (int)$request->numGroupe,-3);
            $data['id_groupe'] = (int)$id_tiers;

            $data['tiers_id'] = (int)$id_tiers;
            $data['animatrice_id'] = $request->employe;
            $data['num_caisse'] = $request->numGroupe;

            $validator = Validator::make($data, [
                'id_groupe' =>'unique:App\Models\GroupeSolide,id_groupe',
            ]);

            if($validator->fails()){
                session()->flash("warning","Le numéro de groupement existe déjà.");
                return back();
            }

            $groupe = GroupeSolide::create($data);

            return redirect()->route('gp.groupe.show', $groupe->id_groupe);

        } catch (\Throwable $th) {

            return abort($th);

        }

    }

    public function show(
        GroupeSolideRepository $_groupe,
        $id_groupe)
    {

        $groupe = $_groupe->find($id_groupe);

        if(empty($groupe)){
            return abort(404);
        }

        $fields = \App\Lib\Forms::get('tierscf');

        $tiers_data = [];

        foreach ($fields as $key => $value) {
            $field = $value['field'];
            $attribue['value'] = $groupe->$field;
            $attribue['label'] = $value['label'];
            $attribue['group'] = $value['group'];
            $attribue['type'] = $value['type'];
            $attribue['options'] = key_exists('options',$value) ? $value['options'] : [];
            $tiers_data[$key] = (object)$attribue;
        }

        $compte = Compte::where('tiers_id', $groupe->tiers_id)
                        ->where('produit_id', config('compte')['produit_base'])
                        ->first();

        $employe = Employe::where('id_employe', $groupe->animatrice_id)->first();

        return view('groupement.gp_solidarites.show',[

            'tiers' =>(object)$tiers_data,
            'employe'   =>$employe,
            'id_groupe' =>$id_groupe,
            'compte' =>$compte,
            'groupe'    =>$groupe,

        ]);
    }

    public function membre(
        Request $request,
        GroupeSolideMembreRepository $_membre_groupe,
        GroupeSolideRepository $_groupe,
        $id_groupe = null)
    {

        $groupe_id = $id_groupe;

        if($request->isMethod('post') && !empty($request->codeGroupe)){
            $groupe_id = $request->codeGroupe;
        }

        $groupe = $_groupe->find($groupe_id);

        $per_page = 10;

        $tiers = $_membre_groupe->membreGroupeByStatus($groupe_id,'A', $per_page);

        $president = $_membre_groupe->getMembreBureauByFonction($groupe_id, 'PRD');

        $bureau = $_membre_groupe->getBureau($groupe_id);

        $fonctions = CFFonctionGroupe::get();

        $compte = Compte::join('cf_groupe_solidarites','ep_comptes.tiers_id','=','cf_groupe_solidarites.id_groupe')
                        ->where('id_groupe', $id_groupe)
                        ->first();
        return view('groupement.gp_solidarites.membre',[

            'groupe' =>$groupe,
            'tiers' =>$tiers,
            'compte' =>$compte,
            'fonctions' =>$fonctions,
            'bureau' =>$bureau,
            'president' =>$president,
            'request'    =>$request,

        ]);

    }

}
