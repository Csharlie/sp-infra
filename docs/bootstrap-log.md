# Spektra Infra — Bootstrap Log

Kronológikus napló: mi jött létre, mikor, miért.

---

## Jelenlegi állapot (Architecture Snapshot)

> Utolsó frissítés: seed + scripts scaffold (#8)

### Workspace struktúra

```
D:\Projects\spektra\sp-infra\          ← shared WP integration infra
├── plugin/                            ← spektra-api WP REST plugin (reusable core)
│   ├── README.md
│   └── spektra-api/
│       ├── spektra-api.php            ← plugin header, autoload, config loading
│       └── includes/
│           ├── class-rest-controller.php  ← REST route registration
│           ├── class-response-builder.php ← SiteData JSON assembly
│           └── class-cors.php             ← CORS + preflight
├── acf/                               ← Reusable ACF helpers
│   ├── README.md
│   └── helpers.php                    ← image_to_media, sizes_to_variants, group_to_cta
├── docker/                            ← Docker base config (WP + MariaDB + WP-CLI)
│   ├── README.md
│   ├── docker-compose.yml             ← 3 service: wordpress, mariadb, wpcli
│   └── .env.example                   ← credentials template
├── seed/                              ← Seed pipeline tools
│   ├── README.md
│   ├── export-seed.ts                 ← site.ts → seed.json (scaffold — Phase 10.4)
│   └── package.json                   ← @spektra/seed (tsx dep)
├── scripts/                           ← Developer tooling (bootstrap, symlink, env)
│   ├── README.md
│   ├── bootstrap.ps1                  ← Full local setup (scaffold — Phase 4)
│   ├── link-plugin.ps1                ← Plugin symlink (scaffold — Phase 4.3)
│   ├── link-overlay.ps1               ← Overlay symlink (scaffold — Phase 4.4)
│   └── setup-env.ps1                  ← Env loader (scaffold — Phase 5.4)
├── apps/                              ← Runnable infra presets
│   └── README.md
├── docs/                              ← Infrastructure documentation
│   └── bootstrap-log.md               ← ez a fájl
├── .gitignore                         ← runtime + environment + IDE ignores
├── BOUNDARY.md                        ← v4 határ-szabályok dokumentáció
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
| 3 | `7b1da56` | chore: scaffold sp-infra directory structure |
| 4 | `75c7cb7` | chore: add .gitignore + BOUNDARY.md — runtime boundary rules |
| — | `613cb9c` | docs: fix bootstrap-log hash for #4 (meta) |
| — | `4fbb897` | docs: fix bootstrap-log — correct #3 hash, fix entry order (meta) |
| 5 | `e5948e0` | feat: add spektra-api plugin base skeleton (P2.1) |
| — | `b2b1169` | docs: fix bootstrap-log hash for #5 (meta) |
| 6 | `8fe6f34` | feat: add acf/helpers.php scaffold (P2.2) |
| — | `da12b43` | docs: fix bootstrap-log hash for #6 (meta) |
| — | `22fdf97` | fix: align sizes_to_variants contract with platform MediaVariant shape |
| 7 | `bd96c76` | feat: add docker/ base config (P2.3) |
| — | `496e307` | docs: fix bootstrap-log hash for #7 (meta) |
| 8 | `e77e7b6` | feat: add seed/ + scripts/ scaffolds (P2.4) |
| — | `2652b0b` | fix: bootstrap-log #8 hash correction (meta) |
| 9 | `ff77870` | fix: replace em-dash with ASCII in scripts (P2.5) |
| — | `91e1644` | fix: bootstrap-log #9 hash correction (meta) |
| 10 | `36626be` | refactor: config loader return-array pattern (P3.1) |

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

## #3 — Directory scaffold (2026-04-05) · `7b1da56`

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

---

## #4 — Boundary rules (2026-04-05) · `75c7cb7`

**Commit:** `chore: add .gitignore + BOUNDARY.md — runtime boundary rules`

### Mi jött létre

```
sp-infra/
├── .gitignore             ← .local/, node_modules, vendor, .env, IDE ignores
└── BOUNDARY.md            ← v4 határ-szabályok (repo határok, runtime szabály,
                              overlay szabály, WP-ismeret határ, dependency flow)
```

Változás más repo-ban:
```
sp-benettcar/.gitignore    ← .local/, .env, .env.local hozzáadva
```

### Miért

- v4 roadmap P1.4: .gitignore + boundary rules
- `.local/` gitignored — assembled WP runtime soha nem commitolható
- BOUNDARY.md — határ-szabályok egy helyen, contributor reference

### Döntések

1. **sp-infra .gitignore**: .local/, node_modules/, vendor/, .env, IDE fájlok
2. **sp-benettcar .gitignore bővítve**: .local/, .env, .env.local, .env.*.local
3. **BOUNDARY.md 6 szekció**: repo határok, runtime szabály, overlay szabály, WP boundary, dependency flow, ellenőrzés
4. **.local/ workspace root szinten** — nem egyetlen repo-ban, de minden .gitignore-ban benne van safety-ből

### Státusz

✅ Pusholva.

---

## #5 — Plugin base skeleton (2026-04-05) · `e5948e0`

**Commit:** `feat: add spektra-api plugin base skeleton (P2.1)`

### Mi jött létre

```
plugin/spektra-api/
├── spektra-api.php                    ← plugin header, constants, autoload, config loading, hook registration
└── includes/
    ├── class-rest-controller.php      ← GET /wp-json/spektra/v1/site route skeleton
    ├── class-response-builder.php     ← SiteData JSON assembly skeleton (build() → meta/navigation/pages)
    └── class-cors.php                 ← CORS + preflight skeleton (rest_pre_serve_request filter)
```

### Miért

- v4 roadmap P2.1: plugin/ base skeleton
- A struktúra a v4 Section 6.1 tervét követi
- Skeleton = hook registration + class structure, NEM implementáció (Phase 5 + 7)

### Döntések

1. **Config loading**: Strategy B (ENV var `SPEKTRA_CLIENT_CONFIG` + symlink fallback `../spektra-config/config.php`)
2. **Namespace**: `Spektra\API` — minden class ebben él
3. **REST route**: `spektra/v1/site`, GET, publikus (permission_callback = `__return_true`)
4. **Preview support**: `?preview=true` param kész, auth Phase 5-ben
5. **CORS**: `rest_pre_serve_request` filter — origins a kliens config-ból (Phase 5.3)
6. **Response builder**: `build(bool $is_preview)` → üres `meta/navigation/pages` tömb (Phase 7 tölti)
7. **Nincs class-config-loader.php** — a config loading a main plugin fájlban él (v4 terv szerint)

### Státusz

✅ Pusholva.

---

## #6 — ACF helpers scaffold (2026-04-05) · `8fe6f34`

**Commit:** `feat: add acf/helpers.php scaffold — image_to_media, sizes_to_variants, group_to_cta (P2.2)`

### Mi jött létre

```
acf/
├── README.md     ← frissítve: Structure tábla, helper függvény státuszok
└── helpers.php   ← 3 helper függvény scaffold (Phase 6.1 tölti ki)
```

### Miért

- v4 roadmap P2.2: acf/ reusable helpers scaffold
- A helpers.php az egyetlen shared ACF utility fájl (Section 16.2 alapján)
- Kétszintű ACF modell: helpers = sp-infra (shared), field-groups = client overlay

### Döntések

1. **Egy fájl** (`helpers.php`) — nem szétbontva (`media-helper.php` + `helpers.php`) — egyszerűbb, a Section 16.2 is `acf/helpers.php`-t hivatkozza
2. **3 függvény stub**: `spektra_acf_image_to_media`, `spektra_acf_sizes_to_variants`, `spektra_acf_group_to_cta`
3. **Partial implementation**: `image_to_media` és `group_to_cta` üres-guard logika kész, `sizes_to_variants` üres tömböt ad (Phase 6.1-ben iteráció)
4. **Nincs `acf_add_local_field_group`** — field regisztráció CSAK a kliens overlay-ban (`sp-benettcar/infra/acf/`)

### Státusz

✅ Pusholva.

---

## #7 — Docker base config (2026-04-05) · `bd96c76`

**Commit:** `feat: add docker/ base config — docker-compose.yml + .env.example (P2.3)`

### Mi jött létre

```
docker/
├── docker-compose.yml     ← 3 service (wordpress, mariadb, wpcli) + 3 named volume
├── .env.example           ← WP_PORT, MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD, MYSQL_ROOT_PASSWORD
└── README.md              ← frissítve: Structure, Services tábla, Usage
```

### Miért

- v4 roadmap P2.3: docker/ base config
- v4 Section 8.5: Docker = előkészített, nem elsődleges (WAMP primary — DR-004)
- Docker kész CI/CD vagy WAMP-mentes gépeknél

### Döntések

1. **v4 Section 8.5 tervezett yaml 1:1 átvéve** — wordpress:6.7-php8.3-apache, mariadb:11.4, wordpress:cli-php8.3
2. **Plugin bind mount** (`../plugin/spektra-api` → container plugins/) — nem symlink Dockerben
3. **Seed mount**: `../seed:/seed` a wpcli service-ben (Phase 10.4-ben használja)
4. **wpcli profiles: ["cli"]** — nem indul automatikusan `docker compose up`-pal
5. **Healthcheck**: mariadb healthcheck.sh — wordpress service vár rá (`service_healthy`)
6. **.env.example**: jelszó mezők üresen — a contributor tölti ki

### Státusz

✅ Pusholva.

---

## #8 — Seed + scripts scaffold (2026-04-05) · `e77e7b6`

**Commit:** `feat: add seed/ + scripts/ scaffolds — export-seed.ts, 4 PowerShell scripts (P2.4)`

### Mi jött létre

```
seed/
├── export-seed.ts         ← site.ts → seed.json converter skeleton (Phase 10.4)
├── package.json           ← @spektra/seed, tsx dependency
└── README.md              ← frissítve: Structure

scripts/
├── bootstrap.ps1          ← Full local setup — WP runtime + symlinks + env (Phase 4)
├── link-plugin.ps1        ← Symlink sp-infra/plugin/spektra-api → runtime (Phase 4.3)
├── link-overlay.ps1       ← Symlink client infra/ → runtime plugins/spektra-config (Phase 4.4)
├── setup-env.ps1          ← .env fájl betöltés, SPEKTRA_CLIENT_CONFIG beállítás (Phase 5.4)
└── README.md              ← frissítve: Structure
```

### Miért

- v4 roadmap P2.4: seed/ + scripts/ + docs/ scaffold
- Minden script paraméterezett: `-Client` param (default: benettcar)
- Scaffold = help text + path logic kész, tényleges műveletek Phase 4/5/10-ben

### Döntések

1. **4 PowerShell script** a v4 scripts/README tervei alapján — mindegyik `-Client` paraméterrel
2. **export-seed.ts** scaffold — `npx tsx` alapú, process.exit(1) amíg nem implementált
3. **seed/package.json** — `@spektra/seed` csomag, `tsx` dependency a TypeScript futtatáshoz
4. **Minden script idempotent tervvel** — Test-Path guard-ok, re-run safe
5. **Workspace-relatív path pattern**: `$PSScriptRoot\..\..\` → workspace root

### Státusz

✅ Pusholva.

---

## #9 — PowerShell encoding fix (2026-04-05) · `ff77870`

**Commit:** `fix: replace em-dash with ASCII in scripts — PowerShell 5.1 encoding compat (P2.5)`

### Mi változott

- 4 `.ps1` fájlban az em-dash (`—`, U+2014) karakterek ASCII `--` -re cserélve
- PowerShell 5.1 alapértelmezetten ANSI kódolást vár, a UTF-8 em-dash eltörte a parsingot
- A `-ForegroundColor` param és a következő sorok literal textként jelentek meg

### Miért

- P2.5 siker-kritérium: "Script-ek futtathatók (még üres logikával)"
- Mind a 4 script tesztelve: `bootstrap.ps1`, `link-plugin.ps1`, `link-overlay.ps1`, `setup-env.ps1`
- Mindegyik hibátlanul fut és WARNING-gal jelzi a scaffold státuszt

### Státusz

✅ Pusholva.

---

## #10 — Config loader return-array pattern (2026-04-05) · `36626be`

**Commit:** `refactor: config loader uses return-array pattern + SPEKTRA_CLIENT_CONFIG constant (P3.1)`

### Mi változott

- `spektra-api.php`: config loading átírva `require_once` → `$config = require`
- A betöltött config.php-nak `return []` -t kell adnia (nem globális változókat)
- `is_array()` guard: ha a config nem tömböt ad vissza, üres tömbbel inicializál
- `SPEKTRA_CLIENT_CONFIG` konstans: a plugin többi része ebből olvassa a config-ot

### Cross-repo: sp-benettcar `79c7c37`

- `infra/config.php` átírva placeholder → valós `return []` konfiguráció
- Kulcsok: `client_slug`, `client_name`, `allowed_origins` (`http://localhost:5174`), `site_defaults` (lang, title), `sections` (10 db bc-* slug)

### Döntések

1. **Return-array pattern** a constants/globals helyett — tisztább, tesztelhetőbb
2. **SPEKTRA_CLIENT_CONFIG** = define() konstans — a CORS, Response_Builder, stb. innen olvassa
3. **Port 5174** az allowed_origins-ben — sp-benettcar vite.config.ts explicit port override

### Státusz

✅ Pusholva.
