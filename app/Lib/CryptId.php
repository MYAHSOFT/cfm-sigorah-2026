<?php
namespace App\Lib;

class CryptId
{

    public static function crypt($id_string)
    {



        $uniq = uniqid('', true);

        $tblUnique = str_split($uniq);

        $tblIdString = str_split($id_string);

        $nb = count($tblIdString);

        $k = 2;

        $serie = [];

        for ($i=0; $i < $nb; $i++) {

            $tblUnique[$k] = $tblIdString[$i];

            $serie[] = $k;

            $k = $k + 2;

        }

        $prefix = strlen($nb) == 1 ? 'Y' : 'Z';

        return $prefix.$nb.implode('', $tblUnique);

    }

    public static function decrypt($strCript)
    {

        $prefix = substr($strCript, 0,1);

        $nb = (int)substr($strCript, 1,1);

        $str = substr($strCript, 2);

        if($prefix == 'Z'){

            $nb = (int)substr($strCript, 1,2);

            $str = substr($strCript, 3);
        }

        $tblUnique = str_split($str);

        $tblIdOper = [];

        $j = 2;

        if(count($tblUnique) > $nb){

            for ($i=0; $i < $nb; $i++) {

                $tblIdOper[$i] = $tblUnique[$j];

                $j = $j + 2;

            }

        }

        return implode('',$tblIdOper);

    }

}
