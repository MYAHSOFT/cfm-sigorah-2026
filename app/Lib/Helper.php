<?php
namespace App\Lib;

class Helper
{

    public static function lastMonth($year)
    {

        $last_months = [
            1=>"31",
            2=>"28",
            3=>"31",
            4=>"30",
            5=>"31",
            6=>"30",
            7=>"31",
            8=>"31",
            9=>"30",
            10=>"31",
            11=>"30",
            12=>"31",
        ];

        if($year%4 == 0){
            $last_months[2] = "29";
        }


        return $last_months;

    }

}
