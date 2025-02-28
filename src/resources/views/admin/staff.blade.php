@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff.css') }}">
@endsection

@section('content')
<div class="staff">
    <h2 class="staff-heading">スタッフ一覧</h2>
    <table class="staff-content">
        <tr class="staff-content__columns">
            <th class="staff-content__column">名前</th>
            <th class="staff-content__column">メ―ルアドレス</th>
            <th class="staff-content__column">月次勤怠</th>
        </tr>
        @foreach($users as $user)
            <tr class="staff-content__row">
                <td class="staff-content__record">{{ $user->name }}</td>
                <td class="staff-content__record">{{ $user->email }}</td>
                <td class="staff-content__record">
                    <a class="staff-content__link" href="{{ route('admin.person', ['user_id'=>$user->id]) }}">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection
