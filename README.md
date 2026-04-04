# sp-infra

Spektra shared infrastructure — reusable WordPress integration tools.

## Purpose

Shared, client-agnostic infrastructure for Spektra WordPress integrations:

- **plugin/** — `spektra-api` WP REST plugin (reusable core)
- **acf/** — Reusable ACF helper functions (image→Media, CTA, repeater)
- **docker/** — Docker base configuration (WP + MariaDB + WP-CLI)
- **seed/** — Seed pipeline tools (site.ts → seed.json → WP-CLI)
- **scripts/** — Developer tooling (bootstrap, symlink, env loader)
- **apps/** — Runnable infra presets (WAMP config, etc.)
- **docs/** — Infrastructure documentation

## Architecture

```
sp-infra/           ← THIS REPO (shared, reusable)
sp-platform/        ← Platform core (types, adapters, validation)
sp-benettcar/       ← Client repo (frontend + infra overlay)
```

**Key rule**: This repo contains NO client-specific code. Client configuration lives in each client's `infra/` overlay directory.

## Related

- [sp-platform](https://github.com/Csharlie/spektra) — Platform core
- [sp-benettcar](https://github.com/Csharlie/sp-benettcar) — Benettcar client
- [sp-docs](https://github.com/Csharlie/sp-docs) — Documentation
