@extends('layouts.app')
@section('title', 'ログイン')

@section('content')
    <h1>ログイン</h1>

    <form method="POST" action="{{ url('/login') }}">
        @csrf

        <label>メールアドレス
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </label>
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <label>パスワード
            <input type="password" name="password" required>
        </label>
        @error('password') <div class="error">{{ $message }}</div> @enderror

        <label>
            <input type="checkbox" name="remember" value="1"> ログイン状態を保持する
        </label>

        <button type="submit">ログイン</button>
    </form>

    <p class="sub"><a href="{{ route('register') }}">新規登録はこちら</a></p>
@endsection