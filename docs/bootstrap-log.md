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
| 11 | `036ecf4` | fix: response-builder placeholder shape meta -> site |
| 12 | `49d5a96` | feat: link-plugin.ps1 real impl + NAMESPACE fix (P4.3) |
| 13 | `1f9db68` | feat: link-overlay.ps1 real impl + target validation (P4.4) |
| 14 | `43c4456` | fix: config path __DIR__ -> WP_PLUGIN_DIR + ACF loader (P4.5) |

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

## #17 -- class-cors.php real impl (2026-04-05) · `26a3f21`

**Commit:** `feat: class-cors.php -- real CORS impl, origin whitelist, preflight 204, Vary header (P5.3)`

### Mi változott

1. **Origin whitelist a kliens configból:**
   - `SPEKTRA_CLIENT_CONFIG['allowed_origins']` tömb alapján dönt
   - Engedélyezett origin → `Access-Control-Allow-Origin: <origin>` + `Vary: Origin`
   - Nem engedélyezett → `header_remove()` — WP default CORS felülírása
   - Origin nélküli kérés → nem nyúlunk hozzá

2. **Preflight kezelés (OPTIONS):**
   - Engedélyezett origin + OPTIONS → 204 No Content
   - `Allow-Methods: GET, OPTIONS`
   - `Allow-Headers: Content-Type, Authorization`
   - `Access-Control-Max-Age: 86400` (24h preflight cache)

3. **Namespace szűrés:**
   - Csak `/spektra/` route-okra hat
   - Nem-spektra route-ok (pl. `/wp/v2/types`) → WP default CORS marad

4. **WP default CORS felülírás:**
   - WP `rest_send_cors_headers()` minden origin-re küld Allow-Origin headert
   - A mi filterünk priority 100-on fut (WP default: 10), tehát utána
   - Nem engedélyezett origin → `header_remove('Access-Control-Allow-Origin')`

### Smoke test eredmények

| Eset | Státusz | Allow-Origin | Vary |
|---|---|---|---|
| Allowed origin GET | 200 | `http://localhost:5174` | `Origin` |
| Disallowed origin GET | 200 | NOT SET | `Origin` |
| No origin GET | 200 | NOT SET | — |
| Preflight allowed | 204 | `http://localhost:5174` | — |
| Preflight disallowed | 200 | NOT SET | — |
| Non-spektra route | 200 | `http://evil.com` (WP default) | — |

### Státusz

✅ Pusholva.

---

## #19 -- Response Builder skeleton (2026-04-05) · `fa463f7`

**Commit:** `feat: response-builder skeleton -- SiteData contract shape (P7.1)`

### Mi változott

**`class-response-builder.php` — teljes átírás (skeleton):**

| Elem | Leírás |
|---|---|
| `$is_preview` property | `build()` elmenti, P7.2–P7.3 használni fogja |
| `build()` | Delegál `build_site_meta()`, `build_navigation()`, `build_pages()`-nak |
| `build_site_meta()` | Skeleton: `{ name: "" }` — P7.2 tölti fel |
| `build_navigation()` | Skeleton: `{ primary: [] }` — P7.2 tölti fel |
| `build_pages()` | Skeleton: `[ build_page('home') ]` — P7.3 bővíti |
| `build_page($slug)` | Skeleton: `{ slug, sections: [] }` — P7.3 tölti fel |
| `get_front_page_id()` | `(int) get_option('page_on_front', 0)` — kész |

### P7.1 guardrails

- ❌ Nincs ACF olvasás
- ❌ Nincs config betöltés
- ❌ Nincs helper require
- ❌ Nincs section assembly
- ❌ Nincs media normalization

### Endpoint output

```json
{
  "site": { "name": "" },
  "navigation": { "primary": [] },
  "pages": [{ "slug": "home", "sections": [] }]
}
```

### Teszteredmények

22/22 PASS — top-level keys, SiteMeta shape, Navigation shape, Page shape, preview mode, JSON encode.

### Státusz

✅ Pusholva.

---

## #19 -- Response Builder site meta + navigation (2026-04-05) · `0edd00f`

