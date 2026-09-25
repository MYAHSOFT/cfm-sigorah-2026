@extends('layouts.app')


@section('content')

    <div class="flex flex-col w-full px-10 mb-10">
        <x-card-head>
           Modifier la date d'opération
            <x-slot name="action">
            </x-slot>
        </x-card-head>        

        <div class="flex flex-col w-full mb-10 space-y-5 bg-gray-300 bg-opacity-40 py-4 px-3 rounded-lg">
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-calendar-alt"></i>
                        Date
                    </div>
                    <div class="text-center font-semibold">
                        : {{(new DateTime($sum_operation->date_oper))->format('d/m/Y')}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Montant total
                    </div>
                    <div class="text-center font-semibold">
                        @php $mtt_total = $sum_operation->mtt_remb + $sum_operation->mtt_depot - $sum_operation->mtt_retrait + $sum_operation->penalite @endphp
                        :  {{number_format($mtt_total, 2, ',', ' ')}}
                    </div>
                </div>
            </div>
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Remboursement
                    </div>
                    <div class="text-center font-semibold">
                        : {{number_format($sum_operation->mtt_remb, 2, ',', ' ')}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Dépôt
                    </div>
                    <div class="text-center font-semibold">
                        :  {{number_format($sum_operation->mtt_depot, 2, ',', ' ')}}
                    </div>
                </div>
            </div>
            <div class="flex justify-between border-b border-gray-300 text-gray-500">
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Retrait
                    </div>
                    <div class="text-center font-semibold">
                        :  {{number_format($sum_operation->mtt_retrait, 2, ',', ' ')}}
                    </div>
                </div>
                <div class="flex w-1/2">
                    <div class="mr-3">
                        <i class="fal fa-coins"></i>
                        Penalite
                    </div>
                    <div class="text-center font-semibold">
                        :  {{number_format($sum_operation->penalite, 2, ',', ' ')}}
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col mb-10">
            <form id="frmOperation" method="post" action="{{route('gp.operation.update')}}">
                @csrf
                <input type="hidden" name="codeGroupe" value="{{$dossier->groupe_id}}">
                <input type="hidden" name="dossierId" value="{{$dossier->id_dossier}}">
                <input type="hidden" name="refOperation" value="{{$ref_operation}}">

                <div class="flex space-x-4 mb-5">
                    <div class="w-full">
                        <x-label>
                            Veuillez renseigner la date d'opération.
                        </x-label>
                        <div>
                            <input type="date" name="dateOper"
                            value="{{$sum_operation->date_oper}}"
                            class="form-control"/>
                            @error('dateOper')
                                <span class="text-red-500">{{$message}}</span>
                            @enderror
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
