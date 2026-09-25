@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">
        <x-card-head>
           Liste des membres à bloquer
            <x-slot name="action">
                <a href="{{route('gp.mbre.out.destroy')}}" class="mr-4 hover:text-orange-500">
                    <i class="fal fa-trash-alt fa-xl"></i>
                </a>
            </x-slot>
        </x-card-head>



        <div class="flex justify-center space-x-3 py-5 text-orange-700">
            Enregister la liste pour valider cette action.
        </div>

        @foreach ($tiers as $membre)
            <div class="flex h-16">
                <div href="{{route('gp.mbre.show', $membre->id_tiers)}}"
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
                </div>
            </div>
        @endforeach

        <form id="frmMembre" method="post" action="{{route('gp.mbre.out.lock')}}">

            @csrf

            <div class="flex justify-center space-x-3 py-5">
                <button type="submit" class="msg-box-show btn btn-warning">
                    <i class="fal fa-save mr-1"></i>
                    Enregistrer
                </button>

                <a href="{{route('gp.mbre.out.destroy')}}" class="inline-block btn btn-dark">
                    <i class="fal fa-times mr-1"></i>
                    Annuler
                </a>

            </div>

        </form>

    </div>
@endsection
