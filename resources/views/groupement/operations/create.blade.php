@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10 mb-10">
        <x-card-head>
           Versement
            <x-slot name="action">
                {{-- <a href="{{route('gp.versement.show', [
                    'id_dossier'    =>$dossier->id_dossier
                ])}}" class=" hover:text-orange-500">
                    <i class="fal fa-plus fa-lg"></i>
                </a>
                <a href="{{route('gp.operation.show', [
                    'id_dossier'    =>$dossier->id_dossier,
                    'tri'   =>'asc'
                ])}}" class=" hover:text-orange-500">
                    <i class="fal fa-sort-numeric-up fa-lg"></i>
                </a>
                <a href="{{route('gp.operation.show', [
                    'id_dossier'    =>$dossier->id_dossier,
                    'tri'   =>'desc'
                ])}}" class=" hover:text-orange-500">
                    <i class="fal fa-sort-numeric-up-alt fa-lg"></i>
                </a> --}}
            </x-slot>
        </x-card-head>


        <div class="flex flex-col space-y-5 mb-5">
            <div>
                <x-show>
                    <x-slot name="label">Remboursement</x-slot>
                    {{number_format($sum->remboursement,2,',',' ')}}
                </x-show>
            </div>
            <div>
                <x-show>
                    <x-slot name="label">Dépôt</x-slot>
                    {{number_format($sum->depot,2,',',' ')}}
                </x-show>
            </div>
            <div>
                <x-show>
                    <x-slot name="label">Retrait</x-slot>
                    {{number_format($sum->retrait,2,',',' ')}}
                </x-show>
            </div>
            <div>
                <x-show>
                    <x-slot name="label">Pénalité</x-slot>
                    {{number_format($sum->penalite,2,',',' ')}}
                </x-show>
            </div>
        </div>

        <div class="flex flex-col mb-10">
            <form id="frmOperation" method="post" action="{{route('gp.operation.store')}}">
                @csrf
                <input type="hidden" name="codeGroupe" value="{{$dossier->groupe_id}}">
                <input type="hidden" name="dossierId" value="{{$dossier->id_dossier}}">
                <input type="hidden" name="reunion" value="{{$reunion}}">
                <input type="hidden" name="produit" value="CFM01">

                <div class="flex space-x-4 mb-5">
                    <div class="w-full">
                        <x-label>
                            Veuillez renseigner la date d'opération.
                        </x-label>
                        <div>
                            <input type="date" name="dateOper"
                            value="{{date('Y-m-d')}}"
                            class="form-control"/>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <div class="flex justify-center space-x-5">
            <button class="msg-box-show btn btn-warning">
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
            Opération
        </x-slot>
        Voulez vous réellement enregistrer cette opération ?
    </x-msg-box>

@endsection


@section('script')

    <script>
        document.getElementById("frmOperation").addEventListener("submit", function(e){
            e.preventDefault();
        });
        document.querySelector(".msg-box-show").addEventListener("click", function(e){
            e.preventDefault();
            Modal("#MsgBox", "show");
        })
        document.getElementById("btnYes").addEventListener("click", function(e){
            e.preventDefault();
            document.getElementById("frmOperation").submit();
        });
    </script>

@endsection
