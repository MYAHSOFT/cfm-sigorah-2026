@extends('layouts.app')

@section('content')

<div class="flex flex-col w-full space-y-4 px-10">
    <div class="flex justify-between items-center w-full mb-10">
        <x-card-head>
            Demande prêt
            <x-slot name="action">
                <a href="{{route('gp.demande.membre')}}" class="mr-5 text-gray-700 hover:text-orange-500">
                    <i class="fal fa-users fa-lg"></i>
                </a>
            </x-slot>
        </x-card-head>
    </div>

    <div class="flex flex-col justify-center items-center w-full">
        <img src="{{asset('img/user.png')}}" alt="photo" class="w-60 border rounded-md shadow-md">
        <div class="flex flex-col justify-center w-full mt-5">
            <div class="font-bold text-center text-gray-700">
                {{$membre->nom_tiers}}
            </div>
            <div class="font-normal text-center text-gray-500">
                {{$membre->prenom_tiers}}
            </div>
            <div class="font-normal text-center text-gray-500">
                {{$membre->cin}}
            </div>
        </div>
    </div>

    <div class="flex flex-col">
        <form id="frmDemande" method="post" action="{{$url}}">
            @csrf
            <input type="hidden" name="groupeId" value="{{$groupe->id_groupe}}">
            <input type="hidden" name="folio" value="{{$membre->id_tiers}}">
            <input type="hidden" name="dossierId" value="{{$id_dossier}}">
            <div class="mb-5">
                <x-label>Montant demandé</x-label>
                @php
                    $montant = !empty($demande->mtt_capital) ? $demande->mtt_capital : old('montant');
                    $objet = !empty($demande->objet_pret) ? $demande->objet_pret : old('objet');
                @endphp
                <input type="text" name="montant" value="{{$montant}}" class="form-control">
                @error('montant')
                    <span class="text-red-500">{{$message}}</span>
                @enderror
            </div>
            <div>
                <x-label>Objet de la demande</x-label>
                <input type="text" name="objet" value="{{$objet}}" class="form-control">
                @error('objet')
                    <span class="text-red-500">{{$message}}</span>
                @enderror
            </div>
            <div class="flex justify-center space-x-3 py-5">
                <button type="submit" class="msg-box-show btn btn-warning">
                    <i class="fal fa-save mr-1"></i>
                    Enregistrer
                </button>

                <a href="{{route('gp.demande.membre')}}" class="inline-block btn btn-dark">
                    <i class="fal fa-times mr-1"></i>
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@endsection


