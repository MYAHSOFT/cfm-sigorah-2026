@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">
        <x-card-head>
           Membre de bureau
            <x-slot name="action">
                {{-- <a href="{{route('gp.contrat.index')}}" class="mr-4 hover:text-orange-500">
                    <i class="fad fa-folder fa-xl"></i>
                </a>
                <a href="{{route('gp.demande.membre')}}" class="mr-4 hover:text-orange-500">
                    <i class="fal fa-plus fa-xl"></i>
                </a> --}}
            </x-slot>
        </x-card-head>

        <div class="flex flex-col space-y-5">

            @foreach ($fonctions as $fonction)

                <div>
                    <div class="text-gray-500">
                        {{$fonction->label}} : 
                    </div>

                    @if(key_exists($fonction->code, $membres))
                        <div>
                            <a href="{{route('gp.bureau.create', $fonction->code)}}" class="text-cyan-800 font-semibold">
                                {{($membres[$fonction->code])->name}}
                            </a>
                        </div>
                    @else
                        <div>
                            <a href="{{route('gp.bureau.create', $fonction->code)}}" class="text-orange-500">
                                <i class="fal fa-plus fa-lg"></i>
                                Ajouter un membre
                            </a>
                        </div>
                    @endif
                </div>

            @endforeach

        </div>

    </div>
@endsection
