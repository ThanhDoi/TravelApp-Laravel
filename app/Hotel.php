<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
	protected $hidden = ['feature_vectors'];

	public function createFeatureVectors($feature_vectors, $str, $result) {
        if (strpos($feature_vectors, $str) !== false) {
            array_push($result, 1);
        } else {
            array_push($result, 0);
        }
        return $result;
    }

    public function save(array $options = []) {
        $feature_set = array("Nhà hàng", "Internet tốc độ miễn phí (WiFi)", "Quầy bar/Phòng khách", "Dịch vụ phòng", "Xe đưa đón đến sân bay", "Bao gồm bữa sáng", "Dịch vụ giặt khô", "Dịch vụ giặt là", "Nhân viên hỗ trợ khách", "Nhân viên đa ngôn ngữ", "Spa", "Bể bơi");
        $feature_vectors = array();
    	foreach ($feature_set as $feature) {
            $feature_vectors = $this->createFeatureVectors($this->features, $feature, $feature_vectors);
        }
        $this->feature_vectors = implode("|", $feature_vectors);
    	return parent::save();
    }

    public function trips() {
    	return $this->belongsToMany('App\Trip');
    }
}
