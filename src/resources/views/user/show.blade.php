@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endsection

@section('content')
<div class="show">
    <h2 class="show-heading">勤怠詳細</h2>
    @if($attendance->requests()->where('status', 'pending')->exists())
        <div class="show-content">
            <table class="show-content__table">
                <tr class="show-content__row">
                    <th class="show-content__column">名前</th>
                    <td class="show-content__record"><p class="show-content__name">{{ $user->name }}</p></td>
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
            <p class="show-form__span">*承認待ちのため修正はできません。</p>
        </div>
    @elseif(Auth::user()->role === 'admin')
        <form class="show-form" action="{{ route('admin.edit', ['attendance_id' => $attendance->id]) }}" method="post">
            @csrf
            <table class="show-content__table">
                <tr class="show-content__row">
                    <th class="show-content__column">名前</th>
                    <td class="show-content__record"><p class="show-content__name">{{ $user->name }}</p></td>
                </tr>
                <tr class="show-content__row">
                    <th class="show-content__column">日付</th>
                    <td class="show-content__record-input">
                        <input class="show-content__date-input" type="text" name="year" value="{{ old('year', \Carbon\Carbon::parse($attendance->date)->format('Y年')) }}">
                        <input class="show-content__date-input" type="text" name="month_day" value="{{ old('month_day', \Carbon\Carbon::parse($attendance->date)->format('n月j日')) }}">
                    </td>
                </tr>
                <tr class="show-content__row">
                    <th class="show-content__column">出勤・退勤</th>
                    <td class="show-content__record-input">
                        <input class="show-content__time-input" type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in) }}">
                        <span class="show-content__time-span">～</span>
                        <input class="show-content__time-input" type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out) }}">
                        <p class="error-message">
                            @error('clock_out')
                                {{$message}}
                            @enderror
                        </p>
                    </td>
                </tr>
                @forelse($attendance->breaks as $index => $break)
                    <tr class="show-content__row">
                        <th class="show-content__column">休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                        <td class="show-content__record-input">
                            <input class="show-content__time-input" type="time" name="breaks[{{ $index }}][break_start]" value="{{ old('breaks.' . $index . '.break_start', $break->break_start) }}">
                            <span class="show-content__time-span">～</span>
                            <input class="show-content__time-input" type="time" name="breaks[{{ $index }}][break_end]" value="{{ old('breaks.' . $index . '.break_end', $break->break_end) }}">
                            <p class="error-message">
                                @error('break_error')
                                    {{$message}}
                                @enderror
                            </p>
                        </td>
                    </tr>
                @empty
                    <tr class="show-content__row">
                        <th class="show-content__column">休憩</th>
                        <td class="show-content__record-input">
                            <input class="show-content__time-input" type="time" name="breaks[0][break_start]" value="{{ old('breaks.0.break_start', '') }}">
                            <span class="show-content__time-span">～</span>
                            <input class="show-content__time-input" type="time" name="breaks[0][break_end]" value="{{ old('breaks.0.break_end', '') }}">
                            <p class="error-message">
                                @error('break_error')
                                    {{$message}}
                                @enderror
                            </p>
                        </td>
                    </tr>
                @endforelse
                <tr class="show-content__row">
                    <th class="show-content__column">備考</th>
                    <td class="show-content__record-input">
                        <textarea class="show-content__textarea" name="description">{{ old('description') }}</textarea>
                        <span class="error-message">
                            @error('description')
                                {{$message}}
                            @enderror
                        </span>
                    </td>
                </tr>
            </table>
            <div class="show-form__button">
                <input class="show-form__button-submit" type="submit" value="修正">
            </div>
        </form>
    @else
        <form class="show-form" action="{{ route('user.edit', ['attendance_id' => $attendance->id]) }}" method="post">
            @csrf
            <table class="show-content__table">
                <tr class="show-content__row">
                    <th class="show-content__column">名前</th>
                    <td class="show-content__record"><p class="show-content__name">{{ $user->name }}</p></td>
                </tr>
                <tr class="show-content__row">
                    <th class="show-content__column">日付</th>
                    <td class="show-content__record-input">
                        <input class="show-content__date-input" type="text" name="year" value="{{ old('year', \Carbon\Carbon::parse($attendance->date)->format('Y年')) }}">
                        <input class="show-content__date-input" type="text" name="month_day" value="{{ old('month_day', \Carbon\Carbon::parse($attendance->date)->format('n月j日')) }}">
                    </td>
                </tr>
                <tr class="show-content__row">
                    <th class="show-content__column">出勤・退勤</th>
                    <td class="show-content__record-input">
                        <input class="show-content__time-input" type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in) }}">
                        <span class="show-content__time-span">～</span>
                        <input class="show-content__time-input" type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out) }}">
                        <p class="error-message">
                            @error('clock_out')
                                {{$message}}
                            @enderror
                        </p>
                    </td>
                </tr>
                @forelse($attendance->breaks as $index => $break)
                    <tr class="show-content__row">
                        <th class="show-content__column">休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                        <td class="show-content__record-input">
                            <input class="show-content__time-input" type="time" name="breaks[{{ $index }}][break_start]" value="{{ old('breaks.' . $index . '.break_start', $break->break_start) }}">
                            <span class="show-content__time-span">～</span>
                            <input class="show-content__time-input" type="time" name="breaks[{{ $index }}][break_end]" value="{{ old('breaks.' . $index . '.break_end', $break->break_end) }}">
                            <p class="error-message">
                                @error('break_error')
                                    {{$message}}
                                @enderror
                            </p>
                        </td>
                    </tr>
                @empty
                    <tr class="show-content__row">
                        <th class="show-content__column">休憩</th>
                        <td class="show-content__record-input">
                            <input class="show-content__time-input" type="time" name="breaks[0][break_start]" value="{{ old('breaks.0.break_start', '') }}">
                            <span class="show-content__time-span">～</span>
                            <input class="show-content__time-input" type="time" name="breaks[0][break_end]" value="{{ old('breaks.0.break_end', '') }}">
                            <p class="error-message">
                                @error('break_error')
                                    {{$message}}
                                @enderror
                            </p>
                        </td>
                    </tr>
                @endforelse
                <tr class="show-content__row">
                    <th class="show-content__column">備考</th>
                    <td class="show-content__record-input">
                        <textarea class="show-content__textarea" name="description">{{ old('description') }}</textarea>
                        <span class="error-message">
                            @error('description')
                                {{$message}}
                            @enderror
                        </span>
                    </td>
                </tr>
            </table>
            <div class="show-form__button">
                <input class="show-form__button-submit" type="submit" value="修正">
            </div>
        </form>
    @endif
</div>
@endsection