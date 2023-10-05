<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entries extends Model
{
    use HasFactory;
    protected $fillable = [
        'organization_id',
        'subject_id',
        'subject_count',
        'service_id',
        'service_price',
        'service_count',
        'total_sum',
        'coment',
        'public_date',
        'invoice_id',
        'user_id',
    ];
}
