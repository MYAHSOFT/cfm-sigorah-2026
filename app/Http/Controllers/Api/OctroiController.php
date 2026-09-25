<?php

namespace App\Http\Controllers\Api;

use App\Models\CalendrierCycle;
use App\Models\Calendrier;
use App\Models\CDOperation;
use App\Models\CFDossier;
use App\Models\CFEcheancier;
use App\Models\Compte;
use App\Models\ContratPret;
use App\Models\ContratPretGroupe;
use App\Models\DemandePretGroupe;
use App\Models\Journal;
use App\Models\ProduitCredit;
use App\Repositories\DemandePretGroupeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Miroir API de Groupement\OctroiPretController.
 *
 * Différences volontaires avec le web (voir audit / feuille de route lot 4) :
 *  - le panier session 'cf_cart_octroi' devient `lignes:[{ref_dde, montant}]` ;
 *  - TOUTES les écritures sont dans une seule transaction ;
 *  - garde d'idempotence : 409 si le dossier est déjà octroyé (statut_octroi = 1) ;
 *  - identifiants de journal sur Str::ulid() plutôt que uniqid().
 * La logique de calcul d'échéancier reste celle de App\Lib\CalendrierGroupement
 * en attendant son extraction en EcheancierCalculator pur.
 */
class OctroiController extends ApiController
{
    /** Dossiers approuvés en attente d'octroi pour l'animatrice. */
    public function index()
    {
        $nonOctroyes = CFDossier::leftJoin('cf_contrat_pret_groupes', 'cf_dossiers.id_dossier', 'cf_contrat_pret_groupes.dossier_id')
            ->where('animatrice', $this->agentCode())
            ->whereNull('dossier_id')
            ->pluck('cf_dossiers.id_dossier');

        $demandes = DemandePretGroupe::join('cd_demandes', 'cf_demande_pret_groupes.ref_dde', 'cd_demandes.id_demande')
            ->join('cf_dossiers', 'cf_demande_pret_groupes.dossier_id', 'cf_dossiers.id_dossier')
            ->join('cf_groupe_solidarites', 'cf_dossiers.groupe_id', 'cf_groupe_solidarites.id_groupe')
            ->where('animatrice', $this->agentCode())
            ->whereIn('dossier_id', $nonOctroyes)
            ->select('date_demande', 'dossier_id', 'num_caisse', DB::raw('SUM(mtt_capital) mtt_capital'))
            ->groupBy('date_demande', 'dossier_id', 'num_caisse')
            ->get();

        return $this->data($demandes);
    }

    /** État d'octroi d'un dossier : demandes + montants recommandés. */
    public function showByDossier(DemandePretGroupeRepository $repo, CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        return $this->data([
            'dossier'      => $dossier,
            'demandes'     => $repo->get($dossier->id_dossier),
            'nb_demande'   => $repo->count($dossier->id_dossier),
            'sum_demande'  => $repo->sum($dossier->id_dossier),
            'deja_octroye' => $dossier->statut_octroi === '1' || $dossier->statut_octroi === 1,
        ]);
    }

    /**
     * Simulation : renvoie le calendrier-cycle et l'échéancier par membre SANS rien écrire.
     * Body: { date_contrat }
     */
    public function simulate(Request $request, DemandePretGroupeRepository $repo, CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        $request->validate(['date_contrat' => ['required', 'date']]);
        $dossier->date_octroi_effectif = $request->input('date_contrat');

        return $this->data([
            'calendrier_cycle'    => \App\Lib\CalendrierGroupement::getGroupe($dossier, $repo, 'A'),
            'echeancier_membres'  => \App\Lib\CalendrierGroupement::echeancierParMembre($dossier, $repo, 'A'),
        ]);
    }

