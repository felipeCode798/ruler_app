<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'value_subpoena', 'fee','value_total_des', 'value_transport', 'value_cia', 'cia_des', 'value_cia_des', 'price'];
}
