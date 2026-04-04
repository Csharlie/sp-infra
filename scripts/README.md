# scripts/

Developer tooling for Spektra infrastructure.

## Purpose

PowerShell scripts for local development setup, symlink management, and environment configuration.

## Planned scripts

- `bootstrap.ps1` — Full local setup (WP runtime, symlinks, env)
- `link-plugin.ps1` — Symlink plugin → WP runtime
- `link-overlay.ps1` — Symlink client infra overlay → WP runtime
- `setup-env.ps1` — Environment variable loader

## Rules

- Windows PowerShell primary (WAMP environment)
- Scripts must be idempotent (safe to re-run)
