<?php

namespace App\Http\Controllers\Api;

use App\Models\CFDemandePret;
use App\Models\CFDossier;
use App\Models\DemandePret;
use App\Models\DemandePretGroupe;
use App\Models\GroupeSolide;
use App\Repositories\CFDossierRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Miroir API de Groupement\DossierController.
 * Un dossier = un cycle de crédit d'un groupement.
 */
class DossierController extends ApiController
{
    /** Dossiers d'un groupement (option ?annee=YYYY|all). */
    public function index(Request $request, GroupeSolide $groupe)
    {
        $this->authorize('access', $groupe);

        $annee = $request->input('annee', date('Y'));

        $dossiers = CFDossier::join('cf_demande_pret_groupes', 'cf_dossiers.id_dossier', 'cf_demande_pret_groupes.dossier_id')
            ->join('cd_demandes', 'cf_demande_pret_groupes.ref_dde', 'cd_demandes.id_demande')
            ->where('cf_dossiers.groupe_id', $groupe->id_groupe)
            ->when($annee !== 'all', fn ($q) => $q->whereYear('debut_cycle', $annee))
            ->when($annee === 'all', fn ($q) => $q->whereYear('debut_cycle', '<=', date('Y')))
            ->select('id_dossier', 'debut_cycle', DB::raw('SUM(mtt_capital) mtt_capital, SUM(mtt_recommande) mtt_recommande'))
            ->groupBy('id_dossier', 'debut_cycle')
            ->orderBy('debut_cycle', 'desc')
            ->get();

        return $this->data($dossiers);
    }

    /** Détail d'un dossier. */
    public function show(CFDossierRepository $repo, CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        return $this->data($repo->find($dossier->id_dossier) ?? $dossier);
    }

    /**
     * Crée un cycle + ses demandes-cycle (miroir de DossierController::store).
     * Body: { date_debut, date_fin, date_octroi, mode, jour, objet?, lignes:[{folio,montant,objet}] }
     */
    public function store(Request $request, GroupeSolide $groupe)
    {
        $this->authorize('access', $groupe);

        $data = $request->validate([
            'date_debut'  => ['required', 'date'],
            'date_fin'    => ['required', 'date'],
            'date_octroi' => ['required', 'date'],
            'mode'        => ['required'],
            'jour'        => ['required'],
            'objet'       => ['nullable', 'in:credit,depot'],
            'lignes'              => ['required', 'array', 'min:1'],
            'lignes.*.folio'      => ['required'],
            'lignes.*.montant'    => ['required', 'numeric', 'gt:0'],
            'lignes.*.objet'      => ['required', 'string'],
        ]);

        $nbReunions = config('groupement')['nb_reunion'];
        $mode = array_key_exists($data['mode'], $nbReunions) ? $data['mode'] : 1;
        $nbReunion = $nbReunions[$mode] ?? current($nbReunions);

        $idDossier = \App\Lib\Referentiel::dossierId($groupe->id_groupe, $data['date_debut']);

        $dossier = [
            'id_dossier'       => $idDossier,
            'groupe_id'        => $groupe->id_groupe,
            'debut_cycle'      => $data['date_debut'],
            'fin_cycle'        => $data['date_fin'],
            'date_prev_octroi' => $data['date_octroi'],
            'date_prev_remb'   => $data['date_fin'],
            'mode_reunion'     => $mode,
            'jour_reunion'     => $data['jour'],
            'nb_reunion'       => $nbReunion,
            'statut'           => 'O',
            'animatrice'       => $this->agentCode(),
        ];

        DB::transaction(function () use ($dossier, $data) {
            CFDossier::create($dossier);

            $rows = [];
            foreach ($data['lignes'] as $ligne) {
                $rows[] = [
                    'dossier_id'  => $dossier['id_dossier'],
                    'groupe_id'   => $dossier['groupe_id'],
                    'tiers_id'    => $ligne['folio'],
                    'mtt_capital' => $ligne['montant'],
                    'objet_pret'  => $ligne['objet'],
                ];
            }
            CFDemandePret::insert($rows);
        });

        return $this->data(CFDossier::find($idDossier), 201);
    }

