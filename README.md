# Ceylon Ads Laravel Revamp

This directory contains the new Laravel + Blade implementation of Ceylon Ads.
It replaces the old split architecture (`system-api` + React + Angular) with a single Laravel monolith.

## Migrated in this pass

- Blade web UI for public pages:
  - `/`
  - `/ads/create`
  - `/ads/{id}`
  - `/about`
  - `/privacy-policy`
  - `/terms-and-conditions`
- JSON API under `/api/v1`
  - `POST /auth/login`
  - `POST /auth/verify-otp`
  - `GET /categories`
  - `GET /cities`
  - `GET /advertisements`
  - `POST /advertisements`
  - `GET /advertisements/{id}`
- MySQL migrations/models for:
  - `application_users`
  - `categories`
  - `countries`, `districts`, `cities`
  - `advertise_types`
  - `general_advertisements`
  - `otp_codes`

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Environment alignment

`.env` and `.env.example` were aligned with the old Spring app defaults:

- `DB_DATABASE=ceylon_adds_db`
- `DB_USERNAME=root`
- `DB_PASSWORD=1234`
- `AWS_DEFAULT_REGION=ap-south-1`
- `AWS_BUCKET=ceylon-ad-bucket`
- `OTP_BYPASS=true`

Set real credentials for Twilio/AWS before production use.

## Next recommended migration slices

1. Port Angular admin dashboards into Blade admin modules.
2. Move upload/image workflows to Laravel storage + S3.
3. Replace demo OTP token behavior with Sanctum/JWT-grade auth.
# laravel-api
