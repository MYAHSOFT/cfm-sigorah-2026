<?php

namespace App\Http\Controllers\Api;

use App\Models\Association\Cycle;
use App\Models\Lending\ApplicationLoan;
use App\Models\Association\GroupLoanApplication;
use App\Models\Association\Group;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Miroir API de Groupement\DemandeCreditController.
 * Le panier session 'cf_create_dde' est remplacé par un tableau `lignes` dans le POST.
 */
class DemandeController extends ApiController
{
    /** File des demandes de l'animatrice (statuts O / P). */
    public function index()
    {
        $demandes = GroupLoanApplication::join('cf_dossiers', 'cf_demande_pret_groupes.dossier_id', 'cf_dossiers.id_dossier')
            ->join('cd_demandes', 'cf_demande_pret_groupes.ref_dde', 'cd_demandes.id_demande')
            ->join('cf_groupe_solidarites', 'cf_dossiers.groupe_id', 'cf_groupe_solidarites.id_groupe')
            ->join('tiers', 'cf_groupe_solidarites.id_groupe', 'tiers.id_tiers')
            ->whereIn('statut', ['O', 'P'])
            ->where('animatrice', $this->employe()->id_employe)
            ->select('nom_tiers', 'id_groupe', 'num_caisse', 'id_dossier', 'debut_cycle', 'statut', DB::raw('SUM(mtt_capital) mtt_capital'))
            ->groupBy('nom_tiers', 'id_groupe', 'num_caisse', 'id_dossier', 'debut_cycle', 'statut')
            ->get();

        return $this->data([
            'demandes'    => $demandes,
            'nb_demande'  => $demandes->count(),
            'sum_demande' => $demandes->sum('mtt_capital'),
        ]);
    }

    /** Membres du groupe encore éligibles (non déjà présents dans `lignes`). Body/query: exclure[]. */
    public function membresEligibles(Request $request, Group $groupe)
    {
        $this->authorize('access', $groupe);

        $exclure = (array) $request->input('exclure', []);

        $tiers = Customer::join('cf_membres', 'tiers.id_tiers', '=', 'cf_membres.tiers_id')
            ->where('groupe_id', $groupe->id_groupe)
            ->where('status', 'A')
            ->whereNotIn('id_tiers', $exclure)
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->input('q');
                $query->where(function ($sub) use ($q) {
                    $sub->where('nom_tiers', 'like', "%$q%")->orWhere('prenom_tiers', 'like', "%$q%");
                });
            })
            ->orderBy('nom_tiers')
            ->get();

        return $this->data($tiers);
    }

    /**
     * Crée un lot de demandes pour un groupe (remplace panier + store).
     * Body: { lignes: [ { folio, montant, objet } ] }
     */
    public function store(Request $request, Group $groupe)
    {
        $this->authorize('access', $groupe);

        $payload = $request->validate([
            'lignes'            => ['required', 'array', 'min:1'],
            'lignes.*.folio'    => ['required'],
            'lignes.*.montant'  => ['required', 'numeric', 'gt:0'],
            'lignes.*.objet'    => ['required', 'string'],
        ]);

        $rows = [];
        foreach ($payload['lignes'] as $ligne) {
            $rows[] = [
                'groupe_id'   => $groupe->id_groupe,
                'tiers_id'    => $ligne['folio'],
                'mtt_capital' => $ligne['montant'],
                'objet_pret'  => $ligne['objet'],
            ];
        }

        // NB : la création du dossier + demandes définitives se fait via DossierController::store,
        // qui reçoit ce même tableau `lignes`. Ici on ne renvoie que le récapitulatif validé.
        return $this->data(['lignes' => $rows, 'total' => array_sum(array_column($rows, 'mtt_capital'))], 201);
    }

    /** Demandes d'un dossier. */
    public function showByDossier(Cycle $dossier)
    {
        $this->authorize('access', $dossier);

        $demandes = GroupLoanApplication::join('cd_demandes', 'cf_demande_pret_groupes.ref_dde', 'cd_demandes.id_demande')
            ->join('tiers', 'cd_demandes.tiers_id', '=', 'tiers.id_tiers')
            ->where('dossier_id', $dossier->id_dossier)
            ->get();

        return $this->data([
            'dossier'     => $dossier,
            'demandes'    => $demandes,
            'nb_demande'  => $demandes->count(),
            'sum_demande' => $demandes->sum('mtt_capital'),
        ]);
    }

    /** Modifie une demande (montant / objet). */
    public function update(Request $request, string $demande)
    {
        $data = $request->validate([
            'montant' => ['required', 'numeric', 'gt:0'],
            'objet'   => ['required', 'string'],
        ]);

        $ligneGroupe = GroupLoanApplication::where('ref_dde', $demande)->firstOrFail();
        $dossier = Cycle::findOrFail($ligneGroupe->dossier_id);
        $this->authorize('access', $dossier);

        ApplicationLoan::where('id_demande', $demande)->update([
            'mtt_capital'    => $data['montant'],
            'mtt_recommande' => $data['montant'],
            'objet_pret'     => $data['objet'],
        ]);

        return $this->message('Demande mise à jour.');
    }
}
