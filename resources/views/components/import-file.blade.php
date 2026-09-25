<div id="modalFile" class="modal hidden justify-center fixed w-full h-full top-0 left-0 z-50 bg-black bg-opacity-80">
    <div class="w-4/5 pt-56">
        <div class="rounded-lg border border-gray-300 bg-white py-3">
            <div class="border-b border-gray-300 px-5 font-semibold">
                <div class="flex justify-between py-2">
                    <div>
                        Importer un fichier
                    </div>
                    <div>
                        <a href="#" class="btn-modal-close">
                            <i class="fal fa-times"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between py-3 px-5">
                <form id="frmFile" method="post" action="{{$params->url}}" enctype="multipart/form-data">
                    @csrf
                    {{$slot}}
                    <input type="file" name="file" id="file">
                </form>
            </div>
            <div class="flex items-center justify-between py-3 px-5">
                <button id="btnSend" class="btn btn-warning">
                    <i class="fal fa-send"></i>
                    Envoyer
                </button>
            </div>
        </div>
    </div>
</div>
