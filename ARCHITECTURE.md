# CRM - Architecture Documentation

## Overview

**CRM** is an enterprise-grade Customer Relationship Management system built on **Laravel 12** with a **modular package architecture** powered by Konekt Concord. The core `app/` directory is intentionally minimal — all business logic resides in 20 self-contained modules under `packages/XYZ/`.

---

## Technology Stack

| Layer            | Technology                          |
|------------------|-------------------------------------|
| Framework        | Laravel 12                          |
| Language         | PHP 8.2+                           |
| Database         | MySQL                               |
| ORM              | Eloquent                            |
| API Auth         | Laravel Sanctum                     |
| Data Layer       | Repository Pattern (L5-Repository)  |
| Module System    | Konekt Concord                      |
| Build Tool       | Vite 5                              |
| HTTP Client      | Axios                               |
| PDF Generation   | DomPDF / mPDF                       |
| Excel            | Maatwebsite/Excel                   |
| SMS/WhatsApp     | Twilio SDK 8.x                      |
| Real-time        | Pusher / Laravel Broadcasting        |
| Email            | SMTP / IMAP / Sendgrid              |
| Testing          | Pest 3 + PHPUnit 11                 |
| Code Style       | Laravel Pint                        |
| API Transform    | League Fractal + Laravel API Resources |

---

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────┐
│                     HTTP / API Layer                     │
│            (Routes, Controllers, Middleware)             │
├─────────────────────────────────────────────────────────┤
│                    Application Layer                     │
│         (Services, Helpers, Facades, DataGrids)          │
├─────────────────────────────────────────────────────────┤
│                     Domain Layer                         │
│     (Models, Repositories, Contracts, Attributes)        │
├─────────────────────────────────────────────────────────┤
│                  Infrastructure Layer                    │
│    (Migrations, Jobs, Events, Listeners, Mail, Queue)    │
├─────────────────────────────────────────────────────────┤
│                      Database                            │
│                       (MySQL)                            │
└─────────────────────────────────────────────────────────┘
```

---

## Directory Structure

```
time-vault-panel/
├── app/                          # Minimal core (3 PHP files)
│   ├── Http/Controllers/         # Base controller
│   ├── Models/                   # User model
│   └── Providers/                # AppServiceProvider
├── packages/XYZ/              # 20 modular packages (890 PHP files)
│   ├── Admin/                    # Dashboard, controllers, views, reporting
│   ├── Core/                     # System foundation, ACL, menus, facades
│   ├── Lead/                     # Lead pipeline management
│   ├── Contact/                  # Organizations & persons
│   ├── User/                     # User management, roles, groups
│   ├── Activity/                 # Activity tracking & logging
│   ├── Email/                    # Email management & inbound processing
│   ├── SMS/                      # Twilio SMS/WhatsApp messaging, templates, scheduling
│   ├── Attribute/                # Dynamic EAV attribute system
│   ├── DataGrid/                 # Data table framework
│   ├── DataTransfer/             # Bulk import/export
│   ├── Quote/                    # Quote/proposal management
│   ├── Product/                  # Product catalog & inventory
│   ├── Warehouse/                # Warehouse & location management
│   ├── Tag/                      # Flexible tagging system
│   ├── EmailTemplate/            # Email template management
│   ├── Automation/               # Workflows & webhooks
│   ├── Marketing/                # Email campaigns
│   ├── WebForm/                  # Public form builder
│   └── Installer/                # Installation wizard
├── config/                       # 23 config files
├── database/migrations/          # 4 core migrations
├── routes/                       # Main route files (most routing in packages)
├── resources/                    # Minimal (views/assets live in packages)
├── tests/                        # Pest + PHPUnit tests
└── public/                       # Web root
```

---

## Modular Package Architecture

Each module under `packages/XYZ/` follows a consistent internal structure:

```
XYZ/{Module}/src/
├── Config/           # Module configuration (menu, ACL, etc.)
├── Contracts/        # Interfaces for models
├── Database/
│   ├── Migrations/   # Module-specific migrations
│   ├── Factories/    # Model factories
│   └── Seeders/      # Data seeders
├── Http/
│   ├── Controllers/  # Request handlers
│   ├── Requests/     # Form request validation
│   ├── Resources/    # API resource transformers
│   └── Middleware/    # Module middleware
├── Models/           # Eloquent models + proxy models
├── Repositories/     # Data access layer
├── Providers/        # Service provider (module registration)
├── Resources/
│   ├── lang/         # Translations
│   └── views/        # Blade templates
├── Routes/           # Module routes
├── Services/         # Business logic
├── Helpers/          # Utility classes
├── Jobs/             # Queue jobs
├── Events/           # Domain events
├── Listeners/        # Event handlers
├── Console/          # Artisan commands
├── Traits/           # Reusable behaviors
├── Enums/            # Enumerations
└── Facades/          # Service facades
```

Modules are registered via **Concord** in `config/concord.php`, which controls load order and dependency resolution.

---

## Design Patterns

### 1. Repository Pattern
All data access uses **Prettus L5-Repository**. Every model has a corresponding repository class that abstracts queries, supports filtering, pagination, caching, and eager loading.

```
Model (Eloquent) → Repository (L5-Repository) → Controller
```

### 2. Proxy Model Pattern
Every model has a corresponding Proxy class (e.g., `LeadProxy`, `UserProxy`). Concord uses these to allow model substitution and extension without modifying core code.

### 3. Entity-Attribute-Value (EAV)
The `Attribute` package implements dynamic attributes via:
- `Attribute` — field definitions (type, label, validation)
- `AttributeOption` — predefined values for select fields
- `AttributeValue` — actual values stored per entity
- `CustomAttribute` trait — attach to any model for dynamic fields

### 4. Contract-Based Design
Every model defines a Contract (interface) in its package. Repositories and services depend on contracts, not concrete models, enabling substitution.

### 5. Event-Driven Architecture
- Activity listeners automatically log entity changes on Leads, Persons, Activities
- Automation workflows trigger on entity events
- Webhooks dispatch on configurable entity events

### 6. Facade Pattern
Key services exposed as facades:
- `Acl` — permission checking
- `Core` — system configuration access
- `Menu` — navigation management
- `SystemConfig` — configuration management
- `Bouncer` — ACL enforcement

### 7. API Resource Pattern
Laravel API Resources (16 transformers) provide consistent JSON responses for all entities (Activity, Email, Lead, Person, Organization, Product, Quote, Stage, Type, Source, Tag, User).

---

## Core Entity Relationship Model

```
User ─────────────────────┐
  ├── has many Roles       │
  ├── has many Groups      │
  └── sales_owner of ──────┤
                           ▼
