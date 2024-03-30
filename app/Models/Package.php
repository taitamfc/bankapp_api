<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    protected $table = 'packages';
    protected $fillable = [
        'name',
        'price',
        'max_ceate_account',
        'max_edit_account',
        'max_transfer_free',
        'max_deposit_app',
        'status',
        'bank_code',
        'type',
    ];
}
