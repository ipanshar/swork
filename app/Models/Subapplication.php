<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subapplication extends Model
{
    use HasFactory;
    protected $fillable=[
        'application_id',
        'organization_id',
        'service_id',
        'article_num',
        'service_num',
        'rate',
        'price',
        'description',
        'invoice_id',
        'user_id',
];
}
