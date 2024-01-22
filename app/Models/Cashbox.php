<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashbox extends Model
{
    use HasFactory;
    protected $fillable = [
        'counterparty_id',
        'invoice_id',
        'salary_id',
        'personal_id',
        'item_id',
        'incoming',
        'expense',
        'description',
        'user_id',
        'cash',
        'cash_id',
    ];
}
