# Nat Wave

## Installation Linux

1. `git clone --recursive https://github.com/Ticlext-Altihaf/nat-server`
2. `composer install`
3. config `.env`
4. `php artisan key:generate`

## CPanel Setup

1. `git clone --recursive https://github.com/Ticlext-Altihaf/nat-server`
2. Add `.htaccess` with permissions 644 to the root project folder
3. Upload `vendor` folder
4. Set `.env`
5. Set project folder permissions to 755

## Setup Device & Sensors

Setup on POST request

## Final Score Matrix

| PH\ORP | 🟩 | 🟨 | 🟥 |
|--------|----|----|----|
| 🟩     | 🟩 | 🟨 | 🟥 |
| 🟨     | 🟨 | 🟥 | 🟥 |
| 🟥     | 🟥 | 🟥 | 🟥 |

- 🟩 = 1 - 0.7
- 🟨 = 0.7 - 0.4
- 🟥 = 0.4 - 0

Mathematical formula:

```
orpScore * phScore
```


