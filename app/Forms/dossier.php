<?php
//`id_cycle`, `id_groupe`, `debut_cycle`, `fin_cycle`, `mode_reunion`

return [

    "dateDebut"  =>[
        "field" =>"debut_cycle",
        "type"  =>"date",
        "size"  =>"lg",
        "label"  =>"Date de la demande",
    ],

    "dateOctroi"  =>[
        "field" =>"date_prev_octroi",
        "type"  =>"date",
        "size"  =>"lg",
        "label"  =>"Date prévue pour octroi",
    ],

    "dateFin"  =>[
        "field" =>"fin_cycle",
        "type"  =>"date",
        "size"  =>"lg",
        "label"  =>"Date prévue fin cycle",
    ],

    "mode"  =>[
        "field" =>"mode_reunion",
        "type"  =>"select",
        "size"  =>"sm",
        "label"  =>"Fréquence de réunion",
        "options"=>config('groupement')['mode_reunion'],
    ],

    "jour"  =>[
        "field" =>"jour_reunion",
        "type"  =>"select",
        "size"  =>"sm",
        "label"  =>"Jour de réunion",
        "options"=>App\Lib\Combobox::days(),
    ],

];
