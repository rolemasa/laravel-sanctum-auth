# 仕様書：Laravel Sanctum ログイン認証機能

| 項目 | 内容 |
| --- | --- |
| 文書名 | ログイン認証機能 仕様書 |
| 版 | 1.0 |
| 対象システム | Laravel Sanctum ログイン認証サンプル |

## 1. 目的・スコープ

### 1.1 目的
Web 画面からのユーザー登録・ログイン・ログアウトを提供し、ログイン済みユーザーのみが閲覧できるページ、および Laravel Sanctum による API 認証（Cookie / トークン）を実装する。

### 1.2 スコープ内
- ユーザー登録、ログイン、ログアウト
- ログイン必須ページ（ダッシュボード）
- Sanctum による API 認証（Cookie 認証・Bearer トークン認証）
- ログイン試行回数の制限

### 1.3 スコープ外
- パスワードリセット、メールアドレス認証、多要素認証
- ソーシャルログイン、権限（ロール）管理
- 本番環境へのデプロイ

## 2. 認証方式

| 利用形態 | 方式 | 使用ガード |
| --- | --- | --- |
| Web 画面（Blade） | セッション認証 | `web` |
| 画面上の JavaScript からの API 呼び出し | Sanctum Cookie 認証（stateful） | `auth:sanctum`（`web` ガードを利用） |
| 外部クライアント（curl、モバイル等） | Sanctum API トークン（Bearer） | `auth:sanctum` |

## 3. 機能要件

| ID | 機能 | 内容 |
| --- | --- | --- |
| FR-01 | ユーザー登録 | 名前・メール・パスワードで登録。成功時は自動ログインしダッシュボードへ遷移 |
| FR-02 | ログイン | メール・パスワードで認証。成功時はセッションを再生成し、元のページまたはダッシュボードへ遷移 |
| FR-03 | Remember Me | チェック時、ログイン状態を長期間維持する |
| FR-04 | ログアウト | 認証解除、セッション無効化、CSRF トークン再生成の後、ログイン画面へ遷移 |
| FR-05 | アクセス制御 | 未ログインで `/dashboard` にアクセスするとログイン画面へリダイレクト。ログイン済みで `/login` `/register` にアクセスするとダッシュボードへリダイレクト |
| FR-06 | ユーザー情報 API | `GET /api/user` でログインユーザー情報を JSON で返す |
| FR-07 | トークン発行 API | メール・パスワードで認証し、Sanctum トークンを発行する |
| FR-08 | トークン失効 API | 使用中のトークンを削除する |
| FR-09 | ログイン試行制限 | `POST /login` と `POST /api/tokens` は 1 分間に 5 回まで |

## 4. 画面仕様

| 画面 | パス | 入力項目・表示内容 |
| --- | --- | --- |
| 登録 | `/register` | 名前、メールアドレス、パスワード、パスワード（確認）、登録ボタン、ログインへのリンク |
| ログイン | `/login` | メールアドレス、パスワード、ログイン状態を保持するチェック、ログインボタン、登録へのリンク |
| ダッシュボード | `/dashboard` | ユーザー名・メール、`/api/user` の取得結果（JSON）、ログアウトボタン |

エラーは入力欄の下に赤字で表示し、入力済みの値（パスワード以外）は保持する。

## 5. ルート仕様

### 5.1 Web（`routes/web.php`）

| メソッド | パス | ルート名 | ミドルウェア | コントローラ |
| --- | --- | --- | --- | --- |
| GET | `/` | - | - | ログイン画面へリダイレクト |
| GET | `/register` | `register` | guest | `RegisterController@create` |
| POST | `/register` | - | guest | `RegisterController@store` |
| GET | `/login` | `login` | guest | `LoginController@create` |
| POST | `/login` | - | guest, throttle:5,1 | `LoginController@store` |
| POST | `/logout` | `logout` | auth | `LogoutController@destroy` |
| GET | `/dashboard` | `dashboard` | auth | `DashboardController` |

### 5.2 API（`routes/api.php`、プレフィックス `/api`）

