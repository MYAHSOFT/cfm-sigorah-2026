@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10 mb-10">
        <div class="flex justify-between items-center w-full mb-10">
            <div class="w-1/2">

            </div>
            <div class="w-1/2 text-right">
                <a href="{{route('gp.operation.edit', [
                    'id_dossier'    =>$sum_operation->dossier_id,
                    'ref_operaton'  =>$ref_operation
                ])}}" class="mr-3 text-gray-700 hover:text-orange-500">
                    <i class="fad fa-edit fa-lg"></i>
                </a>
                <a href="{{route('gp.operation.show', $sum_operation->dossier_id)}}" class="mr-3 text-orange-400 hover:text-orange-500">
                    <i class="fad fa-folder fa-lg"></i>
                </a>
                <a href="{{route('gp.demande.membre')}}" class="mr-3 text-gray-700 hover:text-orange-500">
                    <i class="fal fa-plus fa-lg"></i>
                </a>
            </div>
        </div>

        <div class="flex flex-col w-full mb-10 space-y-5 bg-gray-300 bg-opacity-40 py-4 px-3 rounded-lg">
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-calendar-alt"></i>
                        Date
                    </div>
                    <div class="text-center font-semibold">
                        : {{(new DateTime($sum_operation->date_oper))->format('d/m/Y')}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Montant total
                    </div>
                    <div class="text-center font-semibold">
                        @php $mtt_total = $sum_operation->mtt_remb + $sum_operation->mtt_depot - $sum_operation->mtt_retrait + $sum_operation->penalite @endphp
                        :  {{number_format($mtt_total, 2, ',', ' ')}}
                    </div>
                </div>
            </div>
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Remboursement
                    </div>
                    <div class="text-center font-semibold">
                        : {{number_format($sum_operation->mtt_remb, 2, ',', ' ')}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Dépôt
                    </div>
                    <div class="text-center font-semibold">
                        :  {{number_format($sum_operation->mtt_depot, 2, ',', ' ')}}
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
                        :  {{number_format($sum_operation->mtt_retrait, 2, ',', ' ')}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Penalite
                    </div>
                    <div class="text-center font-semibold">
                        :  {{number_format($sum_operation->penalite, 2, ',', ' ')}}
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col mb-10">
            @foreach ($operations as $operation)
                @php

                    $remb = (double)$operation->mtt_remb;
                    $depot = (double)$operation->mtt_depot;
                    $retrait = (double)$operation->mtt_retrait;
                    $penalite = (double)$operation->penalite;

                    $versement = $remb + $depot + $penalite;
                    $retrait = !empty($operation->retrait) ? (double)$operation->retrait:0;
                @endphp
                <div class="flex">
                    <a href="{{route('gp.operation-edit.show', $operation->id_oper)}}"
                        class="flex w-full items-center px-3 py-3 border-b text-gray-700 hover:text-orange-700 hover:bg-gray-100">
                        <img src="{{asset('img/user.png')}}" alt="" class="h-10 w-10 rounded-full mr-3">
                        <div class="flex justify-between  items-center w-full">
                            <div class="flex flex-col flex-1">
                                <div>
                                    <span class="font-bold @if($operation->profil == '1') text-orange-500 @else text-gray-700 @endif mr-2">
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

    </div>
@endsection
