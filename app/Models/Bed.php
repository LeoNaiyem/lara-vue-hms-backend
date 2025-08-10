<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $fillable = ['bed_number', 'ward_id', 'bed_type', 'status', 'created_at', 'updated_at'];


}