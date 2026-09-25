@extends('layouts.app')

@section('content')


<div class="flex flex-col w-full px-10">

    <x-submenu></x-submenu>

    <div class="mb-5">
        <form method="post" action="{{route('gp.index')}}">
            @csrf
            <input type="text" name="groupeId" value="{{$groupe_id}}" class="form-control">
        </form>
    </div>

    <div class="flex flex-1 flex-col sm:space-y-4">
        @foreach ($groupes as $groupe)
            <div class="flex items-center">
                <a href="{{route('gp.checked', $groupe->id_groupe)}}"
                    class="flex w-full items-center py-2 space-x-2">
                    <span class="text-orange-300 mr-2">
                        <i class="fad fa-folder fa-xl"></i>
                    </span>
                    <span class="font-semibold text-gray-700 truncate">
                        {{$groupe->nom_tiers}}
                    </span>
                    <span class="font-normal text-gray-500">
                        {{$groupe->num_caisse}}
                    </span>
                    <span class="font-normal text-cyan-600">
                        ({{$groupe->id_groupe}})
                    </span>
                </a>
            </div>
        @endforeach
    </div>

</div>

@endsection
