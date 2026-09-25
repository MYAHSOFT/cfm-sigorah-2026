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
                <a href="{{route('gp.calendrier.show', $dossier->id_dossier)}}" class="text-cyan-500 hover:text-orange-500">
                    <i class="fad fa-calendar-alt fa-xl"></i>
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
                        Nb contrat
                    </div>
                    <div class="text-center font-semibold">
                        : {{$nb_demande}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-sigma"></i>
                        Total capital
                    </div>
                    <div class="text-center font-semibold">
                        :  {{number_format($sum_demande, 2, ',', ' ')}}
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
                        Date octroi
                    </div>
                    <div class="text-center font-semibold">
                        :  @if(!empty($dossier->date_octroi_effectif)) 
                            {{(new DateTime($dossier->date_octroi_effectif))->format('d/m/Y')}}
                        @else
                            {{(new DateTime($dossier->date_prev_octroi))->format('d/m/Y')}}
                        @endif 
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
            @foreach ($contrats as $contrat)
                <div class="flex">
                    <a href="{{route('gp.fiche.show', [
                        'id_tiers'  =>$contrat->id_tiers,
                        'id_dossier'    =>$contrat->dossier_id,
                    ])}}"
                        class="flex w-full items-center px-3 py-3 border-b  space-x-2 text-gray-700 hover:text-orange-700 hover:bg-gray-100">
                        <img src="{{asset('img/user.png')}}" alt="" class="h-10 w-10 rounded-full">
                        <div class="flex justify-between  items-center w-full">
                            <div class="flex flex-col">
                                <div>
                                    <span class="font-bold text-gray-700">
                                        {{$contrat->nom_tiers}}
                                    </span>
                                    <span class="font-normal text-gray-500">
                                        {{$contrat->prenom_tiers}}
                                    </span>
                                    <span class="font-normal italic text-cyan-700">
                                        ({{$contrat->cin}})
                                    </span>
                                </div>
                                <span class="font-normal text-gray-500">
                                    {{ucfirst(strtolower($contrat->objet_pret))}}
                                </span>
                            </div>
                            <div class="w-48 text-right">
                                {{number_format($contrat->mtt_capital, 2,',',' ')}}
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

    </div>
@endsection
