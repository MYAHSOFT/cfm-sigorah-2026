<?php

namespace App\Http\Controllers\Groupement;

use App\Http\Controllers\Controller;
use App\Models\GroupeSolideMembre;
use Illuminate\Http\Request;

class MembreInController extends Controller
{

    public function store(Request $request)
    {

        GroupeSolideMembre::where('tiers_id', $request->folio)
                    ->where('groupe_id', session('id_groupe'))
                    ->update(['status'  =>'A']);

        return redirect()->route('gp.mbre.index');

    }

}
