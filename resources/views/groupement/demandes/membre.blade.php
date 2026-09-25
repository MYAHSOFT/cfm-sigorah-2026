@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">
        <div class="flex justify-between items-center w-full mb-10">
            <x-card-head>
                Demande de prêt
                <x-slot name="action">
                    <a href="{{route('gp.demande.membre')}}" class="mr-5 text-gray-700 hover:text-orange-500">
                        <i class="fal fa-redo fa-xl"></i>
                    </a>
                    @if(count($cart)>0)
                        <a href="{{route('gp.demande.cart')}}" class="mr-5 text-gray-700 hover:text-orange-500">
                            <i class="fal fa-cart-plus fa-xl"></i>
                        </a>
                    @endif
                </x-slot>
            </x-card-head>
        </div>

        <div class="mb-5">
            <form method="post" action="{{route('gp.demande.membre')}}">
                @csrf
                <input type="text" name="name" class="form-control h-14">
            </form>
        </div>

        @foreach ($tiers as $membre)
            <div class="flex h-16">
                @php
                    $url = empty($id_dossier) 
                            ? route('gp.demande.create', $membre->id_tiers)
                            : route('gp.demande.tmp.create', [
                                'id_tiers'  =>$membre->id_tiers,
                                'id_dossier'  =>$id_dossier,
                            ]);
                @endphp
                <a href="{{$url}}"
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
