# Nat Wave

# Setup

See here https://github.com/beranidigital/berani-base-architect

# Cleanup command

```bash
php artisan app:monthly-cleanup --dry-run
```

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


