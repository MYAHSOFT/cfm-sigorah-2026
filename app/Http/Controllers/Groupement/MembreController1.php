<?php

namespace App\Http\Controllers\Groupement;

use App\Models\Customer\Customer;
use App\Models\Bank\Teller;
use App\Models\Association\Group;
use Illuminate\Http\Request;
use App\Models\Association\Member;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\MembreRequest;
use App\Repositories\TiersRepository;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\TiersMembreCFRequest;
use App\Http\Controllers\Tiers\TiersController;

class MembreController extends Controller
{

    protected $id_groupe;

    protected $tiers;

    protected $request;

    public function home()
    {

        return view('groupement.membres.home');

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(
        TiersRepository $_tiers)
    {

        $search = session()->has('tiers') ? json_decode(session('tiers')) : (object)[];

        $tiers = $_tiers->get('A');

        return view('groupement.membres.index', [
            'tiers' =>$tiers,
            'search'    =>$search
        ]);

    }

    public function search(Request $request)
    {

        $data = $request->all();

        if(empty($request->input('caisse'))){
            $data['caisse'] = session('agence');
        }

        session()->put('tiers', json_encode($data));

        return redirect()->route('gp.mbre.index');

    }

    public function forget()
    {
        session()->forget('tiers');

        return redirect()->route('gp.mbre.index');

    }

    public function createGroupe()
    {
        session()->flash('info', "Veuillez d'abord sélectionner le groupement concerné.");

        return redirect()->route('gp.groupe.index');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $groupe = Group::where('id_groupe', session('id_groupe'))->first();

        if(empty($groupe->id_groupe)){
            return abort(404);
        }

        $fields = json_decode(json_encode((object) \App\Lib\Forms::get('membrecf')));

        $caisse = Teller::where('agence_id', session('agence'))->first();

        return view("groupement.membres.create", [
            'main'  =>'groupement.membres.main',
            'url'   =>route("gp.mbre.store"),
            'caisse'    =>$caisse,
            'groupe'    =>$groupe,
            'fields'    =>$fields,
            'id_tiers'    =>'',
            'id_groupe' =>$groupe->id_groupe,
        ]);
    }

   /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MembreRequest $request)
    {

        $this->request = $request;

        DB::transaction(function () {

            $data = \App\Lib\Forms::input($this->request, 'membrecf');

            $data['id_tiers'] = \App\Lib\Referentiel::tiers($this->request->caisse);
            $data['caisse_id'] = $this->request->caisse;
            $data['type_tiers'] = 'PP';
            $data['type_pi'] = '1';
            $data['civilite'] = $data['genre'] == 'F' ? 'Mme' : 'Mr';
            $data['nationalite'] = 'MG';

            $this->tiers = Customer::create($data);

            Member::create([
                'tiers_id'  =>$data['id_tiers'],
                'groupe_id'  =>$this->request->groupeId,
                'date_entree'  =>$data['date_entree'],
                'fonction_id'   =>'MBR',
            ]);

            session()->flash(
                'success',
                "Le nouveau tiers a bien été enregisté avec succès."
            );

        });


        return redirect()->route('gp.mbre.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id_tiers)
    {

        $tiers = Customer::where('id_tiers', $id_tiers)->first();

        if(empty($tiers)){
            return abort(404);
        }

        $membre = Member::where('groupe_id', session('id_groupe'))
                        ->where('tiers_id', $id_tiers)
                        ->first();

        $fields = \App\Lib\Forms::show($tiers, 'membrecf');

        $photo = !empty($tiers->photo)?\App\Lib\Image::get(env('IMG_TIERS').DIRECTORY_SEPARATOR.$tiers->photo):"";

        $signature = !empty($tiers->signature)?\App\Lib\Image::get(env('IMG_SIGNATURE').DIRECTORY_SEPARATOR.$tiers->signature):"";

        $copie_cin = !empty($tiers->copie_cin)?\App\Lib\Image::get(env('IMG_CIN').DIRECTORY_SEPARATOR.$tiers->copie_cin):"";


        $params  = (object)[
            'url'   =>route('gp.mbre.upload', $tiers->id_tiers),
        ];

        return view("groupement.membres.show",[

            'fields' =>$fields,
            'membre'  =>$membre,
            'id_tiers'  =>$id_tiers,
            'type_tiers'    =>'pp',
            'photo'  =>$photo,
            'signature'  =>$signature,
            'copie_cin'  =>$copie_cin,
            'para'  =>$copie_cin,
            'params'  =>$params,
            'url'  =>route('gp.mbre.show', $tiers->id_tiers),
            'viewMain'  =>'groupement.membres.main',
            'module'    =>'groupement',

        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id_tiers)
    {

        $tiers = Customer::where('id_tiers', $id_tiers)->first();

        if(empty($tiers)){
            return abort(404);
        }

        $groupe = Group::where('id_groupe', session('id_groupe'))->first();

        $fields = \App\Lib\Forms::get('membrecf');

        $tiers_data = [];

        foreach ($fields as $key => $value) {

            $field = $value['field'];
            $attribue['value'] = $tiers->$field;
            $attribue['label'] = $value['label'];
            $attribue['group'] = $value['group'];
            $attribue['type'] = $value['type'];
            $attribue['options'] = key_exists('options',$value) ? $value['options'] : [];

            $tiers_data[$key] = (object)$attribue;

        }

        $caisse = Teller::where('agence_id', session('agence'))->first();

        $fields = \App\Lib\Forms::show($tiers, 'membrecf');

        return view("groupement.membres.create", [
            'main'  =>"groupement.membres.main",
            'url'   =>route("gp.mbre.update", $id_tiers),
            'fields'    =>$tiers_data,
            'caisse'  =>$caisse,
            'groupe'  =>$groupe,
            'id_tiers'  =>$id_tiers,
        ]);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(MembreRequest $request, $id_tiers)
    {

        $data = \App\Lib\Forms::input($request, 'tierspp');

        unset($data['caisse_id']);

        Customer::where('id_tiers', $id_tiers)->update($data);

        session()->flash(
            'success',
            "La modification a bien été enregistré avec succès."
        );

        return redirect()->route('gp.mbre.show', $id_tiers);

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function upload(Request $request, $id_tiers)
    {

        try {


            $tiers = Customer::where('id_tiers', $id_tiers)->first();

            $validator = Validator::make($request->all(), [
                    'file'  =>'required|file|image|mimes:jpg,png'
                ], [
                    'file.required' =>"Le ficher est obligatoire",
                    'file.image' =>"Le ficher doit être une image",
                    'file.mimes' =>"Le fichier doit être au format jpg ou png",
                ]);

            if($validator->fails()){

                $errors = $validator->errors();

                $messages = [];

                foreach ($errors->all() as $error) {
                    $messages[] = $error;
                }

                $err = implode(', ', $messages);

                $msg = count($messages) > 1
                            ? "Des erreurs ont été constatées lors de l'importation de fichier : $err"
                            : "Une erreur a été constaté lors de l'importation de fichier : $err";

                session()->flash("warning", $msg);

                return back()->withErrors($validator)->withInput();

            }

            $path = $request->file('file');

            $name = $id_tiers.'_'.$path->hashName();

            // dd(env('DISK_IMG'));


            $file_name = $path->storeAs('tiers/'.$request->objetFile, $name, 'img');


            $columns = [
                'photo' =>'photo',
                'signature' =>'signature',
                'cin' =>'copie_cin',
            ];

            $data = [$columns[$request->objetFile]=>$name];

            Customer::where('id_tiers', $tiers->id_tiers)->update($data);

            return redirect($request->urlModule);


        } catch (\Throwable $th) {

            return abort($th);

        }

    }
}
