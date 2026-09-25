<?php
namespace App\Lib;

class Search
{

    public static function get($name)
    {
        $search = (object)[];

        if(session()->has('search')){

            $searchArray = is_array(session('search')) ? session('search') : [];

            if(key_exists($name, $searchArray)){

                if(is_string($searchArray[$name])){

                    $search = json_decode($searchArray[$name]);
                }

            }

        }

        return $search;

    }

    public static function set($name, $data)
    {

        if(is_array($data)){

            session()->put("search.$name", json_encode($data));

        }

    }

    public static function forget($name)
    {

        if(session()->has('search')){

            $searchArray = is_array(session('search')) ? session('search') : [];

            if(key_exists($name, $searchArray)){

                unset($searchArray[$name]);

            }

        }

    }
}
