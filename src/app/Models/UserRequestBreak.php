<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserRequestBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_request_id',
        'break_start',
        'break_end',
    ];

    public function userRequest()
    {
        return $this->belongsTo(UserRequest::class);
    }

    public function getFormattedBreakStartAttribute()
    {
        return $this->break_start ? Carbon::parse($this->break_start)->format('H:i') : null;
    }

    public function getFormattedBreakEndAttribute()
    {
        return $this->break_end ? Carbon::parse($this->break_end)->format('H:i') : null;
    }
}
