<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MembreRequest extends FormRequest
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
            "lastName" => 'required',
            "dateBirth" => "required|date",
            "placeBirth" => "required",
            "cin" => 'required|numeric',
            "datePiece" => "required|date",
            "lieuPiece" => "required",
            "civilite" => [
                'required',
                Rule::in(['Mr','Mme','Mlle'])
            ],
            "fatherName" => 'required',
            "motherName" => 'required',
            "adresse" => 'required',
            "fonction" => 'required',
            "codeFonction" => "required|exists:referentiel_professions,id_profession",
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {

        return [
            'lastName.required' => "Le nom est obligatoire",
            'dateBirth.required' => "La date de naissance est obligatoire",
            'dateBirth.date' => "Le format de la date de naissance n'est pas valide",
            'placeBirth.required' => "Le lieu de naissance est obligatoire",
            'placeBirth.alpha_dash' => "Le lieu de naissance ne doit conntenir que de texte",
            'cin.required'  =>"Le CIN est obligatoire",
            'cin.numeric'  =>"Le CIN doit être composé uniquement    de valeur numerique",
            'lieuPiece.required'    =>"Le lieu de délivrance est obligatoire",
            'fatherName.required'    =>"Le nom du père est obligatoire",
            'motherName.required'    =>"Le nom de la mère est obligatoire",
            'adresse.required'    =>"L'adresse est obligatoire",
            'fonction.required'    =>"Le fonction est obligatoire",
            'fonction.exists'    =>"Le fonction doit être dans le référentiel",
        ];

    }
}
