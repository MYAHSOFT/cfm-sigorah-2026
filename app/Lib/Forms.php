<?php
namespace App\Lib;

use Illuminate\Http\Request;

class Forms
{
    /**
     * Le nom du modèle du formulaire
     *  @param string $forms
     *
     *  @return array $fields
     */
    public static function get($forms)
    {

        $fields = [];

        $file = __DIR__."/../Forms/$forms.php";

        if(file_exists($file)){
            $fields = include $file;
        }

        return $fields;

    }

    /**
     * @param  \Illuminate\Http\Request  $request
     *
     * @param string $forms le nom du modèle de formulaire
     *
     */

    public static function input(Request $request, $forms)
    {

        $fields = self::get($forms);

        $request_data = $request->all();

        unset($request_data['_token']);

        $i = 0;

        $data = [];

        foreach ($request_data as $key => $value) {

            if(key_exists($key, $fields)){
                $data[$fields[$key]['field']]=$value;
            }

            $i++;
        }

        return $data;

    }

    // public static function show($data, $forms)
    // {

    //     $fields = self::get($forms);

    //     $attribues = [];

    //     foreach ($fields as $key => $value) {

    //         $field = $value['field'];
    //         $attribue['value'] = $data->$field;
    //         $attribue['label'] = $value['label'];
    //         $attribue['group'] = key_exists('group', $value) ? $value['group'] : '';
    //         $attribue['type'] = $value['type'];
    //         $attribue['options'] = key_exists('options',$value) ? $value['options'] : [];

    //         $attribues[$key] = (object)$attribue;

    //     }

    //     return $attribues;

    // }

    public static function show($data, $forms)
    {

        $fields = self::get($forms);

        $attribues = [];

        foreach ($fields as $key => $value) {

            $field = $value['field'];
            $attribue['value'] = !empty($data->$field) ?$data->$field : '';
            $attribue['label'] = $value['label'];
            $attribue['group'] = key_exists('group', $value) ? $value['group'] : '';
            $attribue['type'] = $value['type'];
            $attribue['format'] =  key_exists('format', $value) ? $value['format'] : '';
            $attribue['options'] = key_exists('options',$value) ? $value['options'] : [];

            $attribue['readonly']   = key_exists('readonly', $value) ? $value['readonly'] : '';

            $attribues[$key] = (object)$attribue;

        }

        return $attribues;

    }

}