**Commit:** `feat: response builder site meta + navigation assembly (P7.1+P7.2)`

### Mi változott

**`class-response-builder.php` — P7.1 skeleton + P7.2 valós assembly:**

1. **`load_config()`** — Config betöltés `build()` elején (stateless)
   - `WP_PLUGIN_DIR . '/spektra-config/config.php'`
   - Defensive: `file_exists()` + `is_array()` check

2. **`build_site_meta( array $config )`** — Valós SiteMeta assembly
   - Precedence: config `site_defaults.title` → `get_bloginfo('name')` → `''`
   - `description`: `get_bloginfo('description')`
   - `url`: `home_url('/')`
   - `locale`: `get_locale()` → BCP 47 normalizálás

3. **`normalize_locale( string $locale )`** — WP `hu_HU` → BCP 47 `hu-HU`

4. **`build_navigation( array $config )`** — Config-driven curated nav
   - Forrás: `$config['navigation']['primary']`
   - Nem ACF, nem WP menu, nem sections auto-gen
   - Items normalizálva `normalize_nav_item()`-tel

5. **`normalize_nav_item( array $item )`** — NavItem contract normalization
   - `label`, `href` kötelező
   - `external` opcionális (csak ha true)
   - `children` rekurzív (jövőbiztos)

6. **`build_pages()`, `build_page()`, `get_front_page_id()`** — P7.1 skeleton marad
   - `pages[0].sections = []` — P7.3-ban töltjük

### Config változás (sp-benettcar `e4aa10f`)

`config.php` bővítve `navigation` blokkal:
- 4 curated NavItem: Főoldal, Szolgáltatások, Rólunk, Kapcsolat
- Átmeneti megoldás — Phase 11.5: native WP menu integration

### Teszteredmények

| Teszt | Eredmény |
|---|---|
| PHP lint (response-builder + config) | ✅ |
| Top-level: site, navigation, pages (3 key) | ✅ |
| site.name = "Benett Car" | ✅ |
| site.url tartalmazza "benettcar" | ✅ |
| site.locale BCP 47 (hyphen, no underscore) | ✅ |
| navigation.primary: 4 item | ✅ |
| nav[0] = Főoldal / "/" | ✅ |
| nav[3] = Kapcsolat / "/#contact" | ✅ |
| Minden NavItem: label + href | ✅ |
| pages[0].slug = "home" | ✅ |
| pages[0].sections = [] | ✅ |
| Nincs top-level sections key | ✅ |
| **30/30 PASS** | ✅ |

### Architekturális döntések

- Config loading `build()`-ben, nem constructor-ban — stateless builder
- Navigation: config-driven curated list, nem auto-generated sections-ből
- Locale: WP-ből (`get_locale()`), BCP 47-re normalizálva
- Future: WP native menu integration → Phase 11.5

---

## #20 -- Section assembly (2026-04-05) · `6c1d84d`

**Commit:** `P7.3: section assembly — config-driven loop + spektra_get_section_data()`

### Mi változott

**`acf/sections.php` — ÚJ fájl: section data dispatcher + 10 bc-* builder:**

1. **`spektra_get_section_data( $type, $post_id )`** — Központi dispatcher
   - Prefix levezetés: `bc-hero` → `bc_hero_` (ACF naming convention)
   - `match` expression: 10 bc-* slug → per-section builder
   - Ismeretlen slug → `null` (section kihagyva)

2. **Per-section builder funkciók** (10 db):
   - `spektra_build_bc_hero` — title, subtitle, description, backgroundImage, primaryCta, secondaryCta
   - `spektra_build_bc_brand` — title, description, brands[] (name, logo, alt, invert)
   - `spektra_build_bc_gallery` — title, subtitle, showCategories, images[] (src, alt, category, caption)
   - `spektra_build_bc_services` — title, subtitle, services[] (title, icon, description)
   - `spektra_build_bc_service` — title, subtitle, description, services[], brands[], contact group
   - `spektra_build_bc_about` — title, subtitle, content[], image, imagePosition, colorScheme, stats[], cta
   - `spektra_build_bc_team` — title, subtitle, description, members[] (name, role, image, phone, email)
   - `spektra_build_bc_assistance` — title, subtitle, description, serviceArea, requestCta
   - `spektra_build_bc_contact` — title, subtitle, description, info group, colorScheme
   - `spektra_build_bc_map` — title, query, height

