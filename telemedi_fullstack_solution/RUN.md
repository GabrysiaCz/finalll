# Telemedi – Recruitment Task (Fullstack)

This fork contains a working mini‑app: Symfony PHP API + React frontend.

## How to run (recommended: Docker + WSL)

```bash
# In repo root
docker compose up --build
# app runs at http://localhost:80
```

If ports are busy, change the mapping in `docker-compose.yml`.

## Without Docker (WSL)

You need PHP 8.2, Composer 2, Node 18:
```bash
composer install
npm ci
npm run build
php -S 0.0.0.0:8000 -t public
# open http://localhost:8000
```

## API

- `GET /api/currencies` – list supported currencies.
- `GET /api/rates?date=YYYY-MM-DD` – computed buy/sell/mid for supported currencies on chosen date (or latest when date omitted).
- `GET /api/rates/{code}/history?date=YYYY-MM-DD` – last 14 NBP business days up to the given date.

Rules implemented:
- EUR, USD: buy = mid − 0.15 PLN, sell = mid + 0.11 PLN
- CZK, IDR, BRL: buy = null, sell = mid + 0.20 PLN

NBP source: `https://api.nbp.pl/`
