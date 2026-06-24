## Summary

-

## Scope

- [ ] Code change
- [ ] Documentation change
- [ ] Configuration change
- [ ] Database migration
- [ ] Public API or route change
- [ ] No runtime behavior change

## Open-Source Safety

- [ ] No secrets, credentials, private URLs, or local `.env` files are included.
- [ ] No production/member/client data, uploaded files, private branding, or business documents are included.
- [ ] New sample data uses fake values and reserved domains such as `example.test`.
- [ ] New dependencies or bundled assets include license/provenance notes where needed.
- [ ] Browser/dev-test artifacts, screenshots, traces, videos, reports, and dumps are not included.

## Deployment Notes

- Required env changes:
- Migration/backfill steps:
- Rollback notes:

## Validation

- [ ] `composer validate --no-check-publish`
- [ ] `npm run build`
- [ ] `php artisan test`
- [ ] `./vendor/bin/pint`
- [ ] `bash scripts/validate-no-generated-artifacts.sh --all`

Commands actually run:

```text

```
