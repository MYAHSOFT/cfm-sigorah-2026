@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10 mb-10">
        <x-card-head>
           Versement des membres
            <x-slot name="action">
                <a href="{{route('gp.versement.index')}}" class="mr-3 text-orange-400 hover:text-orange-500">
                    <i class="fad fa-folder fa-lg"></i>
                </a>
                <a href="{{route('gp.versement.ech', $dossier->id_dossier)}}" class="mr-3 text-cyan-500 hover:text-cyan-700">
                    <i class="fad fa-calendar-alt fa-lg"></i>
                </a>
                <a href="{{route('gp.versement.destroy')}}" class="mr-3 text-gray-700 hover:text-orange-500">
                    <i class="fal fa-trash-alt fa-lg"></i>
                </a>
            </x-slot>
        </x-card-head>

        <div class="flex flex-col w-full mb-10 space-y-5 bg-gray-300 bg-opacity-40 py-4 px-3 rounded-lg">
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Remboursement
                    </div>
                    <div class="text-center font-semibold">
                        : {{number_format($sum->remboursement, 2, ',', ' ')}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Dépôt
                    </div>
                    <div class="text-center font-semibold">
                        :  {{number_format($sum->depot, 2, ',', ' ')}}
                    </div>
                </div>
            </div>
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Retrait
                    </div>
                    <div class="text-center font-semibold">
                        :  {{number_format($sum->retrait, 2, ',', ' ')}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Penalite
                    </div>
                    <div class="text-center font-semibold">
                        :  {{number_format($sum->penalite, 2, ',', ' ')}}
                    </div>
                </div>
            </div>
        </div>

        @php

            $i = 0;

        @endphp

        <div class="flex flex-col">
            @foreach ($membres as $membre)
                @php
                    $i++;
                    $operation = key_exists($membre->id_tiers, $versements) ? (object)$versements[$membre->id_tiers] : (object)[];
                    $versement = !empty($operation->versement) ? (double)$operation->versement:0;
                    $retrait = !empty($operation->retrait) ? (double)$operation->retrait:0;
                @endphp
                <div class="flex">
                    <a href="{{route('gp.versement.create', [
                        'id_tiers'  =>$membre->id_tiers,
                        'id_dossier'  =>$dossier->id_dossier,
                        'reunion'  =>$reunion,
                    ])}}"
                        class="flex w-full items-center px-3 py-3 border-b text-gray-700 hover:text-orange-700 hover:bg-gray-100">
                        <img src="{{asset('img/user.png')}}" alt="" class="h-10 w-10 rounded-full mr-3">
                        <div class="flex justify-between  items-center w-full">
                            <div class="flex flex-col flex-1">
                                <div>
                                    <span class="font-bold @if($membre->profil == '1') text-orange-500 @else text-gray-700 @endif mr-2">
                                        {{$membre->nom_tiers}}
                                    </span>
                                    <span class="font-normal text-gray-500 mr-2">
                                        {{$membre->prenom_tiers}}
                                    </span>
                                </div>
                                <div class="font-normal text-cyan-700">
                                    {{$membre->cin}}
                                </div>
                            </div>
                            <div>
                                @if(key_exists($membre->id_tiers, $echeanciers))
                                    {{App\Lib\Format::number($echeanciers[$membre->id_tiers])}}
                                @else
                                    -
                                @endif
                            </div>
                            <div class="flex flex-1 flex-col text-right">
                                <span>
                                    {{number_format($versement, 2,',',' ')}}
                                </span>
                                @if($retrait>0)
                                <span class="text-red-500">
                                    ({{number_format($retrait, 2,',',' ')}})
                                </span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if($i > 0)
            <div class="flex justify-center font-bold text-gray-500 mt-5">
                Nombre de membre : {{$i}}
            </div>
        @endif

        <div class="flex justify-center items-center space-x-5 mt-10">
            <a href="{{route('gp.operation.create', [
                'id_dossier'    =>$dossier->id_dossier,
                'reunion'   =>$reunion
            ])}}" class="btn btn-warning">
                <i class="fal fa-save mr-2"></i>
                Enregistrer
            </a>
            <a href="{{route('gp.versement.index')}}" class="btn btn-dark">
                <i class="fal fa-save mr-2"></i>
                Annuler
            </a>
        </div>

    </div>
@endsection
