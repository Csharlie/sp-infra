# scripts/

Developer tooling for Spektra infrastructure.

## Purpose

PowerShell scripts for local development setup, symlink management, and environment configuration.

## Structure

```
scripts/
├── bootstrap.ps1      # Full local setup (scaffold — Phase 4)
├── link-plugin.ps1    # Symlink plugin → WP runtime (scaffold — Phase 4.3)
├── link-overlay.ps1   # Symlink client overlay → WP runtime (scaffold — Phase 4.4)
├── setup-env.ps1      # Environment loader (scaffold — Phase 5.4)
└── README.md
```

## Rules

- Windows PowerShell primary (WAMP environment)
- Scripts must be idempotent (safe to re-run)
