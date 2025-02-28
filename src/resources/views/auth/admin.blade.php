@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="auth-form">
    <h1 class="auth-form__heading">管理者ログイン</h1>
    <form class="form-inner" action="{{ route('admin.login') }}" method="post">
        @csrf
        <div class="form-content">
            <label class="form-label" for="email">メ―ルアドレス</label>
            <input class="form-input" type="text" name="email" value="{{ old('email') }}">
            <p class="error-message">
                @error('email')
                    {{ $message }}
                @enderror
            </p>
            @if ($errors->has('login_error') && $errors->count() === 1)
                <p class="error-message">{{ $errors->first('login_error') }}</p>
            @endif
        </div>
        <div class="form-content">
            <label class="form-label" for="email">パスワード</label>
            <input class="form-input" type="password" name="password">
            <p class="error-message">
                @error('password')
                    {{ $message }}
                @enderror
            </p>
        </div>
        <input class="form-button" type="submit" value="登録する">
    </form>
</div>
@endsection