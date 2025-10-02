<p align="center"><a href="https://laravel.com" target="_blank"></a> Shipment Scheduled Command




## Services
- Nginx
- Docker
- Mysql
- PHP(8.4)
- PhpMyAdmin
- Composer
- Laravel 12

## Clone Project
```sh
- First of All Clone Project From bottom url : 
  https://github.com/abbassmortazavi/scheduled-command.git
After clone, in root project in command line run this command before migrate : 
  cp .env.example .env
```



## Installation
```sh
- Install All Container Run These Command : 
  docker compose up -d

- Down All Container Use this Command :
  docker compose down
```

## Composer update or Install
```sh
docker exec -it php bash
 composer i
 composer u
```

## Run Migration And Seed
```sh
docker exec -it php bash
   php artisan migrate
   php artisan db:seed
   php artisan schedule:work
   if you want run command directly : php artisan shipments:update-statuses
```
