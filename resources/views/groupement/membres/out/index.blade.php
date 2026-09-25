@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">
        <x-card-head>
           Liste des membres bloqués
            <x-slot name="action">
                <a href="{{route('gp.mbre.out.create')}}" class="mr-4 hover:text-orange-500">
                    <i class="fal fa-plus fa-xl"></i>
                </a>
            </x-slot>
        </x-card-head>

        {{-- <div class="mb-5">
            <form method="post" action="{{route('gp.mbre.search')}}">
                @csrf
                <input type="text" name="name" class="form-control h-14">
            </form>
        </div> --}}

        @if($tiers->count() == 0)
            <div class="flex justify-center space-x-3 py-5 text-orange-700">
                La liste est vide.
            </div>
        @endif

        @foreach ($tiers as $membre)
            <div class="flex h-16">
                <a href="{{route('gp.mbre.show', $membre->id_tiers)}}"
                    class="flex w-full items-center px-3 border-b  space-x-2 text-gray-700 hover:text-orange-700 hover:bg-gray-100">
                    <span>
                        <img src="{{asset('img/user.png')}}" alt="" class="h-10 w-10 rounded-full">
                    </span>
                    <span class="font-bold text-gray-700">
                        {{$membre->nom_tiers}}
                    </span>
                    <span class="font-normal text-gray-500">
                        {{$membre->prenom_tiers}}
                    </span>
                    <span class="font-normal text-gray-500 w-60 block">
                        {{$membre->cin}}
                    </span>
                </a>
            </div>
        @endforeach

    </div>
@endsection
