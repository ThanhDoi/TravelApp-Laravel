<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attraction extends Model
{
    protected $hidden = ['feature_vectors'];
    
    public function trips() {
    	return $this->belongsToMany('App\Trip');
    }

    public function createFeatureVectors($feature_vectors, $str, $result) {
        if (strpos($feature_vectors, $str) !== false) {
            array_push($result, 1);
        } else {
            array_push($result, 0);
        }
        return $result;
    }

    public function save(array $options = []) {
        $feature_set = array("Điểm thu hút khách tham quan & thắng cảnh", "Địa điểm linh thiêng & tôn giáo", "Địa điểm lịch sử", "Khu vực đi dạo tham quan di tích lịch sử", "Đài kỷ niệm & tượng", "Cầu", "Nông trại", "Đi xe ngắm cảnh", "Đài & tháp quan sát", "Di tích cổ", "Khu lân cận", "Khu vực đi dạo ngắm cảnh", "Tòa nhà chính phủ", "Tòa nhà kiến trúc", "Đấu trường & sân vận động", "Nhà thờ & nhà thờ lớn", "Trung tâm hành chính", "Trường đại học & trường học", "Địa điểm giáo dục", "Danh lam & Thắng cảnh");
        $feature_vectors = array();
    	foreach ($feature_set as $feature) {
            $feature_vectors = $this->createFeatureVectors($this->features, $feature, $feature_vectors);
        }
        $this->feature_vectors = implode("|", $feature_vectors);
    	return parent::save();
    }
}
