<div>
    <div class="text-gray-500 text-sm">
        {{$label}}
    </div>
    <div class="border-b border-gray-300  text-gray-700 font-semibold">
        @if(strlen($slot) > 0)
            {{$slot}}
        @else
            <span class="text-red-500">ND</span>
        @endif
    </div>
</div>
