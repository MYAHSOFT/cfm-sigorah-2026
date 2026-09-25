@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">
        <div class="flex justify-between items-center w-full mb-10">
            <div class="w-1/2">

            </div>
            <div class="w-1/2 text-right">
                <a href="{{route('gp.mbre.forget')}}" class="mr-5 text-gray-700 hover:text-orange-500">
                    <i class="fal fa-list fa-xl"></i>
                </a>
                <a href="{{route('gp.mbre.create')}}" class="mr-5 text-gray-700 hover:text-orange-500">
                    <i class="fal fa-plus fa-xl"></i>
                </a>
            </div>
        </div>

        {{-- <div class="mb-5">
            <form method="post" action="{{route('gp.mbre.search')}}">
                @csrf
                <input type="text" name="name" class="form-control h-14">
            </form>
        </div> --}}

        <form id="frmMembre" method="post" action="{{route('gp.mbre.out.store')}}">

            @csrf

        @foreach ($tiers as $membre)
            <div class="flex h-16">
                <div href="{{route('gp.mbre.show', $membre->id_tiers)}}"
                    class="flex w-full items-center px-3 border-b  space-x-2 text-gray-700 hover:text-orange-700 hover:bg-gray-100">
                    <span class="mr-5">
                        <input type="checkbox" name="bloquer[]" value="{{$membre->id_tiers}}" class="checkbox">
                    </span>
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

            <div class="flex justify-center space-x-3 py-5">
                <button type="submit" class="msg-box-show btn btn-warning">
                    <i class="fal fa-save mr-1"></i>
                    Continuer
                </button>

                <a href="{{route('gp.mbre.index')}}" class="inline-block btn btn-dark">
                    <i class="fal fa-times mr-1"></i>
                    Annuler
                </a>

            </div>

        </form>

    </div>
@endsection
