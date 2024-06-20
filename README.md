## Product Import Test
Please Follow the guideline to set up locally.

- Laravel 11 (php 8.3/8.2)

### Installation process after cloning from git

1. composer install
2. cp .env.example .env
3. php artisan key:generate
4. set database mysql and update related things in .env (for example your database name, password)
5. php artisan migrate
6. In storage/app directory create a folder csv and put stock.csv file (storage/app/csv/stock.csv)
7. Run this command to store data from csv
`php artisan import:products /csv/stock.csv`
8. Run this command if you don't want to store data in database
`php artisan import:products /csv/stock.csv --test`
9. For unit test run this command
`php artisan test --filter ProductImportServiceTest`