    /**
     * Variante « demandes définitives » (miroir de DossierController::storeDemande) :
     * crée le dossier + cd_demandes + cf_demande_pret_groupes.
     */
    public function storeDefinitif(Request $request, GroupeSolide $groupe)
    {
        $this->authorize('access', $groupe);

        $data = $request->validate([
            'date_debut'  => ['required', 'date'],
            'date_fin'    => ['required', 'date'],
            'date_octroi' => ['required', 'date'],
            'mode'        => ['required'],
            'jour'        => ['required'],
            'lignes'              => ['required', 'array', 'min:1'],
            'lignes.*.folio'      => ['required'],
            'lignes.*.montant'    => ['required', 'numeric', 'gt:0'],
            'lignes.*.objet'      => ['required', 'string'],
        ]);

        $nbReunions = config('groupement')['nb_reunion'];
        $mode = array_key_exists($data['mode'], $nbReunions) ? $data['mode'] : 1;
        $nbReunion = $nbReunions[$mode] ?? current($nbReunions);
        $caisseId = config('groupement.caisseId');

        $idDossier = \App\Lib\Referentiel::dossierId($groupe->id_groupe, $data['date_debut']);

        $dossier = [
            'id_dossier'       => $idDossier,
            'groupe_id'        => $groupe->id_groupe,
            'debut_cycle'      => $data['date_debut'],
            'fin_cycle'        => $data['date_fin'],
            'date_prev_octroi' => $data['date_octroi'],
            'date_prev_remb'   => $data['date_fin'],
            'mode_reunion'     => $mode,
            'jour_reunion'     => $data['jour'],
            'nb_reunion'       => $nbReunion,
            'statut'           => 'O',
            'animatrice'       => $this->agentCode(),
        ];

        DB::transaction(function () use ($dossier, $data, $caisseId, $groupe) {
            CFDossier::create($dossier);

            foreach ($data['lignes'] as $ligne) {
                $idDemande = \App\Lib\Referentiel::demandeId($caisseId, $dossier['debut_cycle']);

                DemandePret::create([
                    'id_demande'     => $idDemande,
                    'ref_demande'    => $idDemande,
                    'tiers_id'       => $ligne['folio'],
                    'caisse_id'      => $caisseId,
                    'date_demande'   => $dossier['debut_cycle'],
                    'mtt_capital'    => $ligne['montant'],
                    'mtt_recommande' => $ligne['montant'],
                    'decision'       => 'E',
                    'genre_demande'  => 'CAE',
                    'objet_pret'     => $ligne['objet'],
                    'duree_mois'     => config('groupement.duree_pret'),
                ]);

                DemandePretGroupe::create([
                    'ref_dde'    => $idDemande,
                    'groupe_id'  => $groupe->id_groupe,
                    'dossier_id' => $dossier['id_dossier'],
                ]);
            }
        });

        return $this->data(CFDossier::find($idDossier), 201);
    }

    /** Mise à jour des paramètres d'un cycle. */
    public function update(Request $request, CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        $data = $request->validate([
            'date_debut'  => ['required', 'date'],
            'date_fin'    => ['required', 'date'],
            'date_octroi' => ['required', 'date'],
            'mode'        => ['required'],
            'jour'        => ['required'],
        ]);

        $nbReunions = config('groupement')['nb_reunion'];
        $mode = array_key_exists($data['mode'], $nbReunions) ? $data['mode'] : 1;
        $nbReunion = $nbReunions[$mode] ?? current($nbReunions);

        $dossier->update([
            'debut_cycle'      => $data['date_debut'],
            'fin_cycle'        => $data['date_fin'],
            'date_prev_octroi' => $data['date_octroi'],
            'date_prev_remb'   => $data['date_fin'],
            'mode_reunion'     => $mode,
            'jour_reunion'     => $data['jour'],
            'nb_reunion'       => $nbReunion,
            'statut'           => 'O',
        ]);

        return $this->data($dossier->fresh());
    }
}
