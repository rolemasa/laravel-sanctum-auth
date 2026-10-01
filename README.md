# Laravel Sanctum ログイン認証サンプル（Docker 対応）

Laravel と Laravel Sanctum を使った、Web 画面からのログイン認証サンプルです。
開発環境はすべて Docker 上に構築するため、PC に PHP / Composer / MySQL をインストールする必要はありません。

## 主な機能

- ユーザー登録 / ログイン / ログアウト（Blade 画面）
- ログイン状態の維持（Remember Me）
- ログイン必須ページ（ダッシュボード）
- Sanctum の Cookie 認証による API 呼び出し（`GET /api/user`）
- Sanctum の API トークン発行・失効（`POST /api/tokens`, `DELETE /api/tokens`）
- 機能テスト（PHPUnit）

詳細は [docs/SPECIFICATION.md](docs/SPECIFICATION.md) を参照してください。

## 技術スタック

| 区分 | 内容 |
| --- | --- |
| フレームワーク | Laravel（11 以降の構成） |
| 認証 | Laravel 標準セッション認証 + Laravel Sanctum |
| 言語 | PHP 8.3 |
| Web サーバー | Nginx 1.27 |
| DB | MySQL 8.4 |
| 実行環境 | Docker / Docker Compose |

## 必要なもの

- Docker Desktop（Docker Compose v2 が使えること）
- Git

## フォルダ構成

```
.
├── docker-compose.yml
├── .env.example            # Docker 用の環境変数のひな形
├── docker/
│   ├── nginx/default.conf
│   └── php/Dockerfile
├── docs/
│   └── SPECIFICATION.md    # 仕様書
└── src/                    # Laravel 本体
    ├── app/Http/Controllers/
    ├── resources/views/
    ├── routes/
    └── tests/
```

## セットアップ

### 1. リポジトリを取得

```bash
git clone https://github.com/<your-name>/<your-repo>.git
cd <your-repo>
```

### 2. Docker 用の環境変数を作成

```bash
cp .env.example .env
```

Linux / WSL の場合は、ファイルの権限エラーを防ぐため `.env` の `APP_UID` / `APP_GID` を `id -u` / `id -g` の結果に合わせてください。

### 3. コンテナをビルドして起動

```bash
docker compose up -d --build
```

### 4. Laravel の準備

```bash
docker compose exec app composer install
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
```

`src/.env` を開き、DB 設定を次のようにします。

```
APP_URL=http://localhost:8080
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
SANCTUM_STATEFUL_DOMAINS=localhost:8080,127.0.0.1:8080
```

その後、テーブルを作成します。

```bash
docker compose exec app php artisan migrate
```

### 5. 動作確認

ブラウザで <http://localhost:8080> を開くとログイン画面が表示されます。
「新規登録」からユーザーを作成してください。

## よく使うコマンド

| 目的 | コマンド |
| --- | --- |
| 起動 | `docker compose up -d` |
| 停止 | `docker compose down` |
| DB ごと削除 | `docker compose down -v` |
| artisan 実行 | `docker compose exec app php artisan <command>` |
| テスト | `docker compose exec app php artisan test` |
| コンテナに入る | `docker compose exec app bash` |
| ログ確認 | `docker compose logs -f web app` |

## 画面・エンドポイント

| メソッド | パス | 認証 | 説明 |
| --- | --- | --- | --- |
| GET | `/register` | 不要 | 登録画面 |
| POST | `/register` | 不要 | ユーザー登録 |
| GET | `/login` | 不要 | ログイン画面 |
| POST | `/login` | 不要 | ログイン |
| POST | `/logout` | 必要 | ログアウト |
| GET | `/dashboard` | 必要 | ダッシュボード |
| GET | `/api/user` | 必要（Cookie or Bearer） | ログインユーザー情報 |
| POST | `/api/tokens` | 不要 | API トークン発行 |
| DELETE | `/api/tokens` | 必要 | API トークン失効 |

### API トークンの使い方

```bash
# トークン発行
curl -X POST http://localhost:8080/api/tokens \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123","device_name":"curl"}'

# トークンを使って呼び出し
curl http://localhost:8080/api/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <発行されたトークン>"
```

## トラブルシューティング

| 症状 | 対処 |
| --- | --- |
| `Permission denied`（storage など） | `docker compose exec app chmod -R ug+rwx storage bootstrap/cache` |
| DB に接続できない | `src/.env` の `DB_HOST=db` を確認。`docker compose ps` で `db` が healthy か確認 |
| 419 Page Expired | フォームに `@csrf` があるか、`APP_URL` とアクセス URL が一致しているか確認 |
| ポート 8080 が使用中 | `docker-compose.yml` の `ports` を変更（例: `"8081:80"`） |
| `/api/user` が 401 | ログイン後に同じホスト（`localhost:8080`）でアクセスしているか、`SANCTUM_STATEFUL_DOMAINS` を確認 |

## セキュリティ上の注意

- `.env`（ルート・`src/`）は絶対にコミットしないでください。
- 本リポジトリの DB パスワードは開発用です。本番では必ず変更してください。
- 本番では `APP_DEBUG=false`、HTTPS の利用、`SESSION_SECURE_COOKIE=true` を設定してください。

## ライセンス

MIT License
