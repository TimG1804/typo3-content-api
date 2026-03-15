Das sind erste Ideen für ein **konkretes Architekturkonzept für eine TYPO3 Headless API Extension**, die TYPO3 **nicht als Renderer**, sondern als **Content Repository** nutzt.

Das Konzept ist bewusst so gestaltet, dass es:

* **ohne TypoScript** funktioniert
* **klare API-Contracts** hat
* **versionierbar** ist
* **testbar** ist
* **frontend-freundlich** ist (Next.js / Nuxt / Apps)

---

# Architekturkonzept

## TYPO3 Headless API Extension

Arbeitstitel:

**EXT:content-api**

---

# 1 Zielbild

TYPO3 bleibt:

* Content Repository
* Backend Editing
* Media Management
* Workspaces
* Localization

Die Extension stellt bereit:

* **REST API Layer**
* **Content Normalization**
* **DTO Serialization**
* **API Versioning**
* **Preview System**

TYPO3 rendert **kein HTML und kein JSON mehr**.

Nur:

```
TYPO3 → Content DB
API Extension → JSON API
Frontend → Rendering
```

---

# 2 Architekturübersicht

```
Frontend (Next.js / React / mobile Apps / etc.)
           │
           │ REST API
           ▼
   TYPO3 API Extension
   -------------------------
   API Controllers
   Query Services
   Domain Mappers
   DTO Layer
   Serializer
   Cache
   Auth / Preview
           │
           ▼
        TYPO3 Core
 pages / tt_content / sys_file
 site / language / routing
```

---

# 3 Hauptprinzipien

## 1 API-first

Die API ist **das Produkt**, nicht das TYPO3 Rendering.

---

## 2 stabile Contracts

Beispiel:

```
GET /api/v1/pages/home
```

Response:

```json
{
  "meta": {
    "apiVersion": "1.0",
    "language": "en"
  },
  "page": {
    "id": 12,
    "slug": "home",
    "title": "Homepage"
  },
  "content": [
    {
      "type": "text",
      "headline": "Welcome",
      "text": "Lorem ipsum"
    }
  ]
}
```

Frontend darf sich **darauf verlassen**.

---

## 3 keine TypoScript Magie

Mapping erfolgt über:

```
PHP Normalizer
DTO Klassen
Serializer
```

---

# 4 API Layer

Die API basiert auf **PSR-15 Routing**.

TYPO3 unterstützt:

```
routeEnhancers
PSR-15 middleware
```

Wir definieren eigene Routen.

Beispiel:

```
/api/v1/pages/{slug}
/api/v1/navigation/{site}
/api/v1/media/{id}
/api/v1/settings/{site}
```

---

# 5 Module der Extension

## Modul 1

API Routing

```
Classes/Api/Controller
```

Controller Beispiele:

```
PageController
NavigationController
MediaController
SettingsController
PreviewController
```

---

## Modul 2

Query Services

```
Classes/Domain/Query
```

Beispiele:

```
PageQueryService
ContentQueryService
NavigationQueryService
MediaQueryService
```

Aufgabe:

TYPO3 Daten lesen.

---

## Modul 3

Normalizer

```
Classes/Domain/Normalizer
```

Transformiert TYPO3 Modelle zu API Struktur.

Beispiel:

```
PageNormalizer
ContentNormalizer
MediaNormalizer
NavigationNormalizer
```

---

## Modul 4

DTO Layer

```
Classes/Domain/Dto
```

Beispiele:

```
PageDto
ContentElementDto
NavigationItemDto
MediaDto
```

DTOs sind **API-Contracts**.

---

## Modul 5

Serializer

Konvertiert DTO → JSON.

Option:

```
Symfony Serializer
oder
custom serializer
```

---

# 6 Content Mapping

tt_content wird normalisiert.

Beispiel Mapping:

TYPO3

```
CType = textmedia
```

API

```json
{
  "type": "textmedia",
  "headline": "...",
  "text": "...",
  "media": [...]
}
```

Normalizer:

```
ContentElementNormalizerRegistry
```

Plugin-System:

