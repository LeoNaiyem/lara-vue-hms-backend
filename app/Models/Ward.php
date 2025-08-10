<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $fillable = ['name'];

    public $timestamps = false; // Disable timestamps

}