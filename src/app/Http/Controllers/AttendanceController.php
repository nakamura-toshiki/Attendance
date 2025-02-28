<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\UserRequest;
use App\Http\Requests\ShowRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::where('user_id', auth()->id())->where('date', Carbon::now()->toDateString())->first();

        $current = \Carbon\Carbon::now();

        return view('user.attendance', compact('current', 'attendance'));
    }

    public function attendance(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())->where('date', Carbon::now()->toDateString())->first();
        
        if ($request->input('action') === '出勤'){
            $attendance->update([
                'clock_in' => $request->input('time'),
                'status' => '出勤中',
            ]);
        } elseif ($request->input('action') === '退勤') {
            $attendance->update([
                'clock_out' => $request->input('time'),
                'status' => '退勤済',
            ]);
        } elseif ($request->input('action') === '休憩入') {
            $attendance->breaks()->create([
                'break_start' => $request->input('time'),
            ]);
            $attendance->update([
                'status' => '休憩中',
            ]);
        } elseif ($request->input('action') === '休憩戻') {
            $lastBreak = $attendance->breaks()->latest()->first();
            if ($lastBreak && !$lastBreak->break_end) {
                $lastBreak->update(['break_end' => $request->input('time')]);

                $attendance->updateBreakTime();
            }

            $attendance->update([
                'status' => '出勤中',
            ]);
        }

        return redirect()->back();
    }


    public function list(Request $request)
    {
        $currentDate = $request->query('date', Carbon::now()->format('Y-m'));

        $current = Carbon::createFromFormat('Y-m', $currentDate);

        $previousMonth = $current->copy()->subMonth()->format('Y-m');
        $nextMonth = $current->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::where('user_id', auth()->id())
            ->whereYear('date', $current->year)
            ->whereMonth('date', $current->month)
            ->get();

        return view('user.list', compact('current', 'previousMonth', 'nextMonth', 'attendances'));
    }

    public function show($attendance_id)
    {
        $attendance = Attendance::findOrFail($attendance_id);
        $user = $attendance->user;

        if($attendance->requests()->exists()){
            $userRequest = UserRequest::where('attendance_id', $attendance_id)->firstOrFail();

            return view('user.show', compact('attendance', 'user', 'userRequest'));
        }

        return view('user.show', compact('attendance', 'user'));
    }

    public function showEdit(ShowRequest $request, $attendance_id)
    {
        $year = preg_replace('/[^0-9]/', '', $request->input('year'));
        $monthDay = preg_replace('/月/', '-', $request->input('month_day'));
        $monthDay = preg_replace('/日/', '', $monthDay);

        $date = Carbon::createFromFormat('Y-n-j', "$year-$monthDay")->format('Y-m-d');

        $user = auth()->user();
        if($user->role === 'user'){
            $userRequest = UserRequest::create([
                'user_id' => auth()->id(),
                'attendance_id' => $attendance_id,
                'date' => $date,
                'clock_in' => request('clock_in'),
                'clock_out' => request('clock_out'),
                'description' => request('description'),
            ]);

            $breaks = $request->input('breaks', null);

            if(is_array($breaks)){
                foreach ($request->input('breaks') as $break) {
                    if (!empty($break['break_start']) && !empty($break['break_end'])) {
                        $userRequest->breaks()->create([
                            'break_start' => $break['break_start'],
                            'break_end' => $break['break_end'],
                        ]);
                    }
                }
            }

            return redirect()->route('user.list');
        }else{
            $attendance = Attendance::findOrFail($attendance_id);
            $attendance->update([
                'date' => $date,
                'clock_in' => request('clock_in'),
                'clock_out' => request('clock_out'),
                'description' => request('description'),
            ]);

            foreach ($request->input('breaks') as $break) {
                if (!empty($break['break_start']) && !empty($break['break_end'])) {
                    $attendance->breaks()->update([
                        'break_start' => $break['break_start'],
                        'break_end' => $break['break_end'],
                    ]);
                }
            }

            return redirect()->route('admin.list');
        }
    }

    public function request(Request $request)
    {
        $user = auth()->user();

        $tab = $request->query('tab', 'pending');

        if($user->role === 'user'){
            if($tab === 'pending'){
                $userRequests = UserRequest::where('user_id', auth()->id())
                ->where('status', 'pending')
                ->get();
            }else{
                $userRequests = UserRequest::where('user_id', auth()->id())
                ->where('status', 'approved')
                ->get();
            }
        }else{
            if($tab === 'pending'){
                $userRequests = UserRequest::where('status', 'pending')->get();
            }else{
                $userRequests = UserRequest::where('status', 'approved')->get();
            }
        }

        return view('user.request', compact('tab', 'userRequests'));
    }
}
