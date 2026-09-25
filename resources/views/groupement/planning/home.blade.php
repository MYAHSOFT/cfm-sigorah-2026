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
            @foreach ($months as $key=>$month)
                <div class="flex items-center border-b hover:bg-gray-100">
                    <a href="{{route('gp.planning.index', $key)}}"
                        class="flex w-full items-center px-3 py-2 space-x-2 text-gray-700 hover:text-orange-700 mr-3">
                        <span class="text-orange-300 mr-2">
                            <i class="fad fa-folder fa-xl"></i>
                        </span>
                        <div class="flex justify-between  items-center w-full">
                            <div class="flex flex-col flex-1">
                                <div>
                                    <span class="font-bold text-gray-700">
                                        {{$month}}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

    </div>
@endsection
