<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $fillable = ['patient_id', 'consultant_id', 'cc', 'rf', 'investigation', 'advice'];

    public $timestamps = false; // Disable timestamps

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function consultant()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function details(){
    return $this->hasMany(PrescriptionDetail::class);
    }
}