3. **Null-safety szabályok:**
   - Required field `null` → teljes section `null` (kihagyva)
   - Optional field `null` → default érték (`''`, `[]`, `null`)
   - Image mezők: raw ACF array (P7.4 normalizál)
   - CTA-k: csak ha legalább text vagy href létezik

**`class-response-builder.php` — Section assembly integráció:**

1. **`build()` → `build_pages( $config )`** — config átadás
2. **`build_page( $slug, $config )`** — config átadás build_sections-nek
3. **`build_sections( array $config )`** — ÚJ metódus
   - Forrás: `$config['sections']` (config.php-ből, NINCS hardkódolt lista)
   - `get_front_page_id()` → post_id; ha 0 → üres sections
   - Loop: slug → `\spektra_get_section_data()` → null check → Section shape
   - Section shape: `{ id: slug, type: slug, data: array }`

**`spektra-api.php` — ACF data layer betöltés:**

- `require_once` a 3 acf/ fájlra: helpers.php, media-helper.php, sections.php
- Betöltési sorrend: helpers → media-helper → sections (függőségi lánc)
- Útvonal: `dirname( __FILE__, 3 ) . '/acf'`

### Teszteredmények

| Teszt | Eredmény |
|---|---|
| PHP lint (3 fájl) | ✅ |
| Top-level struktúra (site, navigation, pages) | ✅ |
| sections count = 10 | ✅ |
| Minden section: id = type = slug | ✅ |
| bc-hero: title, description, primaryCta | ✅ |
| bc-brand: brands[] count >= 1 | ✅ |
| bc-gallery: title, images[] | ✅ |
| bc-services: services[] count >= 1 | ✅ |
| bc-service: services[], brands[], contact | ✅ |
| bc-about: content[], imagePosition, stats[] | ✅ |
| bc-team: members[] count >= 1 | ✅ |
| bc-assistance: title, requestCta | ✅ |
| bc-contact: title, info group | ✅ |
| bc-map: query, height (int) | ✅ |
| **60/60 PASS** | ✅ |

### Architekturális döntések

- Section lista `$config['sections']` — NINCS hardkódolt lista a builderben
- Section-specifikus ACF olvasás `acf/sections.php`-ban — NEM response-builder.php-ban
- Image: raw ACF array marad — P7.4 normalizálja
- Output kulcsok camelCase (TypeScript platform contract)
- bc-* specifikus kód sp-infra-ban — pragmatikus, P11.3 technical debt

### Státusz

✅ Pusholva. sp-infra `0edd00f`, sp-benettcar `e4aa10f`.

---

## #21 -- Contract alignment fix (2026-04-05) · `31a6df3`

**Commit:** `fix(P7.3.1): contract alignment + README + defensive guard`

### Mi változott

**`acf/sections.php` — Frontend site.ts contract igazítás (4 drift fix):**

1. **bc-hero CTA keys** — `primaryCta` / `secondaryCta` → `primaryCTA` / `secondaryCTA`
   - site.ts `primaryCTA` formát használ, a backend eltért
2. **bc-service brands** — `[{ name: string }]` → `string[]`
   - site.ts flat string tömböt vár, nem objektumokat
3. **bc-assistance CTA** — `requestCta: { text, href }` → `requestLabel` + `requestHref`
   - site.ts flat mezőket használ, nem CTA objektumot
4. **bc-contact info** — `info: {}` → `contactInfo: {}`
   - site.ts `contactInfo` kulcsnevet használ

**`README.md` — Repo önleírás pontosítás:**

- "NO client-specific code" kijelentés kiegészítve
- Elismeri `acf/sections.php` bc-* tartalmat mint P11.3 technical debt

**`class-response-builder.php` — Defensive guard:**

