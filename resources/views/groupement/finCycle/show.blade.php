@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10 mb-10">

        <x-card-head>
            Remboursement de prêt
            <x-slot name="action">
                <a href="{{route('gp.finc.index', $dossier->id_dossier)}}" class="mr-3 text-orange-400 hover:text-orange-500">
                    <i class="fad fa-folder fa-lg"></i>
                </a>
            </x-slot>
        </x-card-head>

        <div class="flex flex-col w-full mb-10 space-y-5 bg-gray-300 bg-opacity-40 py-4 px-3 rounded-lg">
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-calendar-alt"></i>
                        Date octroi
                    </div>
                    <div class="text-center font-semibold">
                        : {{(new DateTime($dossier->debut_cycle))->format('d/m/Y')}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-calendar-alt"></i>
                        Echeance
                    </div>
                    <div class="text-center font-semibold">
                        : {{(new DateTime($dossier->date_prev_remb))->format('d/m/Y')}}
                    </div>
                </div>
            </div>
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Solde des opérations
                    </div>
                    <div class="text-center font-semibold">
                        :  {{number_format($solde_operation, 2, ',', ' ')}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Montant à rembourser
                    </div>
                    <div class="text-center font-semibold">
                        : {{number_format($mtt_rembourser, 2, ',', ' ')}}
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col mb-10">
            @foreach ($cd_operations as $operation)
                @php

                    $remb = (double)$operation->mtt_remb;
                    $depot = (double)$operation->mtt_depot;
                    $retrait = (double)$operation->mtt_retrait;
                    $penalite = (double)$operation->penalite;

                    $versement = $remb + $depot + $penalite;
                    $retrait = !empty($operation->retrait) ? (double)$operation->retrait:0;
                @endphp
                <div class="flex">
                    <a href="#"
                        class="flex w-full items-center px-3 py-3 border-b text-gray-700 hover:text-orange-700 hover:bg-gray-100">
                        <img src="{{asset('img/user.png')}}" alt="" class="h-10 w-10 rounded-full mr-3">
                        <div class="flex justify-between  items-center w-full">
                            <div class="flex flex-col flex-1">
                                <div>
                                    <span class="font-bold text-gray-700 mr-2">
                                        {{$operation->nom_tiers}}
                                    </span>
                                    <span class="font-normal text-gray-500 mr-2">
                                        {{$operation->prenom_tiers}}
                                    </span>
                                </div>
                                <div class="font-normal text-cyan-700">
                                    {{$operation->cin}}
                                </div>
                            </div>
                            <div class="flex flex-col w-60 text-right">
                                <span>
                                    @php
                                        $interet = key_exists($operation->ref_pret, $interets) ? $interets[$operation->ref_pret] : 0;
                                        $encaisse = key_exists($operation->id_tiers, $encaisses) ? $encaisses[$operation->id_tiers] : 0;
                                    @endphp
                                    {{number_format($encaisse, 2,',',' ')}}
                                </span>
                                <span class="text-red-500">
                                    {{number_format($operation->mtt_octroye + $interet, 2,',',' ')}}
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if($dossier->statut != 'C')
            <div class="flex justify-center items-center space-x-5 mt-10">
                <a href="{{route('gp.finc.create', $dossier->id_dossier)}}" class="btn btn-warning">
                    <i class="fal fa-save mr-2"></i>
                    Clôturer le cycle
                </a>
                <a href="{{route('gp.finc.index')}}" class="btn btn-dark">
                    <i class="fal fa-times mr-2"></i>
                    Annuler
                </a>
            </div>
        @endif
    </div>
@endsection
