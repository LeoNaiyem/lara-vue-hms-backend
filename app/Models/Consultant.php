<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultant extends Model
{
    protected $fillable = ['name', 'department_id', 'designation'];

    public $timestamps = false; // Disable timestamps

    public function department(){
        return $this->belongsTo(Department::class);
    }
}