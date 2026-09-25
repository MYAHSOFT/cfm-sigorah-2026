@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10 mb-10">
        <div class="flex justify-between items-center w-full mb-10">
            <x-card-head>
                Octroi de prêt
                <x-slot name="action">
                    <a href="{{route('gp.doc.index')}}" class="mr-3 text-orange-400 hover:text-orange-500">
                        <i class="fad fa-folder fa-xl"></i>
                    </a>
                    <a href="{{route('gp.octroi.forget')}}" class="mr-3 text-gray-700 hover:text-orange-500">
                        <i class="fal fa-redo fa-xl"></i>
                    </a>
                </x-slot>
            </x-card-head>
        </div>

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
                @php
                    $mtt_octroi = key_exists($demande->ref_dde, $carts) ? $carts[$demande->ref_dde] : $demande->mtt_recommande;
                @endphp
                <div class="flex items-center border-b hover:bg-gray-100">
                    <a href="{{route('gp.octroi.cart1', $demande->id_tiers)}}"
                        class="flex w-full items-center px-3 space-x-2 text-gray-700 hover:text-orange-700 mr-3">
                        <img src="{{asset('img/user.png')}}" alt="" class="h-10 w-10 rounded-full">
                        <div class="flex justify-between  items-center w-full">
                            <div class="flex flex-col flex-1">
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
                                <span class="font-normal text-gray-500 lowercase">
                                    {{ucfirst(strtolower($demande->objet_pret))}}
                                </span>
                            </div>
                            <div class="w-60 text-right">
                                @if($mtt_octroi != $demande->mtt_recommande)
                                    <span class="line-through decoration-red-500 italic text-red-500 mr-5">
                                        {{number_format($demande->mtt_recommande, 2,',',' ')}}
                                    </span>
                                @endif
                                <span class="@if(key_exists($demande->ref_dde, $carts)) font-semibold text-green-500 @endif" >
                                    {{number_format($mtt_octroi, 2,',',' ')}}
                                </span>
                            </div>
                        </div>
                    </a>
                    @if(key_exists($demande->ref_dde, $carts))
                        <a href="{{route('gp.octroi.destroy', $demande->ref_dde)}}" class="text-orange-500">
                            <i class="fal fa-redo fa-lg"></i>
                        </a>
                    @else
                        <a href="{{route('gp.octroi.cart1', $demande->id_tiers)}}" class="text-green-700">
                            <i class="fal fa-plus fa-lg"></i>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="flex justify-center space-x-5">
            @if(count($carts)>0)
            <a href="{{route('gp.octroi.create', $dossier->id_dossier)}}" class="btn btn-warning">
                <i class="fal fa-angle-double-right mr-2"></i>
                Continuer
            </a>
            @endif
            @if($nb_demande != count($carts))
            <a href="{{route('gp.octroi.storeAll', $dossier->id_dossier)}}" class="btn btn-warning-alt">
                <i class="fal fa-check mr-2"></i>
                Valider la liste
            </a>
            @endif
        </div>
    </div>
@endsection
