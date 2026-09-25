@extends('layouts.app')


@section('content')
    <div class="flex flex-col w-full px-5">
        <x-card-head>
           Fiche membre
            <x-slot name="action">
                @if($dossier->statut !='C')
                <a href="{{route('gp.contrat.show', $sum_operation->dossier_id)}}" class=" hover:text-orange-500">
                    <i class="fal fa-list fa-lg"></i>
                </a>
                @endif
                <a href="{{route('gp.fiche.show', [
                    'id_tiers'    =>$sum_operation->tiers_id,
                    'id_dossier'    =>$sum_operation->dossier_id,
                    'tri'   =>'az'
                ])}}" class=" hover:text-orange-500">
                    <i class="fal fa-sort-numeric-up fa-lg"></i>
                </a>
                <a href="{{route('gp.fiche.show', [
                    'id_tiers'    =>$sum_operation->tiers_id,
                    'id_dossier'    =>$sum_operation->dossier_id,
                    'tri'   =>'za'
                ])}}" class=" hover:text-orange-500">
                    <i class="fal fa-sort-numeric-up-alt fa-lg"></i>
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

        @foreach ($operations as $operation)
            <a href="" class="flex flex-col w-full text-gray-700 border border-orange-200 rounded-md mb-5">
                <div class="flex justify-between text-orange-700 bg-orange-200 text-center py-2 px-5">
                    <div>
                        {{(new DateTime($operation->date_oper))->format('d/m/Y')}}
                    </div>
                    <div class="text-right w-1/3">
                        @php $mtt_total = $operation->mtt_remb + $operation->mtt_depot - $operation->mtt_retrait + $operation->penalite @endphp
                        {{number_format($mtt_total, 2,',',' ')}}
                    </div>
                </div>
                <div class="flex flex-col px-5 py-5 space-y-3">
                    <div class="flex border-b-2">
                        <div class="flex flex-1 truncate">
                            Remboursement
                        </div>
                        <div class="text-right w-1/3">
                            {{number_format($operation->mtt_remb, 2,',',' ')}}
                        </div>
                    </div>
                    <div class="flex border-b-2">
                        <div class="flex flex-1 truncate">
                            Dépôt
                        </div>
                        <div class="text-right w-1/3">
                            {{number_format($operation->mtt_depot, 2,',',' ')}}
                        </div>
                    </div>
                    <div class="flex border-b-2">
                        <div class="flex flex-1 truncate">
                            Retrait
                        </div>
                        <div>
                            {{number_format($operation->mtt_retrait, 2,',',' ')}}
                        </div>
                    </div>
                    <div class="flex border-b-2">
                        <div class="flex flex-1 truncate">
                            Amende
                        </div>
                        <div>
                            {{number_format($operation->penalite, 2,',',' ')}}
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endsection
