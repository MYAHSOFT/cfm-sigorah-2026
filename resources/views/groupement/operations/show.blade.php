@extends('layouts.app')


@section('content')
    <div class="flex flex-col w-full px-5">
        <x-card-head>
           Historique de versement
            <x-slot name="action">
                @if($dossier->statut !='C')
                <a href="{{route('gp.versement.ech', [
                    'id_dossier'    =>$dossier->id_dossier
                ])}}" class=" hover:text-orange-500">
                    <i class="fal fa-plus fa-lg"></i>
                </a>
                @endif
                <a href="{{route('gp.operation.show', [
                    'id_dossier'    =>$dossier->id_dossier,
                    'tri'   =>'asc'
                ])}}" class=" hover:text-orange-500">
                    <i class="fal fa-sort-numeric-up fa-lg"></i>
                </a>
                <a href="{{route('gp.operation.show', [
                    'id_dossier'    =>$dossier->id_dossier,
                    'tri'   =>'desc'
                ])}}" class=" hover:text-orange-500">
                    <i class="fal fa-sort-numeric-up-alt fa-lg"></i>
                </a>
            </x-slot>
        </x-card-head>
        @foreach ($operations as $operation)
            <a href="{{route('gp.operation.showDetail', [
                'id_dossier'    =>$operation->dossier_id,
                'ref_operation'    =>$operation->ref_operation,
                ])}}" class="flex flex-col w-full text-gray-700 border border-orange-200 rounded-md mb-5">
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