| メソッド | パス | ミドルウェア | 説明 |
| --- | --- | --- | --- |
| POST | `/api/tokens` | throttle:5,1 | トークン発行 |
| GET | `/api/user` | auth:sanctum | ユーザー情報取得 |
| DELETE | `/api/tokens` | auth:sanctum | トークン失効 |

## 6. API 仕様

すべてのリクエストに `Accept: application/json` を付与すること。

### 6.1 POST /api/tokens

リクエスト

```json
{ "email": "user@example.com", "password": "password123", "device_name": "curl" }
```

| フィールド | 必須 | 説明 |
| --- | --- | --- |
| email | 必須 | メールアドレス |
| password | 必須 | パスワード |
| device_name | 任意 | トークン名（未指定時は `api`） |

レスポンス

| ステータス | 内容 |
| --- | --- |
| 200 | `{ "token": "1\|xxxx...", "token_type": "Bearer" }` |
| 422 | 認証失敗・入力エラー（`errors.email` に理由） |
| 429 | 試行回数超過 |

### 6.2 GET /api/user

| ステータス | 内容 |
| --- | --- |
| 200 | ユーザー情報（id, name, email, created_at, updated_at など。パスワードは含まない） |
| 401 | 未認証 |

### 6.3 DELETE /api/tokens

| ステータス | 内容 |
| --- | --- |
| 200 | `{ "message": "ログアウトしました。" }` |
| 401 | 未認証 |

## 7. バリデーション

| 対象 | 項目 | ルール |
| --- | --- | --- |
| 登録 | name | required, string, max:255 |
| 登録 | email | required, email, max:255, unique:users |
| 登録 | password | required, confirmed, Password::defaults()（8 文字以上） |
| ログイン | email | required, email |
| ログイン | password | required |

## 8. データベース

| テーブル | 用途 | 備考 |
| --- | --- | --- |
| users | ユーザー | id, name, email(unique), password(hash), remember_token, timestamps |
| password_reset_tokens | パスワードリセット用 | Laravel 標準（本機能では未使用） |
| sessions | セッション保存 | `SESSION_DRIVER=database` |
| personal_access_tokens | Sanctum トークン | トークンは SHA-256 ハッシュで保存 |

## 9. セキュリティ要件

- パスワードは bcrypt でハッシュ化して保存する（`User` モデルの `hashed` キャスト）。
- ログイン成功時にセッション ID を再生成する（セッション固定化対策）。
- ログアウト時にセッションを無効化し、CSRF トークンを再生成する。
- すべての POST フォームに CSRF トークン（`@csrf`）を付与する。
- ログイン失敗時のメッセージは「メールまたはパスワードが違う」のみとし、どちらが誤りかを示さない。
- ログイン試行回数を制限する（FR-09）。
- `.env` をリポジトリに含めない。

## 10. 非機能要件

| 項目 | 内容 |
| --- | --- |
| 動作環境 | Docker Compose（PHP 8.3-fpm / Nginx / MySQL 8.4） |
| アクセス URL | `http://localhost:8080` |
| 言語 | 画面表示は日本語 |
| テスト | PHPUnit による機能テストを `tests/Feature/AuthTest.php` に用意する |

## 11. 構成

```
Browser ──HTTP──> Nginx(:8080) ──FastCGI──> PHP-FPM(Laravel) ──> MySQL
```

## 12. テスト観点

| No | 観点 | 期待結果 |
| --- | --- | --- |
| T-01 | 正しい入力で登録 | ダッシュボードへ遷移し認証済みになる |
| T-02 | 正しい資格情報でログイン | ダッシュボードへ遷移 |
| T-03 | 誤ったパスワードでログイン | 認証されずエラー表示 |
| T-04 | 未ログインでダッシュボード | ログイン画面へリダイレクト |
| T-05 | ログアウト | 未認証状態になる |
| T-06 | トークン発行 → `/api/user` | 200 とユーザー情報 |
| T-07 | 未認証で `/api/user` | 401 |
