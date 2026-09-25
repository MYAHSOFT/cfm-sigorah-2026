<?php

namespace App\Http\Controllers\Api;

use App\Models\Association\MemberRole;
use App\Models\Association\Group;
use App\Models\Association\Member;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Miroir API de Groupement\MembreController + MembreInController + MembreOutController.
 * Les listes filtrées par session('id_groupe') deviennent des routes /groupements/{groupe}/membres.
 */
class MembreController extends ApiController
{
    /** Membres actifs (ou par statut) d'un groupement. */
    public function index(Request $request, Group $groupe)
    {
        $this->authorize('access', $groupe);

        $statut = strtoupper($request->input('statut', 'A'));

        $membres = Customer::join('cf_membres', 'tiers.id_tiers', '=', 'cf_membres.tiers_id')
            ->where('groupe_id', $groupe->id_groupe)
            ->where('status', $statut)
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->input('q');
                $query->where(function ($sub) use ($q) {
                    $sub->where('nom_tiers', 'like', "%$q%")
                        ->orWhere('prenom_tiers', 'like', "%$q%")
                        ->orWhere('cin', $q);
                });
            })
            ->orderBy('nom_tiers')
            ->paginate($request->integer('per_page', 20));

        return $this->paginated($membres);
    }

    /** Fiche d'un membre. */
    public function show(Group $groupe, string $tiers)
    {
        $this->authorize('access', $groupe);

        $membre = Customer::join('cf_membres', 'tiers.id_tiers', '=', 'cf_membres.tiers_id')
            ->where('cf_membres.groupe_id', $groupe->id_groupe)
            ->where('tiers.id_tiers', $tiers)
            ->firstOrFail();

        return $this->data($membre);
    }

    /** Création d'un membre (Tiers PP + rattachement au groupe). */
    public function store(Request $request, Group $groupe)
    {
        $this->authorize('access', $groupe);

        $data = $request->validate([
            'nom_tiers'    => ['required', 'string', 'max:120'],
            'prenom_tiers' => ['nullable', 'string', 'max:120'],
            'genre'        => ['required', 'in:F,M'],
            'cin'          => ['nullable', 'string', 'max:40'],
            'date_naissance' => ['nullable', 'date'],
            'date_entree'  => ['required', 'date'],
            'adresse'      => ['nullable', 'string', 'max:255'],
            'telephone'    => ['nullable', 'string', 'max:40'],
        ]);

        $membre = DB::transaction(function () use ($data, $groupe) {
            $data['id_tiers']    = \App\Lib\Referentiel::tiers(config('groupement.caisseId'));
            $data['caisse_id']   = config('sigorah.caisseId');
            $data['type_tiers']  = 'PP';
            $data['type_pi']     = '1';
            $data['civilite']    = $data['genre'] === 'F' ? 'Mme' : 'Mr';
            $data['nationalite'] = 'MG';

            $tiers = Customer::create($data);

            Member::create([
                'tiers_id'    => $data['id_tiers'],
                'groupe_id'   => $groupe->id_groupe,
                'date_entree' => $data['date_entree'],
                'fonction_id' => 'MBR',
                'status'      => 'A',
            ]);

            return $tiers;
        });

        return $this->data($membre, 201);
    }

    /** Mise à jour d'un membre. */
    public function update(Request $request, Group $groupe, string $tiers)
    {
        $this->authorize('access', $groupe);

        $membre = Customer::where('id_tiers', $tiers)->firstOrFail();

        $data = $request->validate([
            'nom_tiers'    => ['sometimes', 'string', 'max:120'],
            'prenom_tiers' => ['nullable', 'string', 'max:120'],
            'cin'          => ['nullable', 'string', 'max:40'],
            'date_naissance' => ['nullable', 'date'],
            'adresse'      => ['nullable', 'string', 'max:255'],
            'telephone'    => ['nullable', 'string', 'max:40'],
        ]);

        unset($data['caisse_id']);
        $membre->update($data);

        return $this->data($membre->fresh());
    }

    /** Upload d'une image (photo | signature | cin). */
    public function upload(Request $request, Group $groupe, string $tiers)
    {
        $this->authorize('access', $groupe);

        $request->validate([
            'objet' => ['required', 'in:photo,signature,cin'],
            'file'  => ['required', 'file', 'image', 'mimes:jpg,jpeg,png'],
        ]);

        $membre = Customer::where('id_tiers', $tiers)->firstOrFail();

        $name = $tiers . '_' . $request->file('file')->hashName();
        $request->file('file')->storeAs('tiers/' . $request->input('objet'), $name, 'img');

        $column = ['photo' => 'photo', 'signature' => 'signature', 'cin' => 'copie_cin'][$request->input('objet')];
        $membre->update([$column => $name]);

        return $this->data(['column' => $column, 'value' => $name]);
    }

    /** Réintégration de membres bloqués (ex-MembreInController). Body: { folios: [...] }. */
    public function reintegrer(Request $request, Group $groupe)
    {
        $this->authorize('access', $groupe);

        $folios = $request->validate(['folios' => ['required', 'array', 'min:1']])['folios'];

        Member::where('groupe_id', $groupe->id_groupe)
            ->whereIn('tiers_id', $folios)
            ->update(['status' => 'A']);

        return $this->message('Membres réintégrés.');
    }

    /** Blocage / sortie de membres (ex-MembreOutController, remplace le panier session). Body: { folios: [...] }. */
    public function bloquer(Request $request, Group $groupe)
    {
        $this->authorize('access', $groupe);

        $folios = $request->validate(['folios' => ['required', 'array', 'min:1']])['folios'];

        Member::where('groupe_id', $groupe->id_groupe)
            ->whereIn('tiers_id', $folios)
            ->update(['status' => 'D', 'fonction_id' => 'MBR']);

        return $this->message('Membres bloqués.');
    }
}
