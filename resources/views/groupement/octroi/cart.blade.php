@extends('layouts.app')

@section('content')

<div class="flex flex-col w-full space-y-4 px-10">
    <div class="flex justify-between items-center w-full mb-10">
        <div class="w-1/2">

        </div>
        <div class="w-1/2 text-right">
        </div>
    </div>

    <div class="flex flex-col justify-center items-center w-full">
        <img src="{{asset('img/user.png')}}" alt="photo" class="w-60 border rounded-md shadow-md">
        <div class="flex flex-col justify-center w-full mt-5">
            <div class="font-bold text-center text-gray-700">
                {{$demande->nom_tiers}}
            </div>
            <div class="font-normal text-center text-gray-500">
                {{$demande->prenom_tiers}}
            </div>
            <div class="font-normal text-center text-gray-500">
                {{$demande->cin}}
            </div>
        </div>
    </div>
    <div class="flex justify-between border-t border-b text-gray-700">
        <div>
            Mtt demandé : {{number_format($demande->mtt_capital, 2,',',' ')}}
        </div>
        <div>
            Mtt recommandé : {{number_format($demande->mtt_recommande, 2,',',' ')}}
        </div>
    </div>
    <div class="flex flex-col">
        <form id="frmDemande" method="post" action="{{route('gp.octroi.cart2')}}">
            @csrf
            <input type="hidden" name="groupeId" value="{{$demande->groupe_id}}">
            <input type="hidden" name="dossierId" value="{{$demande->dossier_id}}">
            <input type="hidden" name="folio" value="{{$demande->id_tiers}}">
            <input type="hidden" name="refDde" value="{{$demande->ref_dde}}">
            <div class="mb-5">
                <x-label>Montant à octroyer</x-label>
                <input type="text" name="montant" value="{{!empty(old('montant'))?old('montant'):$demande->mtt_recommande}}" class="form-control">
                @error('montant')
                    <span class="text-red-500">{{$message}}</span>
                @enderror
            </div>
            <div class="flex justify-center space-x-3 py-5">
                <button type="submit" class="msg-box-show btn btn-warning">
                    <i class="fal fa-save mr-1"></i>
                    Enregistrer
                </button>

                <a href="{{route('gp.octroi.show', $demande->dossier_id)}}" class="inline-block btn btn-dark">
                    <i class="fal fa-times mr-1"></i>
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@endsection


