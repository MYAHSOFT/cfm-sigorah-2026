<?php
//`id_cycle`, `id_groupe`, `debut_cycle`, `fin_cycle`, `mode_reunion`

return [

    "dateFirstRemb"  =>[
        "field" =>"date_first_remb",
        "type"  =>"date",
        "size"  =>"lg",
        "label"  =>"Date de premier remboursement",
    ],

    "mode"  =>[
        "field" =>"mode_reunion",
        "type"  =>"select",
        "size"  =>"sm",
        "label"  =>"Fréquence de réunion",
        "options"=>config('groupement.mode_reunion'),
    ],

    "jour"  =>[
        "field" =>"jour_reunion",
        "type"  =>"select",
        "size"  =>"sm",
        "label"  =>"Jour de réunion",
        "options"=>App\Lib\Combobox::days(),
    ],

];
