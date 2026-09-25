@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">
        <x-card-head>
           Calendrier de prêt
            <x-slot name="action">
                {{-- <a href="{{route('gp.contrat.index')}}" class="mr-4 hover:text-orange-500">
                    <i class="fad fa-folder fa-xl"></i>
                </a>
                <a href="{{route('gp.demande.membre')}}" class="mr-4 hover:text-orange-500">
                    <i class="fal fa-plus fa-xl"></i>
                </a> --}}
            </x-slot>
        </x-card-head>

        <div class="flex justify-between px-5 border-b-2 border-gray-400 text-cyan-700 mb-5 font-bold py-2">
            <div>
                Nombre : {{$nb_encours}}
            </div>
            <div>
                Montant total : {{number_format($sum_encours, 2,',',' ')}}
            </div>
        </div>

        <div class="flex flex-col mb-10">
            @foreach ($encours as $enc)
                @php
                    // $mtt_octroi = key_exists($demande->ref_dde, $carts) ? $carts[$demande->ref_dde] : $demande->mtt_recommande;
                @endphp
                <div class="flex items-center border-b hover:bg-gray-100">
                    <a href="{{route('gp.calendrier.show', $enc->id_dossier)}}"
                        class="flex w-full items-center px-3 py-2 space-x-2 text-gray-700 hover:text-orange-700 mr-3">
                        <span class="text-orange-300 mr-2">
                            <i class="fad fa-folder fa-xl"></i>
                        </span>
                        <div class="flex justify-between  items-center w-full">
                            <div class="flex flex-col flex-1">
                                <div>
                                    <span class="font-bold text-gray-700">
                                        {{$enc->nom_tiers}}
                                    </span>
                                    <span class="font-normal italic text-cyan-700">
                                        ({{$enc->num_caisse}})
                                    </span>
                                </div>
                                <div>
                                    {{(new DateTime($enc->debut_cycle))->format('d/m/Y')}}
                                </div>
                            </div>
                            <div class="w-60 text-right">
                                {{number_format($enc->mtt_octroi, 2,',',' ')}}
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

    </div>
@endsection
