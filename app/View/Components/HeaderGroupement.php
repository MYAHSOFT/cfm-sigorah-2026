<?php

namespace App\View\Components;

use App\Models\GroupeSolide;
use Illuminate\View\Component;

class HeaderGroupement extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $groupe = null;

        if(session()->has('id_groupe')){

            $groupe = GroupeSolide::join('tiers', 'cf_groupe_solidarites.id_groupe','=','tiers.id_tiers')
                                ->where('id_groupe', session('id_groupe'))
                                ->first();
        }

        return view('components.header-groupement', [
            'groupe'=>$groupe,
        ]);
    }
}
