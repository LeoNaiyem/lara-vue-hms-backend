<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = ['name', 'mobile', 'dob', 'mob_ext', 'gender', 'profession'];

    public $timestamps = false; // Disable timestamps

    public function getAgeAttribute()
    {
        return Carbon::parse($this->dob)->age;
    }

    // public function invoices()
    // {
    //     return $this->hasMany(Invoice::class);
    // }
    public function latestInvoice()
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }
}