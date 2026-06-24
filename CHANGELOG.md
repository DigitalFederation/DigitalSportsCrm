# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] — Initial open-source release

First public release of Digital Sports CRM, a self-hosted Laravel 11 + Vite platform for
federation management. It provides operational workflows for:

- Members, entities, and federations, with role- and permission-based access control.
- Certifications and licenses — purchase, attribution, and validation.
- Committees defined entirely in configuration (`config/committees.php`), so each deployment
  models its own areas of activity — including the license-purchase, licenses-attributed, and
  sidebar-menu wiring — without code changes.
- Events, event applications, and enrollment.
- Documents, payments (with an optional EasyPay gateway), and invoicing.
- Public directories and a configurable public map.
- Deployment-agnostic branding via `config/branding.php`.

See the documentation (`docs/`) for installation, configuration, and architecture.
