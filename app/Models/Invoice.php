<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'counterparty_id',
        'accrual',
        'payment',
        'balance',
        'valuta_id',
        'description',
        'user_id',
    ];
}
