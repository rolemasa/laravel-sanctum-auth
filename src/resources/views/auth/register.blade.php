@extends('layouts.app')
@section('title', '新規登録')

@section('content')
    <h1>新規登録</h1>

    <form method="POST" action="{{ url('/register') }}">
        @csrf

        <label>名前
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>
        @error('name') <div class="error">{{ $message }}</div> @enderror

        <label>メールアドレス
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>
        @error('email') <div class="error">{{ $message }}</div> @enderror

        <label>パスワード（8文字以上）
            <input type="password" name="password" required>
        </label>
        @error('password') <div class="error">{{ $message }}</div> @enderror

        <label>パスワード（確認）
            <input type="password" name="password_confirmation" required>
        </label>

        <button type="submit">登録する</button>
    </form>

    <p class="sub"><a href="{{ route('login') }}">ログインはこちら</a></p>

@endsection