@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">
        <x-card-head>
            Prêt actif
            <x-slot name="action">
                {{-- <a href="{{route('gp.mbre.forget')}}" class="mr-5 text-gray-500 hover:text-orange-500">
                    <i class="fal fa-list fa-xl"></i>
                </a>
                <a href="{{route('gp.demande.membre')}}" class="mr-5 text-gray-500 hover:text-orange-500">
                    <i class="fal fa-plus fa-xl"></i>
                </a> --}}
            </x-slot>
        </x-card-head>
        <div class="flex flex-wrap">
            @if($contrats->count()>0)
                @foreach ($contrats as $contrat)
                    <div class="mr-5 mb-5">
                        <a href="{{route('gp.versement.ech', $contrat->id_dossier)}}"
                            class="flex flex-col justify-center items-center bg-orange-200 bg-opacity-40 shadow-sm hover:bg-orange-200 hover:bg-opacity-60 border px-5 py-5 rounded-md">
                            <div class="mb-2 text-orange-400">
                                <i class="fad fa-folder fa-2xl"></i>
                            </div>
                            <div class="text-gray-500">
                                {{$contrat->id_dossier}}
                            </div>
                            <div class="text-gray-500">
                                {{(new DateTime($contrat->date_oper))->format('d/m/Y')}}
                            </div>
                            <div class="font-normal text-orange-700">
                                {{number_format($contrat->mtt_octroi,2,',',' ')}}
                            </div>
                        </a>
                    </div>
                @endforeach
            @else
            <div class="w-44">
                <a href="#"
                    class="flex flex-col justify-center items-center bg-gray-200 bg-opacity-40 shadow-sm hover:bg-gray-200 hover:bg-opacity-60 border px-3 py-3 rounded-md">
                    <div class="mb-2 text-gray-400">
                        <i class="fad fa-folder fa-2xl"></i>
                    </div>
                    <div class="font-normal text-gray-500">
                        Aucun document
                    </div>
                </a>
            </div>

            @endif
        </div>

    </div>
@endsection
