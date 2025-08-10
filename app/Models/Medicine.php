<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected $fillable = ['name', 'medicine_category_id', 'medicine_type_id', 'generic_name', 'description'];

    public $timestamps = false;

}