<?php
namespace App\Lib;

class Format
{

    public static function number($number)
    {
        return number_format($number, 2,',',' ');

    }

    public static function percent($number, $decimal = 2)
    {
        // $number = $number * 100;
        return number_format($number*100, $decimal,',',' ').'%';

    }

    public static function monetaire($number, $unite = 1)
    {

        return  $unite == 1 ? config('myahs')['money_unite'].' '.number_format($number, 2,',',' ')
                            : number_format($number, 2,',',' ').' '.config('myahs')['money_simbole'];

    }

    public static function numberRoundCeil($number, $round = 3)
    {

        $zero  = '';

        $subRight = '';

        for ($i=0; $i < $round; $i++) {

            $zero .= '0';
        }

        $number_format = 0;

        if(is_numeric($number)){

            $number = ceil($number);

            $subRight = substr($number, -$round);

            $element =  $subRight == $zero ? '' : '1'.$zero;

            $number_format = (int)((substr($number, 0, (strlen($number)-$round)).$zero)) + (int)$element;

        }

        return $number_format;

    }

    public static function daty($date)
    {
        return (new \DateTime($date))->format(config('myahs')['date_format']);
    }

    public static function encoding($text)
    {
        return mb_convert_encoding($text, 'ISO-8859-1','UTF-8');
    }

    public static function phoneStringToArray(
        $phoneString,
        $separator = "/", 
        $titulaireId = null, 
        $typeTitulaire = null)
    {

        $phoneTiers = explode($separator,$phoneString);

        $phones = [];

        foreach ($phoneTiers as $pt) {

            $phones[] = [
                "tiers_id"  =>$titulaireId,
                "phone"  =>substr($pt,0,10),
                "titulaire" =>$typeTitulaire,
            ];
        }

        return $phones;

    }

}
