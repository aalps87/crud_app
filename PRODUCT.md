# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

Laravel 12 with Blade views, Vite, Tailwind CSS 4, SQLite, and the existing PHP/Apache runtime.

## Users

The primary user is a technical-course evaluator or classmate exploring a student CRUD project. They should understand the aviation theme immediately, enjoy the landing-page experience, and reach the inventory workflow without learning a complex product.

## Product Purpose

Demonstrate a polished aviation-themed web experience around a working aircraft CRUD. The landing page earns attention, a lightweight email-and-password screen creates a deliberate transition, and aircraft records become a hangar inventory.

## Positioning

The project treats a basic Laravel assignment as a compact flight-operations experience: cinematic on arrival, calm and legible when the user starts managing records.

## Operating Context

Visitors arrive on the public landing page, where only aircraft explicitly marked public are shown. The administrator authenticates through the login experience and continues to the protected aircraft index. Inside the CRUD they list, create, inspect, edit, and delete aircraft, control public visibility, and optionally manage a photo.

## Capabilities and Constraints

- Preserve the Laravel resource workflow with administrator-only session access, public/private visibility, and aircraft photos.
- Build all work inside `crud_app` and serve the entire experience through the existing PHP/Apache application.
- The CRUD is restricted to the configured administrator credentials; the landing page remains public.
- The `aeronaves` table contains only `id`, `prefixo`, `modelo`, `tripulacao`, `data_fab`, and nullable `foto`; public visibility lives in the related `aeronave_publicacoes` table.
- Optional aircraft photos are JPEG or PNG files stored under `public/assets/aeronaves`, with a relative path of at most 30 characters persisted in `foto` and lifecycle cleanup on replacement or deletion.
- Keep the CRUD intentionally simple; visual design, illustration, transitions, and motion are the primary craft focus.
- Use Blade, the existing Vite entry points, and progressive JavaScript rather than introducing a separate frontend runtime.

## Brand Commitments

- Aviation is the subject.
- Flighty is the primary quality reference: product-led storytelling, data as visual material, floating information surfaces, and polished motion.
- Hyer Aviation is the secondary reference: generous composition, premium restraint, oversized imagery, and editorial pacing.
- The result must feel clean, modern, impactful, and original rather than copying either reference.
- Custom illustration and motion assets should be produced with Higgsfield where they materially improve the experience.

## Evidence on Hand

- Aircraft CRUD implementation under `app/Http`, `app/Models`, `routes/web.php`, and `resources/views/products`.
- SQLite schema migrated from the former product records to the aircraft structure, with visibility kept in a separate related table.
- No approved logo, product name, testimonials, commercial claims, customer metrics, or production authentication flow. Future work must not present invented claims as real.

## Product Principles

- Make the first viewport memorable before explaining the system.
- Let aviation data and movement carry the theme instead of decorative airplane clichés.
- Keep operation screens calmer and more legible than the marketing surface.
- Preserve backend truth while transforming the presentation completely.
- Motion must communicate direction, state, or continuity and remain optional for reduced-motion users.

## Accessibility & Inclusion

Use semantic HTML, keyboard-accessible controls, visible focus states, strong contrast, useful validation messaging, responsive layouts, and `prefers-reduced-motion` fallbacks.
