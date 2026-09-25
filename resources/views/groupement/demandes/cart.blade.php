@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10">

        <x-card-head>
            Demande de prêt
            <x-slot name="action">
                @if($nb_tiers_rest>0)
                    <a href="{{route('gp.demande.membre')}}" class="mr-3 text-gray-700 hover:text-orange-500">
                        <i class="fal fa-plus fa-xl"></i>
                    </a>
                @endif
                @if(count($cart)>0)
                <a href="#" id="btnForget" class="mr-3 text-gray-700 hover:text-orange-500">
                    <i class="fal fa-trash-alt fa-xl"></i>
                </a>
                @endif
            </x-slot>
        </x-card-head>

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
                    {{count($cart)}}
                </div>
                <div>
                    {{number_format($sum_demande, 2, ',', ' ')}}
                </div>
            </div>
        </div>

        <div class="flex flex-col">
        @foreach ($tiers as $membre)
            @php
                $cart = (object)$carts[$membre->id_tiers]
            @endphp
            <div class="flex items-center border-b hover:bg-gray-100">
                <a href="#"
                    class="flex w-full items-center px-3 space-x-2 text-gray-700 hover:text-orange-700 hover:bg-gray-100 mr-2">
                    <img src="{{asset('img/user.png')}}" alt="" class="h-10 w-10 rounded-full">
                    <div class="flex justify-between  items-center w-full">
                        <div class="flex flex-col">
                            <div>
                                <span class="font-bold text-gray-700">
                                    {{$membre->nom_tiers}}
                                </span>
                                <span class="font-normal text-gray-500">
                                    {{$membre->prenom_tiers}}
                                </span>
                                <span class="font-normal italic text-cyan-700">
                                    ({{$membre->cin}})
                                </span>
                            </div>
                            <span class="font-normal text-gray-500 lowercase">
                                {{ucfirst(strtolower($cart->objet))}}
                            </span>
                        </div>
                        <div class="w-48 text-right">
                            {{number_format($cart->montant, 2,',',' ')}}
                        </div>
                    </div>
                </a>
                <a href="{{route('gp.demande.destroy', $membre->id_tiers)}}" class="text-red-500 mr-2">
                    <i class="fal fa-times fa-xl"></i>
                </a>
            </div>
        @endforeach
        </div>

        @if(count($carts)>0)
            <div class="flex justify-center space-x-3 py-5">
                <a href="{{route('gp.doc.create', 'credit')}}" class="btn btn-warning">
                    <i class="fal fa-file-check fa-lg mr-2"></i>
                    Finaliser
                </a>
            </div>
        @endif

        <form id="frmDemande"  method="post" action="{{route('gp.demande.forget')}}">
            @csrf
        </form>

    </div>
    <x-msg-box>
        <x-slot name="title">
            Demande de prêt
        </x-slot>
        Voulez vous réellement supprimer cette liste ?
    </x-msg-box>
@endsection

@section('script')

    <script>
        document.getElementById("frmDemande").addEventListener("submit", function(e){
            e.preventDefault();
        });
        document.getElementById("btnForget").addEventListener("click", function(e){
            e.preventDefault();
            Modal("#MsgBox", "show");
        })
        document.getElementById("btnYes").addEventListener("click", function(e){
            e.preventDefault();
            document.getElementById("frmDemande").submit();
        });
    </script>

@endsection
