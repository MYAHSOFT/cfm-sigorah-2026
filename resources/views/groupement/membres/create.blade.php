@extends('layouts.app')

@section('content')

<div class="flex flex-col w-full space-y-4 px-10">
    <div class="flex justify-between items-center w-full mb-10">
        <div class="w-1/2">

        </div>
        <div class="w-1/2 text-right">
            <a href="{{route('gp.mbre.index')}}" class="mr-5 text-gray-700 hover:text-orange-500">
                <i class="fal fa-list fa-lg"></i>
            </a>
            @if(!empty($id_tiers))
            <a href="{{route('gp.mbre.show', $id_tiers)}}" class="mr-5 text-gray-700 hover:text-orange-500">
                <i class="fal fa-times fa-lg"></i>
            </a>
            @endif
        </div>
    </div>

    @if(!empty($id_tiers))
    <div class="flex justify-center items-center w-full space-x-5">
        <div>
            <img src="{{asset('img/user.png')}}" alt="photo" class="w-60 border rounded-md">
            <button class="btn btn-warning-alt w-60 mt-3">
                <i class="fal fa-camera mr-2"></i>
                Ajouter photo
            </button>
        </div>
        <div>
            <img src="{{asset('img/signature.jpg')}}" alt="photo" class="w-60 border rounded-md">
            <button class="btn btn-warning-alt w-60 mt-3">
                <i class="fal fa-signature mr-2"></i>
                Ajouter photo
            </button>
        </div>
    </div>
    @endif
    <div class="flex flex-col">
        <form id="frmTiers" method="post" action="{{$url}}">
            {{-- <input type="hidden" name="caisse" value="{{$caisse->id_caisse}}"> --}}
            <input type="hidden" name="groupeId" value="{{$groupe->id_groupe}}">
            @csrf
            @php
                $groups = [
                    1=>"Informations principales",
                    2=>"Autres informations"
                ];
            @endphp
            @foreach($groups as $key=>$group)
            <div class="flex bg-gray-500 bg-opacity-20 pl-5 py-1 mb-2 mt-5 text-gray-500 font-semibold">
                {{$group}}
            </div>
            <div class="flex flex-col">
                @foreach ($fields as $name => $attribues)
                    @if($attribues->group == $key)
                    <div class="w-full mb-2">
                        <x-label>
                            {{ !empty($attribues->label) ? $attribues->label : $name }}
                        </x-label>
                        <div>
                            <x-fields :name="$name" :attribues="$attribues"></x-fields>
                            @error($name)
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
            @endforeach
        </form>
        <div class="flex space-x-3 py-5">
            <button class="msg-box-show btn btn-warning">
                <i class="fal fa-save mr-1"></i>
                Enregistrer
            </button>

            <a href="{{route('gp.mbre.show', $groupe->id_groupe)}}" class="inline-block btn btn-dark">
                <i class="fal fa-times mr-1"></i>
                Annuler
            </a>
        </div>
    </div>
</div>

<x-msg-box>
    <x-slot name="title">
        Membre
    </x-slot>
    Voulez vous réellement enregistrer les informations de ce membre ?
</x-msg-box>

@endsection

@section('script')
    <script src="{{asset('select2/js/select2.min.js')}}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
        document.getElementById("frmTiers").addEventListener("submit", function(e){
            e.preventDefault();
        });
        document.querySelector(".msg-box-show").addEventListener("click", function(e){
            e.preventDefault();
            Modal("#MsgBox", "show");
        })
        document.getElementById("btnYes").addEventListener("click", function(e){
            e.preventDefault();
            document.getElementById("frmTiers").submit();
        });
    </script>

@endsection

