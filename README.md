## Requirements

-   Make sure that you have composer installed in your machine ([check this link](https://getcomposer.org/download/))
-   Make sure that you have already attached a ssh-key to your Github account, [check this link for more details](https://docs.github.com/en/github/authenticating-to-github/connecting-to-github-with-ssh)
-   Make sure you are inside ~/majormedia/dev folder

### 1. Getting started

```sh
git clone git@github.com:majormedia360/vacaloc-api.git
cd vacaloc-api
```

Create an auth.json file with your OctoberCMS credentials:

```sh
{
    "http-basic": {
        "gateway.octobercms.com": {
            "username": "your-username",
            "password": "your-api-token"
        }
    }
}
```

Install dependencies and set up environment variables:

```sh
composer install
copy .env.example .env
php artisan key:generate
```

### 2. Clone dependencies

```sh
cd plugins/majormedia/toolbox/ && composer install && cd ../../..

```

### 3. Database

```ini
Update your .env file with the necessary database connection information. Create a database with the collation utf8mb4_general_ci and update the following variables in the .env file:
```

```sh
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

Create Database with collation `utf8mb4_general_ci` & change database connexion info on .env

### 4. Install October app

Follow all steps

```sql
php artisan october:migrate
php artisan october:update
php artisan october:migrate
```

## Backend Access

```sh
http://localhost/vacaloc-api/admin
login: admin
password: admin

```

## Troubleshooting

.. todo
