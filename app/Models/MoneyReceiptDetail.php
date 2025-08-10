<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyReceiptDetail extends Model
{
    public $timestamps=false;

    public function service(){
    return $this->belongsTo(Service::class);
    }

}
