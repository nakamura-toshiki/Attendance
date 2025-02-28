@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endsection

@section('content')
<div class="show">
    <h2 class="show-heading">勤怠詳細</h2>
    <form class="show-form" action="{{ route('admin.approve', ['attendance_correct_request'=>$userRequest->id]) }}" method="post">
        @csrf
        <table class="show-content__table">
            <tr class="show-content__row">
                <th class="show-content__column">名前</th>
                <td class="show-content__record">
                    <p class="show-content__name">
                        {{ $user->name }}
                    </p>
                </td>
            </tr>
            <tr class="show-content__row">
                <th class="show-content__column">日付</th>
                <td class="show-content__record">
                    <span class="show-content__date">{{ \Carbon\Carbon::parse($userRequest->date)->format('Y') }}年</span>
                    <span class="show-content__date">{{ \Carbon\Carbon::parse($userRequest->date)->format('n月j日') }}</span>
                </td>
            </tr>
            <tr class="show-content__row">
                <th class="show-content__column">出勤・退勤</th>
                <td class="show-content__record">
                    <span class="show-content__time">{{ $userRequest->formatted_clock_in }}</span>
                    <span class="show-content__time">～</span>
                    <span class="show-content__time">{{ $userRequest->formatted_clock_out }}</span>
                </td>
            </tr>
            @foreach($userRequest->breaks as $index => $break)
                <tr class="show-content__row">
                    <th class="show-content__column">休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                    <td class="show-content__record">
                        <span class="show-content__time">{{ $break->formatted_break_start }}</span>
                        <span class="show-content__time">～</span>
                        <span class="show-content__time">{{ $break->formatted_break_end }}</span>
                    </td>
                </tr>
            @endforeach
            <tr class="show-content__row">
                <th class="show-content__column">備考</th>
                <td class="show-content__record">
                    <p class="show-content__text">{{ $userRequest->description }}</p>
                </td>
            </tr>
        </table>
        @if($userRequest->status === 'pending')
            <div class="show-form__button">
                <input class="show-form__button-submit" type="submit" value="承認">
            </div>
        @else
            <div class="show-form__tag">
                <span class="show-form__button-txt">承認済み</span>
            </div>
        @endif
    </form>
    </h2>
</div>
@endsection
