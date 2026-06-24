#!/usr/bin/env bash
set -euo pipefail

mode="${1:---staged}"

case "$mode" in
    --staged)
        paths="$(git diff --cached --name-only --diff-filter=ACMR)"
        ;;
    --all)
        paths="$(git ls-files)"
        ;;
    *)
        echo "Usage: $0 [--staged|--all]" >&2
        exit 2
        ;;
esac

blocked_regex='(^|/)(\.playwright-mcp|playwright-mcp|playwright-report|test-results|blob-report|verification-screenshots|coverage|\.nyc_output)(/|$)|(^|/)\.cursor(/|$)|(^|/)\.mcp\.json$|(^|/)postman(/|$)|(^|/)tests/(e2e|playwright)(/|$)|(^|/)playwright\.config\.[^/]+$|(^|/)cypress/(screenshots|videos)(/|$)|(^|/)docs/\.vitepress/(cache|dist)(/|$)|(^|/)public/build(/|$)|(^|/)public/docs(/|$)|(^|/)public/assets/icons(/|$)|(^|/)public/(vendor/)?tinymce(/|$)|(^|/)resources/vendor/tinymce(/|$)|(^|/)public/media$|(^|/)public/(img/)?private-branding(/|$)|(^|/)public/img/(admin-flags|cards|event-hero)(/|$)|(^|/)public/img/bg_diving\.(mp4|webm)$|(^|/)public/img/(private|deployment|client)(-[^/]+)?-logo\.(png|jpg|jpeg|svg)$|(^|/)storage/(clockwork|debugbar)(/|$)|(^|/)storage/fonts/.+|(^|/)storage/logs/.*\.log$|(^|/)temp(/|$)|(^|/)business(/|$)|(^|/)database(\.sqlite|/.*\.sqlite[0-9]?)$|(^|/)permissions-audit-report\.md$|(^|/)\.env(\.playwright(\.example)?|\.testing|\.production|\.backup|\.local)?$|(^|/)auth\.json$|(^|/)localhost(-key)?\.pem$|(^|/).*\.(pem|p12|pfx|bak|backup|old|orig|tmp|dump|sql)$|(^|/)trace\.zip$|(^|/).*\.trace\.zip$|(^|/)error-context\.md$'

blocked_files="$(
    printf '%s\n' "$paths" |
        grep -E "$blocked_regex" |
        # Allow files that match a blocked pattern but are intentionally tracked:
        # the squashed Laravel schema dump, and the storage .gitignore placeholders.
        grep -Ev '(^|/)storage/(clockwork|debugbar|fonts|logs)/\.gitignore$|(^|/)database/schema/mysql-schema\.sql$' || true
)"

if [[ -n "$blocked_files" ]]; then
    cat >&2 <<'MSG'
Blocked: generated test/dev artifacts or local secret files were found.

These paths are intentionally excluded because they can contain screenshots,
videos, traces, credentials, exports, PDFs, logs, or real personal data.

Blocked paths:
MSG
    printf '%s\n' "$blocked_files" >&2
    exit 1
fi
