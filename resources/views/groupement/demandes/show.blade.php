@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10 mb-10">
        <x-card-head>
           Demande de prêts
            <x-slot name="action">
                <a href="{{route('gp.demande.index')}}" class="mr-4 hover:text-orange-500">
                    <i class="fad fa-list fa-xl"></i>
                </a>
                <a href="{{route('gp.doc.index')}}" class="mr-4 text-orange-400 hover:text-orange-500">
                    <i class="fad fa-folder fa-xl"></i>
                </a>
                <a href="{{route('gp.demande.membre')}}" class="mr-4 hover:text-orange-500">
                    <i class="fal fa-plus fa-xl"></i>
                </a>
            </x-slot>
        </x-card-head>

        <div class="flex flex-col w-full mb-10 space-y-5 bg-gray-300 bg-opacity-40 py-4 px-3 rounded-lg">
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-stopwatch-20"></i>
                        Nb demande
                    </div>
                    <div class="text-center font-semibold">
                        : {{$nb_demande}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-sigma"></i>
                        Total demande
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
            @foreach ($demandes as $demande)
                <div class="flex">
                    <a href="{{route('gp.demande.edit', $demande->ref_dde)}}"
                        class="flex w-full items-center px-3 py-3 border-b  space-x-2 text-gray-700 hover:text-orange-700 hover:bg-gray-100">
                        <img src="{{asset('img/user.png')}}" alt="" class="h-10 w-10 rounded-full">
                        <div class="flex justify-between  items-center w-full">
                            <div class="flex flex-col">
                                <div>
                                    <span class="font-bold text-gray-700">
                                        {{$demande->nom_tiers}}
                                    </span>
                                    <span class="font-normal text-gray-500">
                                        {{$demande->prenom_tiers}}
                                    </span>
                                    <span class="font-normal italic text-cyan-700">
                                        ({{$demande->cin}})
                                    </span>
                                </div>
                                <span class="font-normal text-gray-500">
                                    {{ucfirst(strtolower($demande->objet_pret))}}
                                </span>
                            </div>
                            <div class="w-48 text-right">
                                {{number_format($demande->mtt_capital, 2,',',' ')}}
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if($dossier->statut == 'O')
            <div class="flex justify-center space-x-5">
                <a href="{{route('gp.doc.edit', $dossier->id_dossier)}}" class="btn btn-warning">
                    <i class="fal fa-edit mr-2"></i>
                    Modifier
                </a>
            </div>
        @endif

        @if($dossier->statut == 'P')
            <div class="flex justify-center space-x-5">
                <a href="{{route('gp.octroi.show', $dossier->id_dossier)}}" class="btn btn-warning">
                    <i class="fal fa-cart-plus mr-2"></i>
                    Octroyer
                </a>
            </div>
        @endif

        @if($nb_operation > 0)
            <div class="">
                <a href="{{route('gp.operation.show', $dossier->id_dossier)}}" class="btn btn-success">
                    <i class="fal fa-chevron-double-right mr-2"></i>
                    Consulter les remboursement
                </a>
            </div>
        @endif



    </div>
@endsection