Lead ◄──────────────── Person ──── belongs to ──── Organization
  ├── belongs to Pipeline    ├── has many Activities
  ├── belongs to Stage       ├── has many Tags
  ├── has many Activities    └── has many Quotes
  ├── has many Tags
  ├── has many Products ──── Product
  └── has many Quotes           ├── has many ProductInventories
                                │       └── belongs to Warehouse
  Quote                         ├── has many Activities
  ├── has many QuoteItems       └── has many Tags
  └── QuoteItem
       └── belongs to Product   Warehouse
                                  ├── has many Locations
  Email                           ├── has many ProductInventories
  ├── has many Attachments        └── has many Activities
  └── has many Tags

  Activity (polymorphic: Lead, Person, Product, Warehouse)
  ├── has many Files
  └── has many Participants

  SMS Message
  ├── belongs to Person
  ├── belongs to Lead
  ├── belongs to User (sender)
  ├── belongs to TwilioNumber
  └── belongs to Template

  TwilioNumber
  └── has many Messages

  Template (SMS)
  └── has many Messages

  Workflow → triggers on entity events
  Webhook  → calls external systems on events
  Campaign → has many marketing Events
```

---

## Package Breakdown

### Admin (418 files) — UI & Dashboard
The largest module. Contains 54 controllers, 25 DataGrids, 287 Blade templates, and 7 reporting helpers (Activity, Lead, Organization, Person, Product, Quote). Provides the complete admin interface, ACL configuration, and menu definitions.

### Core (53 files) — Foundation
Base repository class, ACL system, menu management, system configuration, translation support, PDF handler, and Vite integration. All other packages depend on this.

### Lead (46 files) — Pipeline Management
Models: Lead, Pipeline, Stage, Source, Type, Product. Includes MagicAI helper/service for AI-powered lead features. 18 migrations covering pipeline stages, sources, types, rotten days tracking.

### Contact (21 files) — CRM Contacts
Organization and Person models with relationships to activities, tags, and sales owners. 10 migrations.

### User (21 files) — User Management
User, Role, and Group models with authentication and authorization. 7 migrations.

### Activity (20 files) — Tracking
Activity model with Files and Participants. `LogsActivity` trait for automatic change tracking on any model. 5 migrations.

### Email (24 files) — Email Management
Inbound email processing via IMAP (Webklex) and Sendgrid. Email parser, HTML filter, charset management, and attachment handling. 3 migrations.

### SMS (21 files) — Twilio SMS/WhatsApp Messaging
Models: Message, TwilioNumber, Template. Full Twilio integration for outbound SMS and WhatsApp with inbound webhook handling. Features include:
- **Bulk messaging** — send to multiple recipients (comma-separated)
- **SMS Templates** — reusable message templates with channel targeting (sms/whatsapp/both)
- **Scheduled messages** — queue messages for future delivery via `sms:send-scheduled` command (runs every minute)
- **Real-time chat** — conversation view with 5-second polling for live updates
- **Broadcasting** — `NewSMSMessage` event with Pusher support for real-time notifications
- **Multi-number support** — manage multiple Twilio numbers with per-number SID/token override
- **Conversation threading** — messages linked to Persons for chat-style conversation view
- 4 migrations, 3 DataGrids (SMS, TwilioNumber, Template), 3 controllers, 3 blade view groups, 1 Artisan command, 1 broadcast event

### Attribute (20 files) — Dynamic Fields
EAV implementation. `CustomAttribute` trait allows any model to have user-defined custom attributes. 4 migrations.

### DataGrid (26 files) — Data Tables
Reusable grid framework with typed columns (Text, Integer, Decimal, Date, DateTime, Boolean, Aggregate), filtering, sorting, mass actions, export, and saved filters.

### DataTransfer (37 files) — Import/Export
Bulk importers for Leads, Persons, Products from CSV/Excel. Uses 6 queued jobs for async batch processing. 2 migrations.

### Automation (22 files) — Workflows & Webhooks
Workflow engine with entity-specific handlers (Lead, Person, Activity, Quote). Webhook service for external system integration. 2 migrations.

### Product (15 files) — Catalog
Product model with warehouse-based inventory tracking. 5 migrations.

### Warehouse (14 files) — Inventory
Warehouse and Location models for inventory management. 4 migrations.

### Quote (12 files) — Proposals
Quote and QuoteItem models for managing proposals and line items. 2 migrations.

### Marketing (15 files) — Campaigns
Campaign and Event models. CampaignMail for email composition. Console command for campaign execution. 2 migrations.

### WebForm (40 files) — Form Builder
Public-facing web form builder with customizable attributes, validation, and embed code generation. Includes its own DataGrid. 2 migrations.

### Tag (7 files) — Tagging
Polymorphic tagging system used by Leads, Persons, Products, Emails, and Warehouses. 1 migration.

### EmailTemplate (8 files) — Templates
Reusable email template management. 2 migrations.

### Installer (50 files) — Setup
Installation wizard with database manager, environment configuration, server requirement checks, and 9 seeders for initial data. 5 migrations.

---

## Database

**Total Migrations:** 89 (4 core + 85 from packages)

### Migrations per Package

| Package       | Count | Package        | Count |
|---------------|-------|----------------|-------|
| Lead          | 18    | Warehouse      | 4     |
| Contact       | 10    | Attribute      | 4     |
| User          | 7     | Core           | 4     |
| Activity      | 5     | Email          | 3     |
| Installer     | 5     | Automation     | 2     |
| Product       | 5     | DataTransfer   | 2     |
| SMS           | 4     | EmailTemplate  | 2     |
| Admin         | 2     | Marketing      | 2     |
| Quote         | 2     | WebForm        | 2     |
| DataGrid      | 1     | Tag            | 1     |

### Key Tables

| Domain       | Tables                                                        |
|--------------|---------------------------------------------------------------|
| Users        | users, roles, groups, user_groups                             |
| Leads        | leads, lead_pipelines, lead_pipeline_stages, lead_products, lead_tags, lead_quotes |
| Contacts     | persons, organizations, person_tags, person_activities        |
| Activities   | activities, activity_files, activity_participants             |
| Products     | products, product_inventories                                 |
| Quotes       | quotes, quote_items                                           |
| Emails       | emails, email_attachments, email_tags                         |
| Attributes   | attributes, attribute_options, attribute_values               |
| SMS          | sms_messages, twilio_numbers, sms_templates                   |
| Automation   | workflows, webhooks                                           |
| Data Import  | imports, import_batches                                       |
| Marketing    | campaigns, events                                             |
| Web Forms    | web_forms, web_form_attributes                                |
| Warehouses   | warehouses, warehouse_locations                               |
| System       | core_config, countries, country_states, tags, email_templates, saved_filters |

---

## Configuration

**23 config files** in `config/`:

| File                | Purpose                              |
|---------------------|--------------------------------------|
| app.php             | Application name, timezone, locale   |
| auth.php            | Authentication guards                |
| broadcasting.php    | Laravel Echo / Pusher                |
| breadcrumbs.php     | Breadcrumb definitions               |
| cache.php           | Cache driver configuration           |
| concord.php         | Module registration & load order     |
| cors.php            | CORS settings                        |
| database.php        | DB connection settings               |
| filesystems.php     | Filesystem disks                     |
| hashing.php         | Password hashing                     |
| imap.php            | IMAP email configuration             |
| crm-vite.php     | Custom Vite integration              |
| logging.php         | Logging channels                     |
| mail.php            | SMTP mail configuration              |
| mail-receiver.php   | Inbound email processing             |
| queue.php           | Queue driver configuration           |
| repository.php      | L5 Repository settings               |
| sanctum.php         | API token configuration              |
| services.php        | Third-party service credentials      |
| session.php         | Session driver configuration         |
| tinker.php          | PsySH REPL settings                  |
| twilio.php          | Twilio SMS/WhatsApp credentials      |
| view.php            | View compilation paths               |

---

## Routing

Main route files (`routes/`) are mostly empty — routing is delegated to packages.

### Admin Routes (`packages/XYZ/Admin/src/Routes/Admin/`)

| File                       | Purpose                                      |
|----------------------------|----------------------------------------------|
| auth-routes.php            | Login, logout, password reset                |
| leads-routes.php           | Lead CRUD & pipeline management              |
| mail-routes.php            | Email management                             |
| contacts-routes.php        | Organizations & persons                      |
| activities-routes.php      | Activity tracking                            |
| products-routes.php        | Product management                           |
| quote-routes.php           | Quote management                             |
| settings-routes.php        | Admin settings (users, roles, pipelines, etc.)|
| configuration-routes.php   | System configuration                         |
| sms-routes.php             | SMS/WhatsApp messaging, templates, numbers   |
| rest-routes.php            | REST API endpoints                           |
| web.php                    | Admin panel entry routes                     |

### Other Route Files
- `packages/XYZ/Admin/src/Routes/Front/web.php` — Front-facing routes
- `packages/XYZ/Installer/src/Routes/` — Installation wizard
- `packages/XYZ/WebForm/src/Routes/` — Public web form submissions

---

## Authentication & Authorization

- **Authentication:** Laravel Sanctum (token-based API auth) + session-based web auth
- **Authorization:** Custom ACL system via `Bouncer` facade
  - `acl.php` config defines permission tree
  - Roles map to ACL permissions
  - Users belong to Roles and Groups
  - Middleware enforces permissions per route

---

## Key Features

| Feature              | Description                                              |
|----------------------|----------------------------------------------------------|
| Lead Management      | Pipeline-based leads with stages, sources, types         |
| Contact Management   | Organizations and persons with relationships             |
| Activity Tracking    | Automatic logging of entity changes                      |
| Email Integration    | Inbound/outbound via IMAP and Sendgrid                   |
| Quote Management     | Proposals with line items linked to products             |
| Product Catalog      | Products with warehouse-based inventory                  |
| SMS & WhatsApp       | Twilio messaging with templates, scheduling, real-time chat |
| Workflow Automation  | Event-driven workflows with condition-based triggers     |
| Webhooks             | HTTP callbacks on entity events                          |
| Marketing Campaigns  | Email campaign management and execution                  |
| Web Forms            | Public form builder with embed code                      |
| Bulk Import/Export   | CSV/Excel import for leads, persons, products            |
| Dynamic Attributes   | EAV system for custom fields on any entity               |
| DataGrids            | Advanced data tables with filters, sorting, mass actions |
| Reporting            | Dashboard with 7 entity-specific reporters               |
| Multi-language       | 7 languages (AR, EN, ES, FA, PT_BR, TR, VI)              |
| AI Features          | MagicAI service for lead intelligence                    |
| REST API             | Full API with 16 resource transformers                   |

---

## Statistics

| Metric              | Count  |
|---------------------|--------|
| Total PHP Files     | 890    |
| Packages/Modules    | 20     |
| Models              | 42     |
| Proxy Models        | 42     |
| Repositories        | 42     |
| Contracts           | 46     |
| Controllers         | 54     |
| DataGrids           | 25     |
| Blade Templates     | 287    |
| Migrations          | 89     |
| Service Providers   | 41     |
| Queue Jobs          | 6      |
| Broadcast Events    | 2      |
| Event Listeners     | 5      |
| Artisan Commands    | 5      |
| Helper Classes      | 38     |
| Services            | 3      |
| API Resources       | 16     |
| Form Requests       | 9      |
| Middleware           | 5      |
| Route Files         | 20     |
| Facades             | 5      |
| Traits              | 5      |
| Enums               | 4      |
| Languages           | 7      |
| Config Files        | 23     |
