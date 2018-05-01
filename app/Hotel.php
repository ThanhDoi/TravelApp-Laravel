<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    public function users() {
    	return $this->belongsToMany('App\User')->withPivot('rating', 'predict');
    }
}
