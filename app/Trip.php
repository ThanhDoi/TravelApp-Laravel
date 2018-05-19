<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
	protected $fillable = ['name', 'start_date', 'end_date', 'user_id'];
	
    public function user() {
    	return $this->belongsTo('App\User');
    }

    public function hotels() {
    	return $this->belongsToMany('App\Hotel');
    }

    public function attractions() {
    	return $this->belongsToMany('App\Attraction');
    }
}
