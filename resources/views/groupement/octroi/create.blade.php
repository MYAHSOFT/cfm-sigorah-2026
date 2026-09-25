@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10 mb-10">
        <div class="flex justify-between items-center w-full mb-10">
            <div class="w-1/2">

            </div>
            <div class="w-1/2 text-right">

            </div>
        </div>

        <div class="flex flex-col w-full border-b-2 mb-10">
            <div class="flex justify-between border-b text-gray-500">
                <div>
                    Nombre demande
                </div>
                <div>
                    Montant total
                </div>
            </div>
            <div class="flex justify-between text-gray-600 font-bold">
                <div>
                    {{count($carts)}}
                </div>
                <div>
                    {{number_format($sum_demande, 2, ',', ' ')}}
                </div>
            </div>
        </div>

        <div class="flex flex-col mb-10">
            <form id="frmContrat" method="post" action="{{route('gp.octroi.store')}}">
                @csrf
                <input type="hidden" name="codeGroupe" value="{{$dossier->groupe_id}}">
                <input type="hidden" name="dossierId" value="{{$dossier->id_dossier}}">
                <input type="hidden" name="produit" value="CFM01">

                <div class="flex space-x-4 mb-5">
                    <div class="w-full">
                        <x-label>
                            Pour enregistrer ce contrat, veuillez renseigner la date d'octroi.
                        </x-label>
                        <div>
                            <input type="date" name="dateContrat"
                            value="{{!empty($dossier->date_prev_octroi)?$dossier->date_prev_octroi:date('Y-m-d')}}"
                            class="form-control"/>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <div class="flex flex-col mb-10">
            @foreach ($demandes as $demande)
                @php
                    $mtt_octroi = key_exists($demande->ref_dde, $carts) ? $carts[$demande->ref_dde] : $demande->mtt_recommande;
                @endphp
                <div class="flex h-16">
                    <a href="{{route('gp.octroi.cart1', $demande->id_tiers)}}"
                        class="flex w-full items-center px-3 border-b  space-x-2 text-gray-700 hover:text-orange-700 hover:bg-gray-100">
                        <img src="{{asset('img/user.png')}}" alt="" class="h-10 w-10 rounded-full">
                        <div class="flex justify-between  items-center w-full">
                            <div class="flex flex-col flex-1">
                                <div>
                                    <span class="font-bold text-gray-700">
                                        {{$demande->nom_tiers}}
                                    </span>
                                    <span class="font-normal text-gray-500">
                                        {{$demande->prenom_tiers}}
                                    </span>
                                    <span class="font-normal italic text-cyan-700">
                                        ({{$demande->cin}})
                                    </span>
                                </div>
                                <span class="font-normal text-gray-500 lowercase">
                                    {{ucfirst(strtolower($demande->objet_pret))}}
                                </span>
                            </div>
                            <div class="w-60 text-right">
                                {{number_format($mtt_octroi, 2,',',' ')}}
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="flex justify-center space-x-5">
            <button type="submit" class="msg-box-show btn btn-warning">
                <i class="fal fa-save mr-1"></i>
                Enregistrer
            </button>

            <a href="{{route('gp.octroi.show', $dossier->id_dossier)}}" class="inline-block btn btn-dark">
                <i class="fal fa-times mr-1"></i>
                Annuler
            </a>
        </div>

    </div>


    <x-msg-box>
        <x-slot name="title">
            Contrat de prêt
        </x-slot>
        Voulez vous réellement enregistrer ce contrat de prêt ?
    </x-msg-box>

@endsection


@section('script')

    <script>
        document.getElementById("frmContrat").addEventListener("submit", function(e){
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

            document.getElementById("frmContrat").submit();
        });
        
    </script>

@endsection
