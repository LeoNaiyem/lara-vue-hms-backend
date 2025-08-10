<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    protected $fillable = ['patient_id', 'ref_doctor_id', 'under_doctor_id', 'bed_id', 'admission_date', 'created_at', 'department_id', 'advance', 'problem', 'remark'];

    public $timestamps = false; // Disable timestamps

    public function patient(){
        return $this->belongsTo(Patient::class);
    }

    public function ref_doctor(){
        return $this->belongsTo(Doctor::class);
    }
    public function under_doctor(){
        return $this->belongsTo(Doctor::class);
    }

    public function department(){
        return $this->belongsTo(Department::class);
    }

    public function bed(){
        return $this->belongsTo(Bed::class);
    }

}