```
registerNormalizer("textmedia")
registerNormalizer("image")
registerNormalizer("carousel")
```

---

# 7 Navigation API

Endpoint:

```
GET /api/v1/navigation/main
```

Response:

```json
{
  "items": [
    {
      "title": "Home",
      "url": "/",
      "children": []
    }
  ]
}
```

Quelle:

```
pages table
site config
```

---

# 8 Media API

Endpoint:

```
GET /api/v1/media/{id}
```

Response:

```json
{
  "id": 12,
  "url": "/fileadmin/image.jpg",
  "width": 1200,
  "height": 800,
  "alt": "..."
}
```

Integration:

```
FAL
sys_file
sys_file_reference
```

---

# 9 Preview System

Problem:

Headless braucht Preview.

Konzept:

```
/api/v1/preview/page/{id}
```

Auth:

```
previewToken
```

Flow:

```
Editor klickt Preview
Frontend bekommt Token
Frontend lädt Preview API
```

---

# 10 Mehrsprachigkeit

API nutzt:

```
site configuration
sys_language_uid
```

Request:

```
/api/v1/pages/home?lang=de
```

oder

Header:

```
Accept-Language
```

---

# 11 Caching

3 Layer:

### TYPO3 Cache

```
cache_api
```

### HTTP Cache

```
Cache-Control
ETag
```

### CDN

```
Varnish / Cloudflare
```

Invalidation:

```
DataHandler Hook
```

Bei Content Änderung → Cache flush.

---

# 12 Versionierung

API versioniert:

```
/api/v1/
/api/v2/
```

DTO Änderungen → neue Version.

---

# 13 Security

Optional:

```
JWT
API keys
OAuth
```

Aber:

Public content meist ohne Auth.

Preview benötigt Auth.

---

# 14 OpenAPI Support

Extension generiert:

```
/api/docs
```

OpenAPI spec.

Frontend Entwickler können:

* Types generieren
* SDK generieren

---

# 15 Teststrategie

### Unit Tests

Normalizer.

### Functional Tests

API Endpoints.

### Contract Tests

JSON Schema.

---

# 16 MVP Scope

Minimal Version:

### Endpoints

```
GET /api/v1/pages/{slug}
GET /api/v1/navigation/main
GET /api/v1/media/{id}
```

### Features

* content elements
* navigation
* language
* media
* caching

Keine:

* forms
* search
* login
* ecommerce

---

# 17 Beispiel Klassenstruktur

```
EXT:content_api
 ├─ Classes
 │  ├─ Api
 │  │  ├─ Controller
 │  │  │  ├─ PageController.php
 │  │  │  ├─ NavigationController.php
 │  │
 │  ├─ Domain
 │  │  ├─ Query
 │  │  │  ├─ PageQueryService.php
 │  │  │  ├─ ContentQueryService.php
 │  │
 │  │  ├─ Normalizer
 │  │  │  ├─ PageNormalizer.php
 │  │  │  ├─ ContentElementNormalizer.php
 │  │
 │  │  ├─ Dto
 │  │  │  ├─ PageDto.php
 │  │  │  ├─ ContentElementDto.php
 │
 │  ├─ Infrastructure
 │  │  ├─ Serializer
 │  │  ├─ Cache
 │
 ├─ Configuration
 │  ├─ Services.yaml
 │  ├─ Routes.php
```

---

# 18 Vorteile gegenüber EXT:headless

| EXT:headless      | eure Extension |
| ----------------- | -------------- |
| TypoScript heavy  | PHP only       |
| page rendering    | echte API      |
| schwer testbar    | testbar        |
| kein API Contract | stabile DTOs   |
| payload variabel  | versioniert    |

---

# 19 Aufwand realistisch

MVP:

```
3–6 Wochen
```

stabile Plattform:

```
3–6 Monate
```

---

# 20 Mein wichtigster Rat

Der größte Fehler bei Headless TYPO3:

**zu viel abstrahieren**

Der richtige Ansatz:

TYPO3 Daten lesen → sauber normalisieren → API liefern.
