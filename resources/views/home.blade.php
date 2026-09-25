@extends('layouts.app')

@section('content')

    <div class="flex flex-col w-full justify-center px-5 mt-10">

        <x-submenu></x-submenu>

        <div class="flex flex-col justify-center space-y-3 text-xl">
            <div class="border border-green-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.versement.index')}}" class="flex h-32 text-green-900">
                    <div class="flex items-center justify-center w-32 bg-green-400 bg-opacity-50">
                        <i class="fad fa-sack-dollar fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-green-100 bg-opacity-70">
                       Versement de membre
                    </div>
                </a>
            </div>
            <div class="border border-pink-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.operation.index')}}" class="flex h-32 text-pink-900">
                    <div class="flex items-center justify-center w-32 bg-pink-400 bg-opacity-50">
                        <i class="fad fa-sack-dollar fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-pink-100 bg-opacity-70">
                       Historique de versement
                    </div>
                </a>
            </div>
            <div class="border border-green-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.finc.index')}}" class="flex h-32 text-green-900">
                    <div class="flex items-center justify-center w-32 bg-green-400 bg-opacity-50">
                        <i class="fad fa-address-book fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-green-100 bg-opacity-70">
                        Fin cycle
                    </div>
                </a>
            </div>
            <div class="border border-cyan-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.demande.membre')}}" class="flex h-32 text-cyan-900">
                    <div class="flex items-center justify-center w-32 bg-cyan-400 bg-opacity-40">
                        <i class="fad fa-file-signature fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-cyan-100 bg-opacity-70">
                       Nouveau demande de prêt
                    </div>
                </a>
            </div>
            <div class="border border-yellow-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.doc.index')}}" class="flex h-32 text-yellow-900">
                    <div class="flex items-center justify-center w-32 bg-yellow-400 bg-opacity-40">
                        <i class="fad fa-books fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-yellow-100 bg-opacity-70">
                       Archive des demandes de prêt
                    </div>
                </a>
            </div>
            <div class="border border-red-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.octroi.index')}}" class="flex h-32 text-red-900">
                    <div class="flex items-center justify-center w-32 bg-red-400 bg-opacity-50">
                        <i class="fad fa-file-signature fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-red-100 bg-opacity-70">
                        Octroi de prêt
                    </div>
                </a>
            </div>
            <div class="border border-sky-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.contrat.index')}}" class="flex h-32 text-sky-900">
                    <div class="flex items-center justify-center w-32 bg-sky-400 bg-opacity-50">
                        <i class="fad fa-file-signature fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-sky-100 bg-opacity-70">
                        Contrat de prêt
                    </div>
                </a>
            </div>
            <div class="border border-green-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.calendrier.index')}}" class="flex h-32 text-green-900">
                    <div class="flex items-center justify-center w-32 bg-green-400 bg-opacity-50">
                        <i class="fad fa-calendar-alt  fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-green-100 bg-opacity-70">
                        Calendrier
                    </div>
                </a>
            </div>
            <div class="border border-red-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.fiche.index')}}" class="flex h-32 text-red-900">
                    <div class="flex items-center justify-center w-32 bg-red-400 bg-opacity-50">
                        <i class="fad fa-file-signature fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-red-100 bg-opacity-70">
                        Carnet de membre
                    </div>
                </a>
            </div>
            <div class="border border-purple-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.mbre.create')}}" class="flex h-32 text-purple-900">
                    <div class="flex items-center justify-center w-32 bg-purple-400 bg-opacity-50">
                        <i class="fad fa-user fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-purple-100 bg-opacity-70">
                        Nouveau membre
                    </div>
                </a>
            </div>
            <div class="border border-orange-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.bureau.index')}}" class="flex h-32 text-orange-900">
                    <div class="flex items-center justify-center w-32 bg-orange-400 bg-opacity-50">
                        <i class="fad fa-users fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-orange-100 bg-opacity-70">
                        Membre de bureau
                    </div>
                </a>
            </div>
            <div class="border border-emerald-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.mbre.index')}}" class="flex h-32 text-emerald-900">
                    <div class="flex items-center justify-center w-32 bg-emerald-400 bg-opacity-50">
                        <i class="fad fa-address-book fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-emerald-100 bg-opacity-70">
                        Registre des membres
                    </div>
                </a>
            </div>

            <div class="border border-red-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.mbre.out.create')}}" class="flex h-32 text-red-900">
                    <div class="flex items-center justify-center w-32 bg-red-400 bg-opacity-50">
                        <i class="fad fa-user-lock fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-red-100 bg-opacity-70">
                        Membres à bloquer
                    </div>
                </a>
            </div>

            <div class="border border-red-400 border-opacity-70 shadow-sm">
                <a href="{{route('gp.mbre.out.index')}}" class="flex h-32 text-red-900">
                    <div class="flex items-center justify-center w-32 bg-red-400 bg-opacity-50">
                        <i class="fad fa-list fa-xl" ></i>
                    </div>
                    <div class="flex flex-1 items-center justify-start px-5 bg-red-100 bg-opacity-70">
                        Registre des membres bloqués
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
