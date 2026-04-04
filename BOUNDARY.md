# Spektra Boundary Rules

A v4 architektúra határ-szabályai. Minden contributor-nak ismernie kell.

---

## 1. Repository határok

| Repo | Tartalmaz | NEM tartalmaz |
|---|---|---|
| **sp-platform** | Típusok, adapterek, validáció, témák | WP/ACF kód, kliens logika |
| **sp-infra** | Plugin core, ACF helpers, Docker, seed, scripts | Kliens config, kliens field groups |
| **sp-benettcar** | Frontend, kliens mapping, kliens infra overlay | Platform-generikus kód, más kliensek kódja |
| **sp-docs** | Architektúra dokumentáció, integration plan | Futtatható kód |

## 2. Runtime szabály

A WordPress runtime **assembled + gitignored**.

```
.local/wp-runtimes/{client}/     ← GITIGNORED — soha nem commitolható
```

A runtime fájlok:
- WordPress core → letöltés (WP-CLI vagy kézi)
- Plugin → symlink `sp-infra/plugin/spektra-api/`
- Client config → symlink `sp-benettcar/infra/`
- ACF → WP plugin install
- Adatbázis → lokális MySQL/MariaDB (WAMP)

**Szabály**: Ha `git status` WP fájlokat mutat → valami rossz. Soha ne add hozzá.

## 3. Infra overlay szabály

A kliens infra overlay (`sp-benettcar/infra/`) **verziózott**, de **nem futtatható**.

| Tulajdonság | Érték |
|---|---|
| Hol él | `sp-benettcar/infra/` |
| Git-ben | ✅ Igen, a kliens repo-val commitolva |
| Futtatható | ❌ Nem — a runtime-ba kell linkelni |
| Nyelv | PHP (a WP plugin PHP-t vár) |
| Tartalom | Kliens config, kliens ACF field groups |

## 4. WP-ismeret határ

| Réteg | WP-ismeretet tartalmaz? | Fájl |
|---|---|---|
| Platform types | ❌ | `@spektra/types` |
| Platform adapters | 🟡 URL/endpoint config | `createWordPressAdapter` |
| Client wp-mapper.ts | ✅ WP response → SiteData | `wp-mapper.ts` |
| sp-infra plugin | ✅ WP REST, ACF, PHP | `plugin/spektra-api/` |
| Client infra overlay | ✅ Kliens ACF config | `infra/config.php`, `infra/acf/` |

**Szabály**: A platform (`@spektra/types`, `@spektra/data`) SOHA nem importál WP/ACF kódot.

## 5. Dependency flow

```
sp-platform  ← nem függ semmitől (core)
sp-infra     ← nem függ sp-platform-tól (PHP, independent)
sp-benettcar ← függ sp-platform-tól (@spektra/types, @spektra/data)
              ← NEM függ sp-infra-tól directly (runtime symlink-ek)
```

**Szabály**: `sp-benettcar/package.json` soha nem tartalmaz `sp-infra` dependency-t.

## 6. Ellenőrzés

Commit előtt ellenőrizd:

1. `git status` — nincs WP runtime fájl staged
2. `sp-infra/` — nincs kliens-specifikus kód (bc-*, client-* fájl)
3. `sp-benettcar/infra/` — nincs platform-generikus kód
4. `sp-platform/` — nincs WP/ACF import
