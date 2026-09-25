<?php
// id_tiers`, `caisse_id`, `type_tiers`, `date_entree`, `nom_tiers`, `prenom_tiers`, `genre`, `date_naiss`, `lieu_naiss`, `type_pi`, `cin`, `date_cin`, `lieu_cin`,
// `code_com_cin`, `num_stat`, `nationalite`, `nif`, `num_rcs`, `juridique`, `phone`, `adresse`, `civilite`, `nom_conjoint`, `phone_conjoint`, `nom_pere`, `nom_mere`, `lieu_residence`, `code_com_resid`, `code_fok_resid`, `profession`, `profession_id`, `revenu_dominant`, `copie_cin`, `photo`, `signature`, `commentaire`, `created_at`, `updated_at`

use App\Lib\Combobox;

return [

    "dateEntree"  =>[
        "field" =>"date_entree",
        "type"  =>"date",
        "size"  =>"lg",
        "label"  =>"Date d'entrée",
        "display"    =>"visible",
        "group" =>1,
    ],
    "civilite"  =>[
        "field" =>"civilite",
        "type"  =>"select",
        "size"  =>"sm",
        "label"  =>"Civilité",
        "display"    =>"visible",
        "options"=>[
            "Mr" =>"MONSIEUR",
            "Mme" =>"MADAME",
            "Mlle" =>"MADEMOISELLE",
        ],
        "group" =>1,
    ],

    "lastName"  =>[
        "field" =>"nom_tiers",
        "type"  =>"text",
        "size"  =>"lg",
        "label"  =>"Nom",
        "display"    =>"visible",
        "group" =>1,
    ],

    "firstName"  =>[
        "field" =>"prenom_tiers",
        "type"  =>"text",
        "size"  =>"lg",
        "label"  =>"Prénoms",
        "display"    =>"visible",
        "group" =>1,
    ],

    "genre"  =>[
        "field" =>"genre",
        "type"  =>"select",
        "size"  =>"sm",
        "label"  =>"Sexe",
        "display"    =>"visible",
        "options"=>[
            "F" =>"FEMININ",
            "M" =>"MASCULIN",
        ],
        "group" =>1,
    ],
    "dateBirth"  =>[
        "field" =>"date_naiss",
        "type"  =>"date",
        "size"  =>"sm",
        "label"  =>"Date de naissance",
        "group" =>1,
        "display"    =>"visible",
        "default"   =>date("Y-m-d"),
    ],

    "placeBirth"  =>[
        "field" =>"lieu_naiss",
        "type"  =>"text",
        "size"  =>"sm",
        "display"    =>"visible",
        "label"  =>"Lieu de naissance",
        "group" =>1,
    ],

    "cin"  =>[
        "field" =>"cin",
        "type"  =>"text",
        "size"  =>"sm",
        "label"  =>"N° pièce d'identitée",
        "display"    =>"visible",
        "group" =>1,
    ],

    "datePiece"  =>[
        "field" =>"date_cin",
        "type"  =>"date",
        "size"  =>"sm",
        "label"  =>"Date de délivrance",
        "group" =>1,
        "display"    =>"visible",
        "default"   =>date("Y-m-d"),
    ],

    "lieuPiece"  =>[
        "field" =>"lieu_cin",
        "type"  =>"text",
        "size"  =>"sm",
        "label"  =>"Lieu de délivrance",
        "group" =>1,
    ],
    //============
    // GROUP 2 : Autres infos
    //============

    "conjoint"  =>[
        "field" =>"nom_conjoint",
        "type"  =>"text",
        "size"  =>"sm",
        "label"  =>"Nom du conjoint(e)",
        "group" =>2,
    ],

    "fatherName"  =>[
        "field" =>"nom_pere",
        "type"  =>"text",
        "size"  =>"sm",
        "label"  =>"Nom du père",
        "display"    =>"visible",
        "group" =>2,
    ],

    "motherName"  =>[
        "field" =>"nom_mere",
        "type"  =>"text",
        "size"  =>"sm",
        "label"  =>"Nom de la mère",
        "group" =>2,
    ],
    "adresse"  =>[
        "field" =>"adresse",
        "type"  =>"text",
        "size"  =>"sm",
        "label"  =>"Adresse",
        "group" =>2,
    ],
    "phone"  =>[
        "field" =>"phone",
        "type"  =>"text",
        "size"  =>"sm",
        "label"  =>"Téléphone",
        "group" =>2,
    ],
    // "mail"  =>[
    //     "field" =>"email",
    //     "type"  =>"text",
    //     "size"  =>"sm",
    //     "label"  =>"Email",
    //     "group" =>2,
    // ],

    "fonction"  =>[
        "field" =>"profession",
        "type"  =>"text",
        "size"  =>"sm",
        "label"  =>"Fonction",
        "group" =>2,
    ],
    "codeFonction"  =>[
        "field" =>"profession_id",
        "type"  =>"select",
        "size"  =>"sm",
        "label"  =>"Code profession",
        "options"   =>Combobox::profession(['P001','P004','P006','P007','P017','P044','P039']),
        "group" =>2,
    ],

];
