<?php

namespace Knowfox\Pocket\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Pocket extends Model
{
    protected $fillable = ['access_token', 'last_count', 'last_sync_at', 'user_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
