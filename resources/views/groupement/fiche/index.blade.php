@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">
        <x-card-head>
           Archive
            <x-slot name="action">
                {{-- <a href="{{route('gp.contrat.index')}}" class="mr-4 hover:text-orange-500">
                    <i class="fad fa-folder fa-xl"></i>
                </a>
                <a href="{{route('gp.demande.membre')}}" class="mr-4 hover:text-orange-500">
                    <i class="fal fa-plus fa-xl"></i>
                </a> --}}
            </x-slot>
        </x-card-head>
        <div class="mb-10">
            <form method="post" action="{{route('gp.contrat.index')}}">
                @csrf
                <div class="flex w-full">
                    <div class="flex flex-1">
                        <input type="text" name="archive" value="{{$archive}}" class="form-control" placeholder="Année, Ex : {{date('Y')}}">
                    </div>
                    <button type="submit" class="btn btn-warning ml-2">
                        <i class="fal fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="flex flex-wrap">
            @if($contrats->count()>0)
                @foreach ($contrats as $contrat)

                @php

                    $bg_color = 'bg-orange-200 hover:bg-orange-200';
                    $text_color = "text-orange-400";

                    if($contrat->statut == 'C'){
                        $bg_color = "bg-gray-200 hover:bg-orange-200";
                    }

                @endphp

                    <div class="w-36 mr-5 mb-5">
                        <a href="{{route('gp.contrat.show', $contrat->id_dossier)}}"
                            class="flex flex-col justify-center items-center  bg-opacity-40 shadow-sm hover:bg-opacity-60 border py-3 rounded-md {{$bg_color}}">
                            <div class="mb-2 {{$text_color}}">
                                <i class="fad fa-folder fa-2xl"></i>
                            </div>
                            <div class="text-gray-500">
                                {{(new DateTime($contrat->date_contrat))->format('d/m/Y')}}
                            </div>
                            <div class="font-normal text-orange-700">
                                {{number_format($contrat->mtt_capital,2,',',' ')}}
                            </div>
                        </a>
                    </div>
                @endforeach

                @if($nb_contrat_all>0)
                <div class="w-36 mr-5 mb-5">
                    <a href="{{route('gp.contrat.index', ['archive'=>'all'])}}"
                        class="flex flex-col justify-center items-center  bg-opacity-40 shadow-sm hover:bg-opacity-60 border py-3 rounded-md bg-gray-200 hover:bg-orange-200">
                        <div class="mb-2 text-amber-800">
                            <i class="fad fa-books fa-2xl"></i>
                        </div>
                        <div class="text-gray-500">
                            Dossier
                        </div>
                        <div class="text-gray-500">
                            Archive
                        </div>
                    </a>
                </div>
                @endif

            @else
            <div class="w-44">
                <a href="#"
                    class="flex flex-col justify-center items-center bg-gray-200 bg-opacity-40 shadow-sm hover:bg-gray-200 hover:bg-opacity-60 border px-3 py-3 rounded-md">
                    <div class="mb-2 text-gray-400">
                        <i class="fad fa-folder fa-2xl"></i>
                    </div>
                    <div class="font-normal text-gray-500">
                        Contrat vide
                    </div>
                </a>
            </div>

            @endif
        </div>

    </div>
@endsection
