@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10 mb-10">
        <x-card-head>
           Contrat de prêt
           @if($dossier->statut == 'C')
                <span class="italic text-orange-500">(Prêt échu)</span>
            @else
            <span class="italic text-orange-500">(Prêt actif)</span>
           @endif
            <x-slot name="action">
                <a href="{{route('gp.contrat.index')}}" class="text-orange-400 hover:text-orange-500">
                    <i class="fad fa-folder fa-xl"></i>
                </a>
                <a href="{{route('gp.encours.index')}}" class="text-orange-400 hover:text-orange-500">
                    <i class="fad fa-sack-dollar fa-xl"></i>
                </a>
                <a href="{{route('gp.demande.membre')}}" class=" hover:text-orange-500">
                    <i class="fal fa-plus fa-xl"></i>
                </a>
            </x-slot>
        </x-card-head>
        <div class="flex flex-col w-full mb-10 space-y-5 bg-gray-300 bg-opacity-40 py-4 px-3 rounded-lg">
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-stopwatch-20"></i>
                        Nb réunion
                    </div>
                    <div class="text-center font-semibold">
                        : {{$nb_echeance}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-sigma"></i>
                        Total capital
                    </div>
                    <div class="text-center font-semibold">
                        :  {{number_format($sum_echeance, 2, ',', ' ')}}
                    </div>
                </div>
            </div>
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-calendar-week"></i>
                        Mode réunion
                    </div>
                    <div class="text-center font-semibold">
                        @php
                            $mode_reunion = config('groupement')['mode_reunion'];
                        @endphp
                        : {{ key_exists($dossier->mode_reunion, $mode_reunion) ? $mode_reunion[$dossier->mode_reunion] : $dossier->mode_reunion}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-calendar-day"></i>
                        Jour réunion
                    </div>
                    <div class="text-center font-semibold">
                        @php $days = App\Lib\Combobox::days() @endphp
                        : {{ key_exists($dossier->jour_reunion, $days) ? $days[$dossier->jour_reunion] : $dossier->jour_reunion}}
                    </div>
                </div>
            </div>
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-calendar-day"></i>
                        Prévue pour octroi
                    </div>
                    <div class="text-center font-semibold">
                        :  {{(new DateTime($dossier->date_prev_octroi))->format('d/m/Y')}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-calendar-week"></i>
                        Fin période
                    </div>
                    <div class="text-center font-semibold">
                        :  {{(new DateTime($dossier->date_prev_remb))->format('d/m/Y')}}
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col mb-10">
            @foreach ($echeanciers as $echeancier)
                <div class="flex w-full">
                    <a href="{{route('gp.versement.show', [
                        'id_dossier'    =>$dossier->id_dossier,
                        'reunion'    =>$echeancier->date_oper
                    ])}}"
                        class="flex w-full items-center px-3 py-3 border-b  space-x-2 text-gray-700 hover:text-orange-700 hover:bg-gray-100">
                        <div class="flex justify-between  items-center w-full">
                            <div class="flex flex-col w-full">
                                <div class="flex justify-between w-full">
                                    <div class="font-bold text-gray-700">
                                        {{(new DateTime($echeancier->date_oper))->format('d/m/Y')}}
                                    </div>
                                    <div class="font-normal text-gray-500">
                                        {{App\Lib\Format::number($echeancier->montant)}}
                                    </div>
                                    <div class="font-normal italic text-cyan-700">
                                        {{App\Lib\Format::number($echeancier->mtt_verse)}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

    </div>
@endsection
