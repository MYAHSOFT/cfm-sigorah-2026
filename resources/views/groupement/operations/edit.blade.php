@extends('layouts.app')

@section('content')

<div class="flex flex-col w-full space-y-4 px-10">
    <x-card-head>
       Versement de membre
        <x-slot name="action">
            <a href="{{route('gp.operation.showDetail', [
                'id_dossier'    =>$operation->dossier_id,
                'ref_operation' =>$operation->ref_operation
            ])}}" class="text-orange-500 hover:text-orange-700">
                <i class="fad fa-folder fa-lg"></i>
            </a>
            <a href="{{route('gp.operation.showDetail', [
                'id_dossier'    =>$operation->dossier_id,
                'ref_operation' =>$operation->ref_operation
            ])}}" class="hover:text-orange-500">
                <i class="fal fa-book-open fa-lg"></i>
            </a>
        </x-slot>
    </x-card-head>

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

        <x-show>
            <x-slot name='label'>Remboursement</x-slot>
            {{!empty($operation->mtt_remb)?number_format($operation->mtt_remb,2,',',' '):'0,00'}}
        </x-show>
        <x-show>
            <x-slot name='label'>Dépôt</x-slot>
            {{!empty($operation->mtt_depot)?number_format($operation->mtt_depot,2,',',' '):'0,00'}}
        </x-show>
        <x-show>
            <x-slot name='label'>Retrait</x-slot>
            {{!empty($operation->mtt_retrait)?number_format($operation->mtt_retrait,2,',',' '):'0,00'}}
        </x-show>
        <x-show>
            <x-slot name='label'>Pénalité</x-slot>
            {{!empty($operation->penalite)?number_format($operation->penalite,2,',',' '):'0,00'}}
        </x-show>

        <div class="flex justify-center space-x-3 py-5">
            <a href="{{route('gp.operation-edit.edit', $operation->id_oper)}}" class="msg-box-show btn btn-warning">
                <i class="fal fa-edit mr-1"></i>
                Modifier
            </a>
        </div>

    </div>
</div>

@endsection


