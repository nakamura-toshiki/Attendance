<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'break_time',
        'description',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function totalBreakTime()
    {
        $totalSeconds = $this->breaks->sum(function ($break) {
            return Carbon::parse($break->break_start)->diffInSeconds(Carbon::parse($break->break_end));
        });
    
        return gmdate('H:i:s', $totalSeconds);
    }

    public function updateBreakTime()
    {
        $this->update(['break_time' => $this->totalBreakTime()]);
    }

    public function getTotalWorkTimeAttribute()
    {
        if ($this->clock_in && $this->clock_out) {
            $workSeconds = Carbon::parse($this->clock_in)->diffInSeconds(Carbon::parse($this->clock_out));
            $breakSeconds = $this->breaks->sum(function ($break) {
                return Carbon::parse($break->break_start)->diffInSeconds(Carbon::parse($break->break_end));
            });

            $totalWorkSeconds = max(0, $workSeconds - $breakSeconds);

            return gmdate('G:i', $totalWorkSeconds);
        }
        return '0:00';
    }

    public function getFormattedClockInAttribute()
    {
        return $this->clock_in ? Carbon::parse($this->clock_in)->format('H:i') : null;
    }

    public function getFormattedClockOutAttribute()
    {
        return $this->clock_out ? Carbon::parse($this->clock_out)->format('H:i') : null;
    }

    public function getFormattedBreakTimeAttribute()
    {
        return $this->break_time ? Carbon::parse($this->break_time)->format('G:i') : '0:00';
    }

    public function requests()
    {
        return $this->hasMany(UserRequest::class);
    }
}
