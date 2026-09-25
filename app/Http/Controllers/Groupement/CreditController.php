<?php

namespace App\Http\Controllers\Groupement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreditController extends Controller
{

    public function home()
    {

        return redirect()->route('gp.pretactif.index', ['list'=>'on']);

        // return view('groupement.credits.home');
    }

}
