@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="auth-form">
    <h1 class="auth-form__heading">会員登録</h1>
    <form class="form-inner" action="/register" method="post">
        @csrf
        <div class="form-content">
            <label class="form-label" for="name">名前</label>
            <input class="form-input" type="text" name="name" id="name" value="{{ old('name') }}">
            <p class="error-message">
                @error('name')
                    {{ $message }}
                @enderror
            </p>
        </div>
        <div class="form-content">
            <label class="form-label" for="email">メ―ルアドレス</label>
            <input class="form-input" type="text" name="email" id="email" value="{{ old('email') }}">
            <p class="error-message">
                @error('email')
                    {{ $message }}
                @enderror
            </p>
        </div>
        <div class="form-content">
            <label class="form-label" for="password">パスワード</label>
            <input class="form-input" type="password" name="password" id="password">
            <p class="error-message">
                @error('password')
                    {{ $message }}
                @enderror
            </p>
        </div>
        <div class="form-content">
            <label class="form-label" for="password_confirmation">パスワード確認</label>
            <input class="form-input" type="password" name="password_confirmation" id="password_confirmation">
        </div>
        <input class="form-button" type="submit" value="登録する">
    </form>
    <a class="auth-link" href="/login">ログインはこちら</a>
</div>
@endsection