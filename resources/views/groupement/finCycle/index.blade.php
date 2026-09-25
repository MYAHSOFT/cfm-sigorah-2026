@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">
        <x-card-head>
           Encours de prêt
            <x-slot name="action">
                {{-- <a href="{{route('gp.contrat.index')}}" class="mr-4 hover:text-orange-500">
                    <i class="fad fa-folder fa-xl"></i>
                </a>
                <a href="{{route('gp.demande.membre')}}" class="mr-4 hover:text-orange-500">
                    <i class="fal fa-plus fa-xl"></i>
                </a> --}}
            </x-slot>
        </x-card-head>

        <div class="flex flex-col mb-10">
            @foreach ($encours as $enc)
                @php
                    // $mtt_octroi = key_exists($demande->ref_dde, $carts) ? $carts[$demande->ref_dde] : $demande->mtt_recommande;
                @endphp
                <div class="flex items-center border-b hover:bg-gray-100">
                    <a href="{{route('gp.finc.show', $enc->id_dossier)}}"
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

            @if($encours->count() == 0)
            <div class="w-44">
                <a href="#"
                    class="flex flex-col justify-center items-center bg-gray-200 bg-opacity-40 shadow-sm hover:bg-gray-200 hover:bg-opacity-60 border px-3 py-3 rounded-md">
                    <div class="mb-2 text-gray-400">
                        <i class="fad fa-folder fa-2xl"></i>
                    </div>
                    <div class="font-normal text-gray-500">
                        Encours vide
                    </div>
                </a>
            </div>
            @endif

        </div>

    </div>
@endsection