- `$data === null` → `! is_array( $data )` a `build_sections()` loopban
- Védelem jövőbeli hibás return típus ellen

### Teszteredmények

| Teszt | Eredmény |
|---|---|
| PHP lint (2 fájl) | ✅ |
| Contract-aligned runtime teszt | 64/64 PASS ✅ |
| bc-hero: primaryCTA / secondaryCTA | ✅ |
| bc-service: brands[0] is string | ✅ |
| bc-assistance: requestLabel / requestHref | ✅ |
| bc-contact: contactInfo / contactInfo.phone | ✅ |

---

## #24 -- Full SiteData endpoint validation (2026-04-06) · `8e87043`

**Phase:** P7.5 — validation only, no code changes

### Validation method

1. **p75-dump.php** — PHP CLI, `Response_Builder->build()` → `p75-sitedata.json` (11 KB, 1 page, 10 sections)
2. **p75-validate.mjs** — Node.js, imports `validateSiteData()` from `sp-platform/packages/data/dist/validate.js`

### Layer 1 — Platform `validateSiteData()`

| Check | Result |
|---|---|
| `validateSiteData(siteData)` | ✅ `valid: true` |

Validated: site meta, navigation (primary NavItem[]), pages (min 1), sections (id+type+data), optional meta/ogImage.

### Layer 2 — Consumer compatibility

| Check | Result |
|---|---|
| bc-hero.backgroundImage → Media\|null | ✅ |
| bc-about.image → Media\|null | ✅ |
| bc-team.members[*].image → Media\|null | ✅ (3 members) |
| bc-brand.brands[*].logo → string | ✅ (3 brands, P7.4.1) |
| bc-gallery.images[*].src → string\|null | ✅ (2 images) |
| Null policy (optional fields) | ✅ |
| Section integrity (id+type+data, 10 sections) | ✅ |

### Eredmény

| | |
|---|---|
| **Összesített** | **45/45 PASS** |
| **P7.5** | **PASS** |

---

## #23 -- bc-brand.logo rollback to URL string (2026-04-06) · `fef8893`

**Commit:** `fix(acf): rollback bc-brand.logo to URL string (P7.4.1)`

### Mi változott

**`acf/sections.php` — bc-brand.brands[].logo visszaállítva URL stringre:**

- P7.4 `spektra_normalize_media()` → Media objektumot adott vissza
- Frontend `bc-brand.schema.ts` `logo?: string`-et vár, component `<img src={brand.logo}>` -ként használja
- Media objektum → `[object Object]` lenne img src-ben → **contract drift**
- Megoldás: ACF image array-ből URL string kinyerése inline (`$logo['url']`)
- Azonos pattern mint bc-gallery: flat string amíg P8 mapper nem létezik

### Érintett mezők — végső állapot P7.4.1 után

| Mező | Kimenet | Miért |
|---|---|---|
| bc-hero.backgroundImage | `Media\|null` | ✅ frontend schema `Media`-t vár |
| bc-about.image | `Media\|null` | ✅ frontend schema `Media`-t vár |
| bc-team.members[].image | `Media\|null` | ✅ frontend schema `Media`-t vár |
| bc-brand.brands[].logo | `string` | ⚠️ frontend schema `string`-et vár — P8-ra normalizálódik |
| bc-gallery.images[].src | `string` | ⚠️ frontend schema `string`-et vár — P8-ra normalizálódik |

### Teszteredmények

| Teszt | Eredmény |
|---|---|
| PHP lint | ✅ |
| hero.backgroundImage is Media\|null | ✅ |
| brand.brands[*].logo is string | ✅ |
| about.image is Media\|null | ✅ |
| team.members[*].image is Media\|null | ✅ |
| gallery.images[*].src NOT normalized | ✅ |
| **11/11 PASS** | ✅ |

---

## #22 -- Media normalization (2026-04-06) · `486a436`

**Commit:** `feat(P7.4): media normalization — ACF image → canonical Media shape`

### Mi változott

**`acf/sections.php` — 4 image mező normalizálva `spektra_normalize_media()` hívással:**

