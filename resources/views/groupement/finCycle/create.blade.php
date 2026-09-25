@extends('layouts.app')


@section('content')
    <div class="flex flex-col w-full px-5">
        <x-card-head>
           Fin cycle
            <x-slot name="action">
                <a href="{{route('gp.finc.show', $dossier->id_dossier)}}" class="mr-3 text-orange-400 hover:text-orange-500">
                    <i class="fad fa-folder fa-lg"></i>
                </a>
            </x-slot>
        </x-card-head>

        <div class="flex flex-col w-full text-gray-700 border border-orange-200 rounded-md mb-5">
            <div class="flex flex-col px-5 py-5 space-y-3">
                <div class="flex border-b-2">
                    <div class="flex flex-1 truncate">
                        Capital octroyé
                    </div>
                    <div class="text-right w-1/3">
                        {{number_format($cd_operation->mtt_octroye, 2,',',' ')}}
                    </div>
                </div>
                <div class="flex border-b-2">
                    <div class="flex flex-1 truncate">
                        Intérêt
                    </div>
                    <div class="text-right w-1/3">
                        {{number_format($echeancier->interet, 2,',',' ')}}
                    </div>
                </div>
                <div class="flex border-b-2">
                    <div class="flex flex-1 truncate">
                        Pénalité
                    </div>
                    <div class="text-right w-1/3">
                        {{number_format(0, 2,',',' ')}}
                    </div>
                </div>
                <div class="flex border-b-2">
                    <div class="flex flex-1 truncate">
                        Montant à rembourser
                    </div>
                    <div>
                        {{number_format($cd_operation->mtt_octroye + $echeancier->interet, 2,',',' ')}}
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($cf_operation))
        <div class="flex flex-col w-full text-gray-700 border border-orange-200 rounded-md mb-5">
            <div class="flex flex-col px-5 py-5 space-y-3">
                <div class="flex border-b-2">
                    <div class="flex flex-1 truncate">
                        Remboursement effectué
                    </div>
                    <div class="text-right w-1/3">
                        {{number_format($cf_operation->mtt_remb, 2,',',' ')}}
                    </div>
                </div>
                <div class="flex border-b-2">
                    <div class="flex flex-1 truncate">
                        Dépôt
                    </div>
                    <div class="text-right w-1/3">
                        {{number_format($cf_operation->mtt_depot, 2,',',' ')}}
                    </div>
                </div>
                <div class="flex border-b-2">
                    <div class="flex flex-1 truncate">
                        Retrait
                    </div>
                    <div>
                        {{number_format($cf_operation->mtt_retrait, 2,',',' ')}}
                    </div>
                </div>
                <div class="flex border-b-2">
                    <div class="flex flex-1 truncate">
                        Amende
                    </div>
                    <div>
                        {{number_format($cf_operation->penalite, 2,',',' ')}}
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="flex flex-col mb-10">
            <form id="frmFinCycle" method="post" action="{{route('gp.finc.store', $dossier->id_dossier)}}">
                @csrf
                <input type="hidden" name="codeGroupe" value="{{$dossier->groupe_id}}">
                <input type="hidden" name="dossierId" value="{{$dossier->id_dossier}}">
                <input type="hidden" name="produit" value="CFM01">

                <div class="flex space-x-4 mb-5">
                    <div class="w-full">
                        <x-label>
                            Pour enregistrer la fin cycle, veuillez renseigner la date d'octroi.
                        </x-label>
                        <div>
                            <input type="date" name="dateOper"
                            value="{{ !empty($dossier->fin_cycle) ? $dossier->fin_cycle : date('Y-m-d')}}"
                            class="form-control"/>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <div class="flex justify-center items-center space-x-5 mt-10">
            <button class="btn btn-warning msg-box-show">
                <i class="fal fa-save mr-2"></i>
                Enregistrer
            </button>
            <a href="{{route('gp.finc.index')}}" class="btn btn-dark">
                <i class="fal fa-times mr-2"></i>
                Annuler
            </a>
        </div>


        <x-msg-box>
            <x-slot name="title">
                Fin cycle
            </x-slot>
            Voulez vous réellement enregistrer cette fin cycle ?
        </x-msg-box>

    </div>
@endsection


@section('script')

    <script>
        document.getElementById("frmFinCycle").addEventListener("submit", function(e){
            e.preventDefault();
        });
        document.querySelector(".msg-box-show").addEventListener("click", function(e){
            e.preventDefault();
            Modal("#MsgBox", "show");
        })
        document.getElementById("btnYes").addEventListener("click", function(e){
            e.preventDefault();
            document.getElementById("frmFinCycle").submit();
        });
    </script>

@endsection
