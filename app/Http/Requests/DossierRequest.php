<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DossierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'dateDebut' =>'required|date',
            'dateFin'   =>'required|date',
            'mode'  =>'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'dateDebut.required' =>"La date de début période est obligatoire",
            'dateDebut.date' =>"Le format de la date n'est pas valide",
            'dateFin.required'   =>"La date de fin période est obligatoire",
            'dateFin.date'   =>"Le format de la date n'est pas valide",
            'mode.required'  =>"Le mode de réunion est obligatoire",
            'mode.numeric'  =>"Le mode de réunion doit être un nombre numérique",
        ];

    }
}
