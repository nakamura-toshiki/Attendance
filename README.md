# coachtech 勤怠管理アプリ

## 環境構築
### Dockerビルド
1. git clone git@github.com:nakamura-toshiki/fleamarket.git  
2. docker-compose up -d --build
### Laravel環境構築
1. docker-compose exec php bash  
2. composer install  
3. cp .env.example .env,環境変数を変更  
``` text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
4. php artisan key:generate  
5. php artisan migrate  
6. php artisan db:seed
### メール認証
mailtrapを使用
1. 以下のリンクから会員登録　
   https://mailtrap.io/
2. メールボックスのIntegrationsから 「laravel 7.x and 8.x」を選択　
3. .envファイルのMAIL_MAILERからMAIL_ENCRYPTIONまでの項目をコピー＆ペースト　
4. MAIL_FROM_ADDRESSに任意のメールアドレスを設定
## URL
・開発環境：http://localhost/  
・phpMyAdmin:：http://localhost:8080/
## 使用技術
・php 7.4.9  
・Laravel 8  
・mysql 8.0.26  
・nginx 1.21.1
