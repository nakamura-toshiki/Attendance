@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

@section('content')
<div class="mail">
    <div class="mail-header">
        <p class="mail-header__txt">認証用のメールを送信しました</p>
    </div>

    <div class="mail-content">
        @if (session('resent'))
        <p class="mail-content__txt" role="alert">
            新規認証メールを再送信しました！
        </p>
        @endif
        <p class="mail-resend__txt">
            このページを閲覧するには、Eメールによる認証が必要です。<br>
            もし認証用のメールを受け取っていない場合、
            <form class="mail-resend__form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="mail-resend__button">こちらのリンク</button>をクリックして、認証メールを受け取ってください。
            </form>
        </p>
    </div>
</div>
@endsection