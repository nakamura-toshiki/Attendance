@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

@section('content')
<div class="mail">
    <div class="mail-content">
        <p class="mail-content__txt">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>
        <a class="mail-link" href="https://mailtrap.io/">認証はこちらから</a>
        <form class="mail-resend__form" method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="mail-resend__button">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection