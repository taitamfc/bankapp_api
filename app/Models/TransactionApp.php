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
        'account_balance',
        'reference',
        'from_name',
        'recipient_name',
        'to_name',
        'from_number',
        'recipient_account_number',
        'to_number',
        'type',
        'bank_code_id',
        'amount',
        'surplus',
        'note',
        'received_amount',
        'fee_amount',
        'transaction_code',
    ];
}