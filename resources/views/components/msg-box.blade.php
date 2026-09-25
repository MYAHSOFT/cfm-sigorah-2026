<div id="MsgBox" class="modal hidden justify-center fixed w-full h-full top-0 left-0 z-50 bg-black bg-opacity-80">
    <div class="w-4/5 pt-56">
        <div class="rounded-lg border border-gray-300 bg-white py-3 text-gray-700">
            <div id="msgTitle" class="border-b border-gray-300 px-5 font-semibold">
                {{$title}}
            </div>
            <div class="checkMsg flex flex-col">
                <div class="flex items-center py-3 px-5">
                    <div id="msgContent">
                        {{$slot}}
                    </div>
                </div>
                <div class="flex items-center justify-center space-x-4 border-t border-gray-300 mt-5 px-5 pt-2">
                    <button id="btnYes" class="btn btn-warning">
                        <i class="fal fa-check mr-1"></i>
                        Oui
                    </button>
                    <button id="btnNo" class="btn btn-dark btn-modal-close">
                        <i class="fal fa-check mr-2"></i>
                        Non
                    </button>
                </div>
            </div>
            <div class="waint hidden justify-center w-full text-orange-500 font-semibold">
                L'enregistrement de l'octroi est en cours. Veuillez patienter ...
            </div>
        </div>
    </div>
</div>
