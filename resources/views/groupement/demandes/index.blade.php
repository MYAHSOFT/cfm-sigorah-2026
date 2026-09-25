@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">
        <x-card-head>
           Demande de prêt
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
                Nombre : {{$nb_demande}}
            </div>
            <div>
                Montant total : {{number_format($sum_demande, 2,',',' ')}}
            </div>
        </div>

        <div class="flex flex-col mb-10">
            @foreach ($demandes as $demande)
                @php
                    // $mtt_octroi = key_exists($demande->ref_dde, $carts) ? $carts[$demande->ref_dde] : $demande->mtt_recommande;
                @endphp
                <div class="flex items-center border-b hover:bg-gray-100">
                    <a href="{{route('gp.demande.groupe', $demande->id_dossier)}}"
                        class="flex w-full items-center px-3 py-2 space-x-2 text-gray-700 hover:text-orange-700 mr-3">
                        <span class="text-orange-300 mr-2">
                            <i class="fad fa-folder fa-xl"></i>
                        </span>
                        <div class="flex justify-between  items-center w-full">
                            <div class="flex flex-col flex-1">
                                <div>
                                    <span class="font-bold text-gray-700">
                                        {{$demande->nom_tiers}}
                                    </span>
                                    <span class="font-normal italic text-cyan-700">
                                        ({{$demande->num_caisse}})
                                    </span>
                                </div>
                                <div>
                                    {{(new DateTime($demande->debut_cycle))->format('d/m/Y')}}
                                </div>
                            </div>
                            <div class="w-60 text-right">
                                @if($demande->statut == 'P')
                                    <span class="text-green-500">
                                        <i class="fal fa-check"></i>
                                    </span>
                                @endif
                               {{number_format($demande->mtt_capital, 2,',',' ')}}
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

    </div>
@endsection
