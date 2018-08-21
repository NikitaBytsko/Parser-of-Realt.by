<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfficeObjects extends Model
{

    public function images()
    {
        return $this->hasMany('App\OfficeImages','code','code');
    }
}
