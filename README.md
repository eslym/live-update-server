# Live Update Server

A simple server for serving zip bundles for `@capawesome/live-update`, a self-hosted alternative
to [Capawesome Cloud](https://cloud.capawesome.io/).

## Requirements

| Name                  | Version  |
| --------------------- | -------- |
| PHP                   | >= 8.3   |
| Composer              | >= 2.1   |
| [Bun](https://bun.sh) | >= 1.2.0 |

## Installation

```bash
git clone https://github/eslym/live-update-server.git live-update-server
cd live-update-server
composer install --no-dev
bun install
bun run build
cp .env.example .env
php artisan key:generate

# Edit .env for your configuration

php artisan migrate
php artisan optimize # Optional but recommended
```

Navigate to `https://your-domain.com/setup` to set up account.

## Development

```bash
composer install
bun run dev
php artisan serve
```
