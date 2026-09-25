@extends('layouts.app')

@section('content')
<div class="w-full justify-center px-5">

    <div class="flex flex-col w-full border-b-2 mb-10">
        <div class="flex justify-between border-b text-gray-500">
            <div>
                Nombre demande
            </div>
            <div>
                Montant total
            </div>
        </div>
        <div class="flex justify-between text-gray-700 font-bold">
            <div>
                {{$nb_demande}}
            </div>
            <div>
                {{number_format($sum_demande, 2, ',', ' ')}}
            </div>
        </div>
    </div>

    <div class="mt-10">
        <x-alert/>
        <form id="frmDossier" method="post" action="{{ $url }}">
            @csrf
            <input type="hidden" name="groupeId" value="{{$groupe->id_groupe}}">
            <div class="flex flex-col md:w-full lg:flex-row  lg:flex-wrap space-y-3">
                @foreach ($fields as $name => $attribues)

                    <div class="w-full">
                        <div class="text-sm text-gray-500">
                            {{ !empty($attribues->label) ? $attribues->label : $name }}
                        </div>
                        <div>
                            <x-fields :name="$name" :attribues="$attribues"></x-fields>
                            @error($name)
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                @endforeach
            </div>
        </form>
    </div>
    <div class="flex justify-center space-x-3 py-5">
        <button class="msg-box-show btn btn-warning">
            <i class="fal fa-save mr-1"></i>
            Enregistrer
        </button>

        @php
            $url_annuler = empty($id_dossier) ? route('gp.demande.cart') : route('gp.demande.show', $id_dossier)
        @endphp

        <a href="{{$url_annuler}}" class="inline-block btn btn-dark">
            <i class="fal fa-times mr-1"></i>
            Annuler
        </a>

    </div>
</div>


<x-msg-box>
    <x-slot name="title">
        Dossier
    </x-slot>
    Voulez vous réellement céer ce nouveau dossier ?
</x-msg-box>
@endsection

@section('script')

    <script>
        document.getElementById("frmDossier").addEventListener("submit", function(e){
            e.preventDefault();
        });
        document.querySelector(".msg-box-show").addEventListener("click", function(e){
            e.preventDefault();
            Modal("#MsgBox", "show");
        })
        document.getElementById("btnYes").addEventListener("click", function(e){
            e.preventDefault();

            let checkMsg = document.querySelector(".checkMsg");

            let userWaint = document.querySelector(".waint");

            checkMsg.classList.remove('flex');
            checkMsg.classList.add('hidden');

            userWaint.classList.add('flex');
            userWaint.classList.remove('hidden');
            
            document.getElementById("frmDossier").submit();
        });
    </script>

@endsection
