@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
<div class="request">
    <h2 class="request-heading">申請一覧</h2>
    <div class="tab-menu">
        <a class="tab {{ $tab === 'pending' ? 'selected' : '' }}" href="{{ route('user.request', array_merge(request()->query(), ['tab' => 'pending'])) }}">承認待ち</a>
        <a class="tab {{ $tab === 'approved' ? 'selected' : '' }}" href="{{ route('user.request', array_merge(request()->query(), ['tab' => 'approved'])) }}">承認済み</a>
    </div>
    <table class="request-content">
        <tr class="request-content__columns">
            <th class="request-content__column">状態</th>
            <th class="request-content__column">名前</th>
            <th class="request-content__column">対象日時</th>
            <th class="request-content__column">申請理由</th>
            <th class="request-content__column">申請日時</th>
            <th class="request-content__column">詳細</th>
        </tr>
        @foreach($userRequests as $userRequest)
            <tr class="request-content__row">
                @if($userRequest->status === 'pending')
                    <td class="request-content__record">承認待ち</td>
                @else
                    <td class="request-content__record">承認済み</td>
                @endif
                <td class="request-content__record">{{ $userRequest->user->name }}</td>
                <td class="request-content__record">{{ \Carbon\Carbon::parse($userRequest->date)->isoFormat('YYYY/MM/DD') }}</td>
                <td class="request-content__record">{{ $userRequest->description }}</td>
                <td class="request-content__record">{{ \Carbon\Carbon::parse($userRequest->created_at)->isoFormat('YYYY/MM/DD') }}</td>
                <td class="request-content__record">
                    @if(auth()->user()->role === 'admin')
                        <a class="request-content__link" href="{{ route('admin.showRequest', ['attendance_correct_request' => $userRequest->id]) }}">詳細</a>
                    @else
                        <a class="request-content__link" href="{{ route('user.show', ['attendance_id' => $userRequest->attendance_id]) }}">詳細</a>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection