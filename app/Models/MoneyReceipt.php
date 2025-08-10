<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyReceipt extends Model
{
    protected $fillable = ['created_at', 'updated_at', 'patient_id', 'remark', 'receipt_total', 'discount', 'vat'];

    public function patient(){
        return $this->belongsTo(Patient::class);
    }

    public function moneyReceiptDetails(){
        return $this->hasMany(MoneyReceiptDetail::class);
    }
}
