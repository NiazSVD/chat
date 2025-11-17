<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['user_id', 'receiver_id', 'message'];

    // User relationship (sender)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Receiver relationship
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
