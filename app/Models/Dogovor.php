<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dogovor extends Model
{
    use HasFactory;
    protected $fillable = [
        'counterparty_id',
        'service_id',
        'price',
        'user_id',
    ];
}
