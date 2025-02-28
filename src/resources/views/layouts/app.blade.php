<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>attendance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz@0,14..32;1,14..32&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <div class="app">
        <div class="header">
            <div class="header-inner">
                <div class="header-logo">
                    <img class="logo-img" src="{{ asset('storage/images/logo.svg') }}" alt="ロゴ">
                </div>
                @if(auth('admin')->user())
                    <div class="header-link__admin">
                        <a class="header-link__button" href="{{ route('admin.list') }}">勤怠一覧</a>
                        <a class="header-link__button" href="{{ route('admin.staff') }}">スタッフ一覧</a>
                        <a class="header-link__button" href="{{ route('user.request') }}">申請一覧</a>
                    </div>
                @else
                    <div class="header-link">
                        <a class="header-link__button" href="{{ route('user.attendance')}}">勤怠</a>
                        <a class="header-link__button" href="{{ route('user.list') }}">勤怠一覧</a>
                        <a class="header-link__button" href="{{ route('user.request') }}">申請</a>
                    </div>
                @endif
                <form class="logout-form" action="/logout" method="post">
                    @csrf
                    <input class="logout-button" type="submit" value="ログアウト">
                </form>
            </div>
        </div>
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>
</html>