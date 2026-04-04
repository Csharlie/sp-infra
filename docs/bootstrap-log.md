# Spektra Infra — Bootstrap Log

Kronológikus napló: mi jött létre, mikor, miért.

---

## Jelenlegi állapot (Architecture Snapshot)

> Utolsó frissítés: init (#1)

### Workspace struktúra

```
D:\Projects\spektra\sp-infra\          ← shared WP integration infra
├── plugin/                            ← spektra-api WP REST plugin (reusable core)
├── acf/                               ← Reusable ACF helpers
├── docker/                            ← Docker base config (WP + MariaDB + WP-CLI)
├── seed/                              ← Seed pipeline tools
├── scripts/                           ← Developer tooling (bootstrap, symlink, env)
├── apps/                              ← Runnable infra presets
├── docs/                              ← Infrastructure documentation
│   └── bootstrap-log.md               ← ez a fájl
└── README.md
```

### Repository határ

| Repo | Tartalom | WP-ismeret |
|---|---|---|
| **sp-platform** | Típusok, adapterek, validáció | ❌ WP-agnosztikus |
| **sp-infra** | Plugin, ACF helpers, Docker, seed, scripts | ✅ WP-specifikus (reusable) |
| **sp-benettcar** | Frontend + infra overlay | 🟡 Adapter-en keresztül |
| **sp-docs** | Dokumentáció | — |

### Dependency flow

```
sp-platform  ← nem függ semmitől (core)
sp-infra     ← nem függ sp-platform-tól (PHP, independent)
sp-benettcar ← függ sp-platform-tól (@spektra/types, @spektra/data)
              ← NEM függ sp-infra-tól (runtime-ban a WP biztosítja az adatot)
```

### Commit history

| # | Hash | Leírás |
|---|------|--------|
| 1 | `75c7783` | init: sp-infra repository — shared Spektra WP infrastructure |

---

## #1 — Repository init (2026-04-04) · `75c7783`

**Commit:** `init: sp-infra repository — shared Spektra WP infrastructure`

### Mi jött létre

```
sp-infra/
└── README.md                  ← repo leírás, purpose, architecture, related repos
```

### Miért

- v4 architektúra döntés (DR-005): reusable infra = shared repo, kliens config = kliens repo
- sp-infra létrehozása az első implementációs lépés a v4 roadmap szerint (P1.1)

### Döntések

1. **Repo neve**: `sp-infra` — a `sp-` prefix konzisztens az összes Spektra repo-val
2. **Remote**: `https://github.com/Csharlie/sp-infra.git`
3. **Branch**: `main`
4. **Tartalom**: README.md — purpose, architecture, related repos

### Státusz

✅ Local repo kész, GitHub remote konfigurálva, push sikeres.
