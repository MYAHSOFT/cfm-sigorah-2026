@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">
        <x-card-head>
           Choisir un {{$id_fonction}}
            <x-slot name="action">
                <a href="{{route('gp.bureau.index')}}" class="mr-5 text-gray-700 hover:text-orange-500">
                    <i class="fal fa-users fa-xl"></i>
                </a>
            </x-slot>
        </x-card-head>

        <div class="mb-5">
            <form method="post" action="{{route('gp.mbre.search')}}">
                @csrf
                <input type="text" name="name" class="form-control h-14">
            </form>
        </div>

        @foreach ($tiers as $membre)
            <div class="flex h-16">
                <a href="{{route('gp.bureau.store', [
                    'id_tiers'  =>$membre->id_tiers,
                    'id_fonction'  =>$id_fonction
                ])}}"
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

        @if(!empty($tiers->count()))
            <div class="flex justify-center font-bold text-gray-500 mt-5">
                Nombre de membre : {{$tiers->count()}}
            </div>
        @endif

    </div>
@endsection