1. **bc-hero.backgroundImage** — `spektra_get_field(…)` → `spektra_normalize_media( spektra_get_field(…) )`
2. **bc-brand.brands[].logo** — `$row['logo'] ?? null` → `spektra_normalize_media( $row['logo'] ?? null )` *(P7.4.1-ben visszaállítva URL stringre)*
3. **bc-about.image** — `spektra_get_field(…)` → `spektra_normalize_media( spektra_get_field(…) )`
4. **bc-team.members[].image** — `$row['image'] ?? null` → `spektra_normalize_media( $row['image'] ?? null )`

**bc-gallery.images[].src — SZÁNDÉKOSAN nem módosítva:**
- Frontend jelenleg `string`-et vár (`src: "https://..."`)
- Normalizálás `Media` objektumot adna → contract drift
- P8 mapper scope-ba tartozik

**Header frissítés:**
- Depends on: `+ media-helper.php (spektra_normalize_media)`
- Phase history: `+ P7.4: media normalization`

### Normalizálási szabályok

| Input | Output |
|---|---|
| Valid ACF image array | `{ src, alt, width, height, variants, mimeType }` |
| URL string | `{ src, alt: '', width: null, height: null, variants: [], mimeType: null }` |
| null / empty | `null` |

### Teszteredmények

| Teszt | Eredmény |
|---|---|
| PHP lint | ✅ |
| hero.backgroundImage is Media\|null | ✅ |
| brand.brands[*].logo is Media\|null | ✅ *(P7.4.1-ben string-re rollback)* |
| about.image is Media\|null | ✅ |
| team.members[*].image is Media\|null | ✅ |
| gallery.images[*].src NOT normalized | ✅ |
| **11/11 PASS** | ✅ |

---

## #18 -- ACF helpers finalize + media-helper.php (2026-04-05) · `ed775ee`

**Commit:** `feat: ACF helpers finalize + media-helper.php (P6.1)`

### Mi változott

1. **`spektra_get_field()` — új függvény (helpers.php):**
   - Safe wrapper `get_field()` köré
   - `$default` értéket ad vissza ha a mező üres/hiányzik (nem `false`-t)
   - Ha ACF nincs betöltve → `$default` (nem fatal error)

2. **`spektra_acf_sizes_to_variants()` — valódi implementáció (helpers.php):**
   - ACF sizes tömb iterálása: `thumbnail`, `medium`, `large`, stb.
   - Width/height párosítás: `$key . '-width'` / `$key . '-height'` konvenció
   - Output: `MediaVariant[]` — `{ name, source: { url, width, height } }`

3. **`spektra_acf_group_to_cta()` — CTA field name fix (helpers.php):**
   - Régi: `label` / `url` / `target` → nem egyezett a platform type-pal
   - Új: `text` / `href` — illeszkedik a `CallToAction { text: string, href: string }` típushoz (P8-R4: href kötelező)
   - `target` eltávolítva (nincs a platform contract-ban)

4. **`spektra_normalize_media()` — új fájl (media-helper.php):**
   - Univerzális media normalizer
   - ACF image array → `Media` shape (delegál `image_to_media`-nak)
   - URL string → minimális `Media` shape (`src` only)
   - `null` / `false` / `''` → `null`

5. **README.md frissítve:**
   - Teljes függvény inventory
   - Platform shape referencia

### Teszteredmények

| Teszt | Eredmény |
|---|---|
| 5/5 function_exists (standalone) | ✅ |
| 5/5 function_exists (WP runtime) | ✅ |
| sizes_to_variants: 3 variáns (thumb/medium/large) | ✅ |
| CTA: text/href (nem label/url) | ✅ |
| normalize_media(array) → Media | ✅ |
| normalize_media(string) → Media | ✅ |
| normalize_media(null) → null | ✅ |
| normalize_media('') → null | ✅ |
| get_field(no ACF) → fallback | ✅ |
| get_field(missing, WP) → default | ✅ |

### Boundary döntés

- `spektra_get_section_data()` **nem** került P6.1-be — az section assembly logika, P7.3 scope
- P6 = helper layer (nyers ACF → platform shape)
- P7 = assembly layer (section → SiteData struktúra)

