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
            <a href="{{route('gp.mbre.edit', $id_tiers)}}" class="mr-5 text-gray-700 hover:text-orange-500">
                <i class="fal fa-pencil fa-lg"></i>
            </a>
            <a href="{{route('gp.mbre.create')}}" class="mr-5 text-gray-700 hover:text-orange-500">
                <i class="fal fa-plus fa-lg"></i>
            </a>
        </div>
    </div>
    <div class="flex justify-center items-center w-full">
        <div class="flex flex-col mr-5">
            <img src="{{!empty($photo)?$photo:asset('img/user.png')}}" alt="photo" class="w-60 h-60 border rounded-md">
            <button class="photo btn btn-warning-alt w-60 mt-3" data-objet="photo" data-url="{{$url}}">
                <i class="fal fa-camera mr-2"></i>
                Ajouter Photo
            </button>
        </div>
        <div class="flex flex-col">
            <img src="{{!empty($signature)?$signature:asset('img/signature.jpg')}}" alt="photo" class="w-60 h-60 border rounded-md">
            <button class="photo btn btn-warning-alt w-60 mt-3" data-objet="signature" data-url="{{$url}}">
                <i class="fal fa-signature mr-2"></i>
                Ajouter Signature
            </button>
        </div>
    </div>
    <div class="pt-3">@php
        $groups = [
            1=>"Informations principales",
            2=>"Autres informations"
        ];
    @endphp
    @foreach($groups as $key=>$group)
    <div class="flex justify-between bg-gray-500 bg-opacity-20 pl-5 py-1 mb-3 text-gray-500 font-semibold">
        <div>
            {{$group}}
        </div>
    </div>
    <div class="flex flex-col w-full">
        @if($key == 1)
        <div class="w-full">
            <x-show>
                <x-slot name="label">
                    ID Tiers
                </x-slot>
                {{$id_tiers}}
            </x-show>
        </div>
        @endif
        @foreach ($fields as $name => $attribues)
            @if($attribues->group == $key)
                <div class="w-full mb-2">
                    <x-show>
                        <x-slot name="label">
                            {{ !empty($attribues->label) ? $attribues->label : $name }}
                        </x-slot>
                        @switch($attribues->type)
                            @case('date')
                                {{(new DateTime($attribues->value))->format('d/m/Y')}}
                                @break
                            @case('select')
                                {{ $attribues->value }}
                                    @if(key_exists($attribues->value,$attribues->options))
                                        {{$attribues->options[$attribues->value]}}
                                    @endif
                                @break
                            @default
                            {{$attribues->value}}
                        @endswitch
                    </x-show>
                </div>
            @endif
        @endforeach
    </div>
    @endforeach
    </div>

    @if(!empty($membre->status))
        @if($membre->status == 'D')
        <form id="frmMembre" method="post" action="{{route('gp.mbre.in')}}">

            @csrf

            <input type="hidden" name="folio" value="{{$membre->tiers_id}}">

            <div class="flex justify-center space-x-3 py-5 text-orange-700">
                Ce membre est bloqué, voullez vous le débloquer ?
            </div>

            <div class="flex justify-center space-x-3 py-5">

                <button type="submit" class="msg-box-show btn btn-warning">
                    <i class="fal fa-user-lock mr-1"></i>
                    Débloquer le membre
                </button>

            </div>

        </form>
        @endif
    @endif

</div>

<x-import-file :params="$params">
    <input type="hidden" name="urlModule" id="urlModule">
    <input type="hidden" name="objetFile" id="objetFile">
</x-import-file>

@endsection

@section('script')
    <script>

        let btnPhoto = document.querySelectorAll('.photo');

        for (let i = 0; i < btnPhoto.length; i++) {

            btnPhoto[i].addEventListener('click', function(e){
                e.preventDefault();

                let objet = this.getAttribute('data-objet');
                let url = this.getAttribute('data-url');

                document.getElementById('objetFile').setAttribute('value', objet);
                document.getElementById('urlModule').setAttribute('value', url);

                Modal("#modalFile", "show");

            })

        }

        document.getElementById('btnSend').addEventListener("click", function(e){
            document.getElementById('frmFile').submit();
        });

    </script>
@endsection
