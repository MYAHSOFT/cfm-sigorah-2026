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
    @if(!empty($contrat->ref_pret))
    <div class="flex justify-between border-t border-b text-gray-700">
        <div>
            Mtt à rembourser : {{number_format($contrat->mtt_capital, 2,',',' ')}}
        </div>
        <div>
            Mtt recommandé : {{number_format(0, 2,',',' ')}}
        </div>
    </div>
    @endif
    <div class="flex flex-col">
        <form id="frmDemande" method="post" action="{{$url}}">
            @csrf
            <input type="hidden" name="dossierId" value="{{$id_dossier}}">
            <input type="hidden" name="folio" value="{{$membre->id_tiers}}">

            <div class="mb-5">
                <x-label>Remboursement</x-label>
                <input type="text" name="remboursement" value="{{!empty($operation->mtt_remb)?$operation->mtt_remb:old('remboursement')}}" class="form-control" @if(empty($contrat->id_pret)) readonly @endif>
            </div>
            <div class="mb-5">
                <x-label>Dépôt</x-label>
                <input type="text" name="depot" value="{{!empty($operation->mtt_depot)?$operation->mtt_depot:old('depot')}}" class="form-control">
            </div>
            <div class="mb-5">
                <x-label>Retrait</x-label>
                <input type="text" name="retrait" value="{{!empty($operation->mtt_retrait)?$operation->mtt_retrait:old('retrait')}}" class="form-control">
            </div>
            <div class="mb-5">
                <x-label>Pénalité</x-label>
                <input type="text" name="penalite" value="{{!empty($operation->penalite)?$operation->penalite:old('penalite')}}" class="form-control">
            </div>
            <div class="flex justify-center space-x-3 py-5">
                <button type="submit" class="msg-box-show btn btn-warning">
                    <i class="fal fa-save mr-1"></i>
                    Enregistrer
                </button>

                <a href="{{route('gp.versement.show', $id_dossier)}}" class="inline-block btn btn-dark">
                    <i class="fal fa-times mr-1"></i>
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@endsection


