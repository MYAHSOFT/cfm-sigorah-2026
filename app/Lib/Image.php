<?php
namespace App\Lib;

use App\Models\Customer\Customer;

class Image
{
    public static function get($img)
    {


        $url = "";

        $file = env('DISK_IMG').DIRECTORY_SEPARATOR.$img;
        // $file = public_path('img').DIRECTORY_SEPARATOR.$img;

        if(!empty($img)){
            if(file_exists($file)){
                $url = implode(DIRECTORY_SEPARATOR, [env('URL_IMG'), 'img', $img]);
            }
        }

        // dd($url);

        return $url;

    }

}