### Státusz

✅ Pusholva.

---

## #17 -- class-cors.php real impl (2026-04-05) · `26a3f21`

**Commit:** `feat: rest-controller -- schema, validate preview, version+cache headers (P5.2)`

### Mi változott

1. **Preview param schema + validáció:**
   - `args` tömb hozzáadva a `register_rest_route`-hoz
   - `validate_callback`: csak `'true'` elfogadott, minden más → 400 + `WP_Error`
   - `sanitize_callback`: `sanitize_text_field` (WP beépített)
   - Hiányzó param = normál mód (nincs validáció hiba)

2. **Response headerek:**
   - `X-Spektra-Version: 0.1.0` — minden válaszban
   - `Cache-Control: no-cache` — csak preview módban
   - Publikus mód: nincs cache policy (nem P5.2 scope)

3. **Phase 5.2 placeholder kommentek eltávolítva**

### Smoke test eredmények

| Kérés | Státusz | X-Spektra-Version | Cache-Control |
|---|---|---|---|
| `GET /site` | 200 | `0.1.0` | — |
| `GET /site?preview=true` | 200 | `0.1.0` | `no-cache` |
| `GET /site?preview=yes` | 400 | — | — |

400 body: `{"code":"rest_invalid_param","message":"Invalid parameter(s): preview",...}`

### Státusz

✅ Pusholva.

---

## #15 -- spektra-api.php finalize (2026-04-05) · `9b0321f`

**Commit:** `refactor: spektra-api.php finalize -- add API_URL, clean Phase 5 comments (P5.1)`

### Mi változott

1. **SPEKTRA_API_URL konstans hozzáadva:**
   - `define( 'SPEKTRA_API_URL', plugin_dir_url( __FILE__ ) );`
   - Szükséges asset URL-ekhez — Phase 5+ lépések használják

2. **Phase 5 placeholder kommentek eltávolítva:**
   - Hook registration szekcióból kikerült a "Implementation: Phase 5." megjegyzés
   - A hook-ok már implementálva vannak — komment elavult volt

### Runtime hatás

- Endpoint: 200 OK, `debug.log` üres — nincs regresszió
- SPEKTRA_API_URL elérhető a plugin kódban

### Státusz

✅ Pusholva.

---

## #14 -- WP_PLUGIN_DIR fix + ACF field group loader (2026-04-05) . `43c4456`

**Commit:** `fix: config path __DIR__ -> WP_PLUGIN_DIR + add ACF field group loader (P4.5)`

### Mi valtozott

1. **spektra-api.php config path fix:**
   - `__DIR__ . '/../spektra-config/config.php'` --> `WP_PLUGIN_DIR . '/spektra-config/config.php'`
   - Ok: `__DIR__` Junction-on at a valos forras utvonalra old fel (`sp-infra/plugin/spektra-api/`),
     nem a `wp-content/plugins/` konyvtarra. Igy a `../spektra-config/` soha nem talalt.
   - `WP_PLUGIN_DIR` a tenyleges plugins mappat adja, ahol a `spektra-config` Junction el.

2. **ACF field group loader hozzaadva:**
   - `dirname($spektra_config_path) . '/acf/field-groups.php'` -- require_once
   - A `field-groups.php` sajat `acf/init` hook-ban regisztral, tehat sima require eleg
   - Ha a fajl nem letezik (mas kliens overlay), skip -- nem crashel

### Runtime hatas

- Config betoltodes: `config.php` most tenyleg betoltodik (korabban csendben `[]` volt)
- 10 ACF field group regisztralva: BC Hero, BC Brand, BC Gallery, BC Services, BC Service,
  BC About, BC Team, BC Assistance, BC Contact, BC Map (osszesen 51 mezo)
- Endpoint: 200 OK, nincs fatal error, `debug.log` ures

### Bug jelleg

Ez egy **P4.4-bol oroklott latens bug** volt: a `spektra-config` Junction letrejott,
de az `__DIR__` feloldas miatt a plugin soha nem talalta meg. A 200 OK valasz elfeddte,
mert a placeholder response nem fugg a configtol.

