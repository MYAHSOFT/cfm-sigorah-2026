<?php

namespace App\Http\Controllers\Groupement;

use App\Http\Controllers\Controller;
use App\Models\Bank\Employe;
use App\Repositories\EmployeRepository;
use Illuminate\Http\Request;

class AnimatriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(
        EmployeRepository $_employe
    )
    {

        // dd(session()->all());

        $employe = (\Auth::user())->employe;

        $per_page = 10;

        $search = session()->has('cf_employe') ? json_decode(session('cf_employe')) : (object)[];

        $employes = $_employe->employeByResponsable($employe->id_employe, $per_page);

        return view('groupement.animatrices.index', [
            'employes'  =>$employes,
            'search'    =>$search,
        ]);

    }

    public function search(Request $request)
    {

        session()->put('cf_employe', json_encode($request->all()));

        return redirect()->route('gp.animatrice.index');

    }

    public function open($id_employe)
    {

        session()->put('animatrice', $id_employe);

        return redirect()->route('gp.home');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id_employe)
    {

        $employe = Employe::where('id_employe', $id_employe)->first();

        if(empty($employe->id_employe)){

            session()->flash("warning", "L'employé sélectinné n'existe pas dans la base de données");
            return abort(404);
        }

        $attributes = \App\Lib\Forms::show($employe, 'employe');

        return view('groupement.animatrices.show', [
            'employe'   =>$employe,
            'attributes'   =>$attributes,
        ]);
    }

    public function animatrice()
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
}
