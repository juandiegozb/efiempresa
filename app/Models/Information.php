<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    use HasFactory;

    protected $table = 'information_user';

    protected $fillable = [
        'user_id',
        'address',
        'phone'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
