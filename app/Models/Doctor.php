<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = ['name', 'phone', 'designation_id', 'department_id', 'created_at', 'updated_at', 'photo'];

    public function designation(){
        return $this->belongsTo(Designation::class);
    }
    public function department(){
        return $this->belongsTo(Department::class);
    }
}