### Statusz

Pusholva.

---

## #13 -- link-overlay.ps1 real impl (2026-04-05) . `1f9db68`

**Commit:** `feat: link-overlay.ps1 real impl -- Junction + target validation (P4.4)`

### Mi valtozott

- **scripts/link-overlay.ps1** -- scaffold lecserelve valos implementaciora:
  - `New-Item -ItemType Junction` -- kliens overlay (`sp-benettcar/infra/`) belinkelese `spektra-config/` neven
  - Target validation: ha junction mar letezik, ellenorzi, hogy a helyes source-ra mutat-e
  - Ha rossz target-re mutat: error + manualis torlest ker (nem csereli csendben)
  - Ha helyes target: skip (idempotens)
  - Source/plugins dir guard: ha nem letezik, error

### Runtime hatas

- Junction letrehozva: `.local/wp-runtimes/benettcar/wp-content/plugins/spektra-config` -> `sp-clients/sp-benettcar/infra`
- `spektra-api.php` fallback path (`__DIR__ . '/../spektra-config/config.php'`) most talal fajlt
- Plugin aktiv maradt, nem crashelt
- Endpoint: `http://benettcar.local/wp-json/spektra/v1/site` -> 200 OK (placeholder shape)
- `debug.log` nem jott letre (nincs error)

### Architektura megjegyzes

Ez a symlink fallback a **jelenlegi runtime stratégia**. Kesobb a `bootstrap.ps1` ugyanezt
a logikát hivja scriptbol, a `wp-config.php.tpl` + ENV-driven path pedig a vegleges modell.
A script ugy epult, hogy abba a flow-ba illeszkedjen.

### Statusz

Pusholva.

---

## #12 -- link-plugin.ps1 + NAMESPACE fix (2026-04-05) . `49d5a96`

**Commit:** `feat: link-plugin.ps1 real impl + fix NAMESPACE reserved keyword in rest-controller (P4.3)`

### Mi valtozott

1. **scripts/link-plugin.ps1** -- scaffold lecserelve valos implementaciora:
   - `New-Item -ItemType Junction` -- nem SymbolicLink (admin jog nem kell)
   - Idempotens: `Test-Path` guard (ha mar letezik, kilepk)
   - Source/target validacio: `Test-Path $PluginSource`, `Test-Path $PluginsDir`
   - Parancs: `& .\scripts\link-plugin.ps1 -Client benettcar`

2. **class-rest-controller.php** -- `const NAMESPACE` atnevezve `const API_NAMESPACE`-re:
   - `namespace` PHP reserved keyword -- PHP 8-ban fatal error
   - WP error handler elnyelte, a plugin csendben nem regisztralodott
   - `self::NAMESPACE` -> `self::API_NAMESPACE` mindenhol

### Runtime hatas

- Junction letrehozva: `.local/wp-runtimes/benettcar/wp-content/plugins/spektra-api` -> `sp-infra/plugin/spektra-api`
- Plugin aktivalva (MySQL active_plugins update)
- Spektra endpoint mukodik: `http://benettcar.local/wp-json/spektra/v1/site` -> 200 OK
- Response: `{"site":[],"navigation":[],"pages":[]}` (helyes placeholder shape)

### Statusz

Pusholva.

---

## #11 -- Response-builder contract fix (2026-04-05) . `036ecf4`

**Commit:** `fix: response-builder placeholder shape meta -> site -- match SiteData contract`

### Mi valtozott

- `class-response-builder.php`: placeholder return shape `'meta' => []` atirva `'site' => []`-ra
- A platform SiteData contract `site / navigation / pages` shape-et var, nem `meta`
- 1 soros fix, nincs funkcionalis valtozas (a builder meg placeholder)

### Miert most

- User review eszrevette a contract mismatch-et
- Barmely downstream kod ami a placeholder-re nez, mar a helyes shape-et latja
- Phase 7-ben a valos implementacio mar a helyes shape-bol indul

### Statusz

:white_check_mark: Pusholva.

---

## #10 -- Config loader return-array pattern (2026-04-05) . `36626be`

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
