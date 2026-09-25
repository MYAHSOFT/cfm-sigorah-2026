<div class="font-bold w-1/2 truncate">
@if(!empty($groupe->id_groupe))
    <a href="{{route('gp.index')}}">
        <i class="fad fa-folder-open mr-2"></i>
        {{$groupe->nom_tiers}} ({{$groupe->num_caisse}})
    </a>
@endif
</div>
