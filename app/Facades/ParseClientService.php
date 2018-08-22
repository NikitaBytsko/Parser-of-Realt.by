<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ParseClientService extends Facade {
    protected static function getFacadeAccessor()
    {
        return 'ParseClient';
    }
}
