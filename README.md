# coachtechフリマ

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
