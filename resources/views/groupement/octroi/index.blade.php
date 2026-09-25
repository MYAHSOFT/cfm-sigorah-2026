@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">
        <x-card-head>
            Octroi de prêt
            <x-slot name="action">
                {{-- <a href="{{route('gp.mbre.forget')}}" class="mr-5 text-gray-500 hover:text-orange-500">
                    <i class="fal fa-list fa-xl"></i>
                </a>
                <a href="{{route('gp.demande.membre')}}" class="mr-5 text-gray-500 hover:text-orange-500">
                    <i class="fal fa-plus fa-xl"></i>
                </a> --}}
            </x-slot>
        </x-card-head>
        <div class="flex flex-wrap space-y-3">
            @if($demandes->count()>0)
                @foreach ($demandes as $demande)
                    <div class="w-36 mr-5">
                        <a href="{{route('gp.octroi.show', $demande->dossier_id)}}"
                            class="flex flex-col justify-center items-center bg-orange-200 bg-opacity-40 shadow-sm hover:bg-orange-200 hover:bg-opacity-60 border py-3 rounded-md">
                            <div class="mb-2 text-orange-400">
                                <i class="fad fa-folder fa-2xl"></i>
                            </div>
                            <div class="font-semibold text-gray-500">
                                {{$demande->num_caisse}}
                            </div>
                            <div class="text-gray-500">
                                {{(new DateTime($demande->date_demande))->format('d/m/Y')}}
                            </div>
                            <div class="font-normal text-orange-700">
                                {{number_format($demande->mtt_capital,2,',',' ')}}
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
