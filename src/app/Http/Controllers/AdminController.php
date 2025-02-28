<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\UserRequest;
use App\Http\Requests\ShowRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function list(Request $request)
    {
        $currentDate = $request->query('date', Carbon::now()->format('Y-m-d'));

        $current = Carbon::createFromFormat('Y-m-d', $currentDate);

        $previousDay = $current->copy()->subDay()->format('Y-m-d');
        $nextDay = $current->copy()->addDay()->format('Y-m-d');

        $users = User::where('id', '!=', auth()->id())
            ->with(['attendances' => function ($query) use ($current) {
                $query->whereDate('date', $current->toDateString());
            }])->get();

        return view('admin.list', compact('current', 'previousDay', 'nextDay', 'users'));
    }

    public function showEdit(ShowRequest $request, $attendance_id)
    {
        $year = preg_replace('/[^0-9]/', '', $request->input('year'));
        $monthDay = preg_replace('/月/', '-', $request->input('month_day'));
        $monthDay = preg_replace('/日/', '', $monthDay);

        $date = Carbon::createFromFormat('Y-n-j', "$year-$monthDay")->format('Y-m-d');

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

    public function staffList()
    {
        $users = User::where('id', '!=', auth()->id())->get();

        return view ('admin.staff', compact('users'));
    }

    public function person(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);

        $currentDate = $request->query('date', Carbon::now()->format('Y-m'));

        $current = Carbon::createFromFormat('Y-m', $currentDate);

        $previousMonth = $current->copy()->subMonth()->format('Y-m');
        $nextMonth = $current->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::where('user_id', $user_id)
            ->whereYear('date', $current->year)
            ->whereMonth('date', $current->month)
            ->get();

        return view('admin.person', compact('user', 'current', 'previousMonth', 'nextMonth', 'attendances'));
    }

    public function exportCsv(Request $request, $user_id)
    {
        $month = $request->query('date', Carbon::now()->format('Y-m'));

        $attendances = Attendance::where('user_id', $user_id)
            ->where('date', 'like', "$month%")
            ->get();

        $csvHeader = ['日付', '出勤', '退勤', '休憩時間', '合計勤務時間'];

        $response = new StreamedResponse(function () use ($csvHeader, $attendances) {
            $createCsvFile = fopen('php://output', 'w');

            mb_convert_variables('SJIS-win', 'UTF-8', $csvHeader);
            fputcsv($createCsvFile, $csvHeader);

            foreach ($attendances as $attendance) {
                $csvRow = [
                    Carbon::parse($attendance->date)->isoFormat('YYYY/MM/DD(ddd)'),
                    $attendance->formatted_clock_in,
                    $attendance->formatted_clock_out,
                    $attendance->formatted_break_time,
                    $attendance->total_work_time,
                ];

                mb_convert_variables('SJIS-win', 'UTF-8', $csvRow);
                fputcsv($createCsvFile, $csvRow);
            }

            fclose($createCsvFile);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_' . $month . '.csv"',
        ]);

        return $response;
    }

    public function showRequest($attendance_correct_request)
    {
        $userRequest = UserRequest::findOrFail($attendance_correct_request);
        $user = $userRequest->user;

        return view('admin.approve', compact('userRequest', 'user'));
    }

    public function approve(Request $request, $attendance_correct_request)
    {
        $userRequest = UserRequest::findOrFail($attendance_correct_request);
        $userRequest->update(['status' => 'approved']);

        $attendance = Attendance::where('id', $userRequest->attendance_id)->firstOrFail();
        $attendance->update([
            'date' => $userRequest->date,
            'clock_in' => $userRequest->clock_in,
            'clock_out' => $userRequest->clock_out,
            'description' => $userRequest->description,
        ]);

        $attendance->breaks()->delete();

        foreach ($userRequest->breaks as $break) {
            $attendance->breaks()->create([
                'break_start' => $break->break_start,
                'break_end' => $break->break_end,
            ]);
        }

        return redirect()->back();
    }
}
