<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class Purchase extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens;

    protected $table='purchase';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status',
        'client_token',
        'receipt_id',
        'expire_date',
        'day',
        'active'
    ];

}
