<?php

return [

    'exo'   =>'2024',

    'caisseId'    =>'1410',

    'produit_base'  =>'01',
    
    'duree_pret'    =>4, //Durée de prêt pardefaut

    'bureau'    =>[
        'PRD'   =>"PRESIDENT",
        'TRE'   =>"TRESORIERE",
        'SCE'   =>"SECRETAIRE",
        'COM'   =>"COMMISSAIRE AU COMPTE",
        'SAG'   =>"SAGE"
    ],

    'mode_reunion'  =>[
        "1" =>"HEBDO",
        "2" =>"BIMENSUEL",
        "4" =>"MENSUEL",
    ],

    'nb_reunion'    =>[
        1=>16,
        2=>8,
        4=>4,
    ],

    'taux_interet'  =>0.18


];
