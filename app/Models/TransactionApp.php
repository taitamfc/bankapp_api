<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionApp extends Model
{
    use HasFactory;
    protected $table = "app_tranctions";
    protected $fillable = [
        'user_bank_account_id',
        'reference',
        'from_name',
        'to_name',
        'from_number',
        'to_number',
        'type',
        'amount',
        'surplus',
        'note'
    ];
}