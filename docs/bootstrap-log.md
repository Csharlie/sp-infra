# Spektra Infra — Bootstrap Log

Kronológikus napló: mi jött létre, mikor, miért.

---

## Jelenlegi állapot (Architecture Snapshot)

> Utolsó frissítés: scaffold (#2)

### Workspace struktúra

```
D:\Projects\spektra\sp-infra\          ← shared WP integration infra
├── plugin/                            ← spektra-api WP REST plugin (reusable core)
│   └── README.md
├── acf/                               ← Reusable ACF helpers
│   └── README.md
├── docker/                            ← Docker base config (WP + MariaDB + WP-CLI)
│   └── README.md
├── seed/                              ← Seed pipeline tools
│   └── README.md
├── scripts/                           ← Developer tooling (bootstrap, symlink, env)
│   └── README.md
├── apps/                              ← Runnable infra presets
│   └── README.md
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
| 2 | `f5b634d` | docs: add bootstrap-log — infra kronológikus napló |
| 3 | `6b8f9b4` | chore: scaffold sp-infra directory structure |

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

---

## #2 — Bootstrap-log (2026-04-04) · `f5b634d`

**Commit:** `docs: add bootstrap-log — infra kronológikus napló`

### Mi jött létre

```
docs/
└── bootstrap-log.md           ← kronológikus napló (ez a fájl)
```

### Státusz

✅ Pusholva.

---

## #3 — Directory scaffold (2026-04-05) · `6b8f9b4`

**Commit:** `chore: scaffold sp-infra directory structure`

### Mi jött létre

```
sp-infra/
├── plugin/README.md           ← spektra-api plugin — purpose, planned structure, rules
├── acf/README.md              ← reusable ACF helpers — planned helpers, rules
├── docker/README.md           ← Docker base config — status: prepared, not primary
├── seed/README.md             ← seed pipeline — pipeline diagram, planned files
├── scripts/README.md          ← dev tooling — planned scripts, rules
└── apps/README.md             ← runnable infra presets — naming rules
```

### Miért

- v4 roadmap P1.2: sp-infra alapstruktúra scaffold
- Minden mappában README — purpose + planned content + boundary rules
- Git requires files to track directories — README-k kettős célt szolgálnak

### Döntések

1. **7 top-level mappa**: plugin/, acf/, docker/, seed/, scripts/, apps/, docs/
2. **Minden README tartalmazza**: purpose, planned files, boundary rules
3. **Nincs még implementáció** — csak struktúra és dokumentáció
4. **docs/ már létezett** (#2-ből) — nem kap új README-t

### Státusz

✅ Pusholva.
