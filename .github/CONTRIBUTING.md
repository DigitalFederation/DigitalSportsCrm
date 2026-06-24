# Contributing

Thank you for your interest in contributing to Digital Sports CRM.

This project is maintained as open-source software for self-hosted federation management. Contributions are welcome, but there is no guaranteed response time, support commitment, roadmap commitment, or service-level agreement.

## Before You Start

- Read `README.md`, `AGENTS.md`, `SECURITY.md`, and `SUPPORT.md`.
- Open an issue before large changes, schema changes, public API changes, or breaking behavior changes.
- Keep private deployment details out of issues, commits, pull requests, screenshots, fixtures, and documentation.

## Development Setup

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

## Pull Request Expectations

- Use focused commits and clear PR descriptions.
- Include migration notes for database or operational changes.
- Include environment variable changes when configuration is added or changed.
- Include tests for behavior changes.
- Run formatting and relevant checks before submitting.
- Do not include generated browser test artifacts, logs, dumps, uploads, private branding, or screenshots containing private data.

## Coding Guidelines

- Follow existing Laravel, domain action, request validation, policy, enum, and service patterns.
- Avoid hardcoded business strings when an enum, config value, or translation key is appropriate.
- Prefer backwards-compatible changes for existing deployments.
- Use fake test data and reserved domains such as `example.test`.

## License Of Contributions

Unless explicitly stated otherwise, contributions submitted to this project are licensed under the Apache License 2.0.