    /**
     * Exécute l'octroi.
     * Body: { date_contrat, produit_id, lignes:[{ref_dde, montant}] }
     */
    public function store(Request $request, DemandePretGroupeRepository $repo, CFDossier $dossier)
    {
        $this->authorize('access', $dossier);

        $payload = $request->validate([
            'date_contrat'       => ['required', 'date'],
            'produit_id'         => ['required'],
            'lignes'             => ['required', 'array', 'min:1'],
            'lignes.*.ref_dde'   => ['required'],
            'lignes.*.montant'   => ['required', 'numeric', 'gt:0'],
        ]);

        $produit = ProduitCredit::where('id_produit', $payload['produit_id'])->first();
        abort_if(! $produit, 422, 'Produit de crédit inconnu.');

        // ref_dde -> montant
        $cart = [];
        foreach ($payload['lignes'] as $ligne) {
            $cart[$ligne['ref_dde']] = (float) $ligne['montant'];
        }

        // Contrôle d'appartenance des demandes au dossier + plafond montant recommandé.
        $refs = array_keys($cart);
        $demandesDossier = DemandePretGroupe::join('cd_demandes', 'cf_demande_pret_groupes.ref_dde', 'cd_demandes.id_demande')
            ->where('dossier_id', $dossier->id_dossier)
            ->whereIn('cd_demandes.id_demande', $refs)
            ->get()
            ->keyBy('id_demande');

        foreach ($refs as $ref) {
            abort_if(! $demandesDossier->has($ref), 422, "La demande $ref n'appartient pas à ce dossier.");
            if ($cart[$ref] > (float) $demandesDossier[$ref]->mtt_recommande) {
                abort(422, "Le montant octroyé dépasse le montant recommandé pour la demande $ref.");
            }
        }

        $result = DB::transaction(function () use ($dossier, $produit, $cart, $payload, $repo) {
            $locked = CFDossier::where('id_dossier', $dossier->id_dossier)->lockForUpdate()->first();
            if ($locked->statut_octroi === '1' || $locked->statut_octroi === 1) {
                abort(409, 'Ce dossier a déjà été octroyé.');
            }

            $dossier->date_octroi_effectif = $payload['date_contrat'];
            $locked->update(['date_octroi_effectif' => $payload['date_contrat']]);

            if (CalendrierCycle::where('dossier_id', $dossier->id_dossier)->count() === 0) {
                $calendriers = (object) \App\Lib\CalendrierGroupement::getGroupe($dossier, $repo, 'A');
                $cals = [];
                foreach ($calendriers as $c) {
                    $cals[] = ['dossier_id' => $dossier->id_dossier, 'date_oper' => $c->date_oper, 'montant' => $c->montant];
                }
                DB::table('cf_calendier_cycles')->insert($cals);
            }

            $firstRemb = CalendrierCycle::where('dossier_id', $dossier->id_dossier)->min('date_oper');
            $echeance  = CalendrierCycle::where('dossier_id', $dossier->id_dossier)->max('date_oper');
            $echeanciersMembres = \App\Lib\CalendrierGroupement::echeancierParMembre($dossier, $repo, 'A');

            $compte = Compte::where('tiers_id', $dossier->id_tiers)
                ->where('produit_id', config('groupement.produit_base'))
                ->first();

            $numPiece = strtoupper(Str::ulid());
            $contrats = [];

            foreach ($cart as $refDde => $montant) {
                $demande = $repo->find($refDde);

                $contrat = [
                    'id_pret'      => \App\Lib\Referentiel::contratPretId($demande->caisse_id, $payload['date_contrat']),
                    'demande_id'   => $refDde,
                    'compte_id'    => $compte?->id_compte,
                    'date_contrat' => $payload['date_contrat'],
                    'premier_remb' => $firstRemb,
                    'duree_mois'   => $produit->duree_max,
                    'frequence'    => 1,
                    'echeance'     => $echeance,
                    'mtt_capital'  => $montant,
                    'mtt_interet'  => $montant * $produit->taux_interet,
                    'taux_interet' => $produit->taux_interet,
                    'frais'        => $produit->frais,
                    'produit_id'   => $produit->id_produit,
                ];

                $echeanciers = [];
                if (array_key_exists($demande->tiers_id, $echeanciersMembres)) {
                    foreach ($echeanciersMembres[$demande->tiers_id] as $ech) {
                        $echeanciers[] = [
                            'pret_id'   => $contrat['id_pret'],
                            'date_oper' => $ech->date_oper,
                            'montant'   => $ech->montant,
                        ];
                    }
                }

                $idJournal = strtoupper(Str::ulid());
                Journal::create([
                    'id_journal'   => $idJournal,
                    'exo_id'       => config('groupement.exo'),
                    'caisse_id'    => config('groupement.caisseId'),
                    'employe_id'   => $dossier->animatrice,
                    'date_oper'    => $payload['date_contrat'],
                    'cloture'      => 'N',
                    'type_journal' => 'JOD',
                    'module'       => 'G',
                    'libelle'      => 'JOURNAL DCF ' . $dossier->animatrice,
                ]);

                ContratPret::create($contrat);
                ContratPretGroupe::create([
                    'pret_id'    => $contrat['id_pret'],
                    'groupe_id'  => $demande->groupe_id,
                    'dossier_id' => $dossier->id_dossier,
                ]);
                CDOperation::create([
                    'pret_id'    => $contrat['id_pret'],
                    'journal_id' => $idJournal,
                    'num_piece'  => $numPiece,
                    'date_oper'  => $contrat['date_contrat'],
                    'debit'      => $contrat['mtt_capital'],
                ]);
                Calendrier::create([
                    'pret_id'   => $contrat['id_pret'],
                    'date_oper' => $echeance,
                    'capital'   => $contrat['mtt_capital'],
                    'interet'   => $contrat['mtt_capital'] * $produit->taux_interet,
                ]);
                if ($echeanciers) {
                    CFEcheancier::insert($echeanciers);
                }

                $contrats[] = $contrat;
            }

            $locked->update(['statut' => 'A', 'statut_octroi' => '1']);

            return $contrats;
        });

        return $this->data(['contrats' => $result], 201);
    }
}
