@extends('layouts.app')
@section('title', 'ダッシュボード')

@section('content')
    <h1>ダッシュボード</h1>
    <p>ようこそ、<strong>{{ auth()->user()->name }}</strong> さん<br>
       <small>{{ auth()->user()->email }}</small></p>

    <p>Sanctum（Cookie認証）で <code>/api/user</code> を呼んだ結果：</p>
    <pre id="api-result">読み込み中...</pre>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout">ログアウト</button>
    </form>

    <script>
        fetch('/api/user', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
            .then(res => res.json())
            .then(data => {
                document.getElementById('api-result').textContent = JSON.stringify(data, null, 2);
            })
            .catch(() => {
                document.getElementById('api-result').textContent = '取得に失敗しました';
            });
    </script>
@endsection