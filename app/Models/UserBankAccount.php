<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBankAccount extends Model
{
    use HasFactory;
    protected $table = 'user_bank_accounts';
    protected $fillable = [
        'name',
        'image',
        'bank_number',
        'surplus',
        'bank_username',
        'status',
    ];
}