@extends('layouts.guest')

@section('content')
<form method="POST" action="{{ route('login') }}" class="w-full">
    @csrf
    <div class="flex flex-col items-center justify-center pb-5 space-y-4">
        <div class="flex flex-col w-1/2 space-y-5">
            <input type="text" name="name" value="{{old('name')}}" required autofocus
            class="w-full text-3xl  text-gray-700 px-3 border border-cyan-600  rounded-md focus:border-blue-400 focus:border-opacity-50 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-opacity-30 placeholder-gray-500  placeholder-opacity-40"
            placeholder="Nom d'utilisateur"/>
        </div>
        <div class="flex flex-col w-1/2">
            <input type="password" name="password"
            class="w-full text-3xl  text-gray-700 px-3 border border-cyan-600  rounded-md focus:border-blue-400 focus:border-opacity-50 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-opacity-30 placeholder-gray-500  placeholder-opacity-40"
            placeholder="Mot de passe"/>
        </div>
        <div>
            <button type="submit" class="text-cyan-90 font-bold text-lg hover:text-orange-600">
                <i class="fal fa-key mr-1"></i>
                Connexion
            </button>
        </div>
    </div>
</form>
@endsection
