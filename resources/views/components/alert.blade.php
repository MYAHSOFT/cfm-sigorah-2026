@php
$alert = '';
$message = '';
@endphp
@if(session()->has('info'))
@php $alert = 'border-cyan-400 bg-cyan-100 text-cyan-800 bg-opacity-70'; $message = session('info'); @endphp
@endif
@if(session()->has('success'))
@php $alert = 'border-green-400 bg-green-100 text-green-800'; $message = session('success'); @endphp
@endif
@if(session()->has('warning'))
@php $alert = 'border-orange-400 bg-orange-100 text-orange-800'; $message = session('warning'); @endphp
@endif
@if(session()->has('danger'))
@php $alert = 'border-orange-400 bg-orange-100 text-orange-800'; $message = session('danger'); @endphp
@endif
@if(!empty($alert))
<div class="flex justify-between alert {{$alert}} border rounded-md px-5 py-2 mb-5 shadow-md">
    @php
        $msg = explode("@", $message);
        $title = "";
        $msg_content="";
        if(count($msg)>1){
            $title = $msg[0];
            $msg_content = $msg[1];
        }else{
            $msg_content = $message;
        }
    @endphp
    <div>
        @if(!empty($title))
        <span class="font-semibold text-lg">{{ $title }} : </span>
        @endif
        <span>{{$msg_content}}</span>
    </div>
    <div>
        <button class="btn-alert-close focus:outline-none" data-bs-dismiss="alert">
            <i class="fal fa-times-circle fa-lg"></i>
        </button>
    </div>
</div>
@endif
