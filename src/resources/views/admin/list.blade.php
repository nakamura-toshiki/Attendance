@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list">
    <h2 class="list-heading">勤怠一覧</h2>
    <div class="list-month">
        <div class="previous">
            <img class="previous-img" src="{{ asset('storage/arrow.png') }}" alt="矢印">
            <a class="previous-link" href="?date={{ $previousDay }}">前日</a>
        </div>
        <div class="current">
            <img class="current-img" src="{{ asset('storage/calendar.png') }}" alt="カレンダー">
            <span class="current-date">{{ $current->isoFormat('YYYY/MM/DD') }}</span>
        </div>
        @if($current->format('Y-m-d') < \Carbon\Carbon::today()->format('Y-m-d'))
            <div class="next">
                <a class="next-link" href="?date={{ $nextDay }}">翌日</a>
                <img class="next-img" src="{{ asset('storage/arrow.png') }}" alt="矢印">
            </div>
        @else
            <span class="empty"></span>
        @endif
    </div>
    <table class="list-content">
        <tr class="list-content__columns">
            <th class="list-content__column">名前</th>
            <th class="list-content__column">出勤</th>
            <th class="list-content__column">退勤</th>
            <th class="list-content__column">休憩</th>
            <th class="list-content__column">合計</th>
            <th class="list-content__column">詳細</th>
        </tr>
        @foreach($users as $user)
            @foreach($user->attendances as $attendance)
                <tr class="list-content__row">
                    <td class="list-content__name">{{ $user->name }}</td>
                    <td class="list-content__record">{{ $attendance->formatted_clock_in }}</td>
                    <td class="list-content__record">{{ $attendance->formatted_clock_out }}</td>
                    <td class="list-content__record">{{ $attendance->formatted_break_time }}</td>
                    <td class="list-content__record">{{ $attendance->total_work_time }}</td>
                    <td class="list-content__record">
                        <a class="list-content__link" href="{{ route('user.show', ['attendance_id'=>$attendance->id]) }}">詳細</a>
                    </td>
                </tr>
            @endforeach
        @endforeach
    </table>
</div>
@endsection