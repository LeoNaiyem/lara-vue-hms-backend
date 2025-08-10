<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'price', 'discount', 'vat', 'medicine_category_id'];

    public $timestamps = false; // Disable timestamps

    public function medicineCategory(){
        return $this->belongsTo(MedicineCategory::class);
    }

}
