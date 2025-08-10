<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $fillable = ['name', 'medicine_category_id', 'medicine_type_id', 'generic_name', 'description'];

    public $timestamps = false;

    public function medicineType(){
        return $this->belongsTo(MedicineType::class);
    }
    public function medicineCategory(){
        return $this->belongsTo(MedicineCategory::class);
    }

}
