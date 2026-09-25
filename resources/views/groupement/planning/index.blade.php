@extends('layouts.app')


@section('content')
    <div class="flex flex-col w-full px-5">
        <x-card-head>
           Planning mensuel @if(key_exists($month, $months)) : <span class="text-orange-500 italic">{{$months[$month]}} {{$year}}</span> @endif
            <x-slot name="action">
                <a href="{{route('gp.planning.home')}}" class="text-orange-500 hover:text-orange-700">
                    <i class="fad fa-folder fa-lg"></i>
                </a>
            </x-slot>
        </x-card-head>

        @if($calendriers->count()>0)
        @foreach ($days as $key=>$day)
            <a href="" class="flex flex-col w-full mb-5">
                <div class="flex w-full font-bold text-gray-700 border-b-2 border-gray-500">
                    {{$day}}
                </div>
                <div class="flex bg-gray-500 text-gray-300 py-1">
                    <div class="w-1/3 pl-5">
                        Date
                    </div>
                    <div class="flex flex-1 justify-center">
                        @foreach ($dates[$key] as $d=>$date)

                            <div class="flex w-1/5 justify-center">
                                {{(new DateTime($date))->format('d/m')}}
                            </div>

                        @endforeach

                        @if(count($dates[$key]) == 4)
                            <div class="flex w-1/5 justify-center"></div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col bg-orange-100">

                    @foreach ($calendriers as $calendrier)

                        @if($calendrier->jour_reunion == $key)
                            <div class="flex border-b border-white">
                                <div class="w-1/3 pl-5 py-1">
                                    {{$calendrier->nom_tiers}} ({{$calendrier->num_caisse}})
                                </div>
                                <div class="flex flex-1 justify-center">

                                    @php

                                        $echeancier = $echeanciers[$calendrier->id_dossier]

                                    @endphp

                                    @foreach ($echeancier as $ech)
                                            @php $bg = !empty($ech) ? 'bg-green-500' : 'bg-gray-300'; @endphp
                                        <div class="flex w-1/5 justify-center  {{$bg}} border-white border-r">

                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        @endif

                    @endforeach
                </div>
            </a>
        @endforeach
        @else
            <div class="text-gray-600">Oops! Aucun planning</div>
        @endif
    </div>
@endsection
