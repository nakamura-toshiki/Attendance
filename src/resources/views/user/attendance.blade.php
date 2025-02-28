@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance-status">
        <p class="attendance-status__txt">{{ $attendance->status }}</p>
    </div>
    <form class="attendance-form" action="{{ route('user.attendance') }}" method="post">
        @if($attendance->status === '勤務外')
            @csrf
            <p class="attendance-date">{{ $current->isoFormat('YYYY年MM月DD日(ddd)') }}</p>
            <input class="attendance-input" type="time" name="time" value="{{ $current->isoFormat('HH:mm') }}" >
            <p class="attendance-time">{{ $current->isoFormat('HH:mm') }}</p>
            <input class="attendance-button" type="submit" name="action" value="出勤">
        @elseif($attendance->status === '出勤中')
            @csrf
            <p class="attendance-date">{{ $current->isoFormat('YYYY年MM月DD日(ddd)') }}</p>
            <input class="attendance-input" type="time" name="time" value="{{ $current->isoFormat('HH:mm') }}" >
            <p class="attendance-time">{{ $current->isoFormat('HH:mm') }}</p>
            <div class="attendance-buttons">
                <input class="attendance-button" type="submit" name="action" value="退勤">
                <input class="break-button" type="submit" name="action" value="休憩入">
            </div>
        @elseif($attendance->status === '休憩中')
            @csrf
            <p class="attendance-date">{{ $current->isoFormat('YYYY年MM月DD日(ddd)') }}</p>
            <input class="attendance-input" type="time" name="time" value="{{ $current->isoFormat('HH:mm') }}" >
            <p class="attendance-time">{{ $current->isoFormat('HH:mm') }}</p>
            <input class="break-button" type="submit" name="action" value="休憩戻">
        @elseif($attendance->status === '退勤済')
            <p class="attendance-date">{{ $current->isoFormat('YYYY年MM月DD日(ddd)') }}</p>
            <p class="attendance-time">{{ $current->isoFormat('HH:mm') }}</p>
            <p class="attendance-message">お疲れ様でした。</p>
        @endif
    </form>
</div>
@endsection
