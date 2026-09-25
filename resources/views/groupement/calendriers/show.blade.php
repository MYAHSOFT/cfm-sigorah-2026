@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10 mb-10">
        <x-card-head>
           Calendriers
            <x-slot name="action">
                <a href="{{route('gp.contrat.index')}}" class="text-orange-400 hover:text-orange-500">
                    <i class="fad fa-folder fa-xl"></i>
                </a>
                <a href="{{route('gp.calendrier.edit', $groupe->id_dossier)}}" class="text-gray-400 hover:text-orange-500">
                    <i class="fal fa-pencil fa-xl"></i>
                </a>
                <a href="{{route('gp.finc.show', $groupe->id_dossier)}}" class="text-orange-400 hover:text-orange-500">
                    <i class="fad fa-sack-dollar fa-xl"></i>
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
                        : {{$octrois->nb_contrat}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-sigma"></i>
                        Total capital
                    </div>
                    <div class="text-center font-semibold">
                        :  {{number_format($octrois->debit, 2, ',', ' ')}}
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
                        : {{ key_exists($groupe->mode_reunion, $mode_reunion) ? $mode_reunion[$groupe->mode_reunion] : $groupe->mode_reunion}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-calendar-day"></i>
                        Jour réunion
                    </div>
                    <div class="text-center font-semibold">
                        @php $days = App\Lib\Combobox::days() @endphp
                        : {{ key_exists($groupe->jour_reunion, $days) ? $days[$groupe->jour_reunion] : $groupe->jour_reunion}}
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
                        :  {{(new DateTime($groupe->date_prev_octroi))->format('d/m/Y')}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-calendar-week"></i>
                        Fin période
                    </div>
                    <div class="text-center font-semibold">
                        :  {{(new DateTime($groupe->date_prev_remb))->format('d/m/Y')}}
                    </div>
                </div>
            </div>
        </div>

        @php

            $i = 0;

        @endphp

        <div class="flex flex-col mb-10">
            @foreach ($calendriers as $calendrier)
                @php
                    $i++;
                @endphp
                <div class="flex">
                    <a href="#"
                        class="flex w-full items-center px-3 py-3 border-b  space-x-2 text-gray-700 hover:text-orange-700 hover:bg-gray-100">
                        <div class="flex justify-between  items-center w-full">
                            <div class="flex flex-col">
                                {{$i}}
                            </div>
                            <div class="flex flex-col">
                                {{(new DateTime($calendrier->date_oper))->format('d/m/Y')}}
                            </div>
                            <div class="w-48 text-right">
                                {{number_format($calendrier->montant, 2,',',' ')}}
                            </div>
                        </div>
                    </a>
                </div>

            @endforeach
        </div>

@endsection
