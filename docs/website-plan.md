# MAK Digital Arena — website design and implementation plan

Prepared 29 September 2026. Status: planning complete; implementation has not started beyond the existing Laravel foundation. The user has authorized creative direction and use of 3D. No additional design permission is needed to begin the first implementation milestone.

## 1. Objective and creative direction

Create a distinctive agency website that makes MAK's capabilities understandable, demonstrates credible work, and helps visitors start a relevant business conversation. Interpret the ambition of “100X better” through observable improvements in visual identity, storytelling, usability, accessibility, speed and enquiry completion. It is not a numerical performance or conversion promise.

**Creative direction: MAK's blue ribbon globe becomes an interactive, three-dimensional brand signature.**

Retain the recognizable MAK logo and its blue/cyan identity. Surround it with warm white surfaces, deep ink typography, expressive editorial layouts and occasional dark blue sections. The overall feeling should be confident, inventive and approachable.

Proposed hero copy:

> You visualize.
> We digitalize.
>
> Websites, brands and campaigns built to move your business forward.

Supporting descriptor: “Digital marketing, development & business consulting · Indore, India.”

Primary action: **Start a project**. Secondary action: **Explore our work**.

## 2. Reference findings and evidence

[Lemon Yellow](https://lemonyellow.design/) was inspected in Chrome at 1440 × 1000 and 390 × 844. Observed elements include a light canvas, large rounded typography, yellow accents, a bicycle illustration carried through scrolling, a persistent header, a process sequence and project previews. Its homepage loaded GSAP, ScrollTrigger, Lenis, Lottie and a loader video. No canvas appeared in the initial desktop DOM inspection; this is evidence of animated layered assets, not confirmation of a live 3D engine. The review is a visual sample, not a complete performance or accessibility audit.

Use its cohesive storytelling and friendly tone as inspiration. MAK will have original compositions, assets and animation choreography.

Evidence saved locally:

- `storage/app/reference/lemonyellow/desktop-hero.png`
- `storage/app/reference/lemonyellow/desktop-solutions.png`
- `storage/app/reference/lemonyellow/desktop-process.png`
- `storage/app/reference/lemonyellow/mobile-hero.png`
- `storage/app/reference/lemonyellow/audit.json`

## 3. Visual system

| Element | Proposed direction |
| --- | --- |
| Background | Warm white `#F7F8F5`, with clean white content surfaces |
| Text | Deep ink `#101923`; secondary text `#526071` |
| Brand | Cobalt `#165DDE`, deep blue `#123877`, cyan `#19B8D6` |
| Dark sections | Midnight blue `#091D35`, with restrained cyan highlights |
| Typography | Self-hosted, properly licensed variable sans; start with Manrope for headings and Inter for body text, subject to visual testing |
| Scale | Fluid editorial headings, approximately 64–112 px on large screens and 40–56 px on phones; 16–18 px readable body copy |
| Layout | 12-column desktop grid, 4-column mobile grid, generous spacing, deliberately varied section compositions |
| Imagery | Real project captures, client work and authentic company photographs where available |
| Details | Thin rules, small section numbers, directional arrows and subtle blue ribbon motifs |

Keep the existing logo intact. A cleaner vector source may be obtained later; do not invent a replacement wordmark. Check actual foreground/background contrast when selecting final tokens. Cyan is an accent, not small body text on white.

## 4. Homepage storyboard

| Order | Section and content | Experience | Intended result |
| --- | --- | --- | --- |
| 1 | Compact header: Work, Services, About, Insights, Start a project | Logo left; desktop navigation; accessible mobile menu | Visitors immediately understand where to go |
| 2 | Hero with the existing tagline, concise positioning and two actions | Large left-aligned copy; original 3D ribbon globe to the right; mobile object below the copy | Memorable identity with an immediately readable offer |
| 3 | Verified client/logo strip | Static or user-controlled horizontal list | Establish confidence without interrupting reading |
| 4 | Selected work: three strong projects | One large featured project and two contrasting layouts; restrained image movement | Show credible evidence near the top |
| 5 | Services grouped by business need | Four substantial service rows; desktop visual changes on focus/hover; tap-friendly expansion on mobile | Help visitors choose an appropriate service |
| 6 | How MAK works | Discover → Strategize → Create → Launch → Improve; brief deliverables for each step | Make the working relationship tangible |
| 7 | Why MAK and client feedback | Concise differentiators, one featured testimonial, supporting quotes | Show the people and process behind the work |
| 8 | Selected insights | Two useful articles, with dates and clear categories | Demonstrate current thinking; omit weak material |
| 9 | Project invitation and footer | Short enquiry form, email, phone and optional WhatsApp link | Make starting a conversation straightforward |

Draft service groups:

- **Build your presence:** website development, mobile apps and ecommerce.
- **Get discovered:** SEO and content marketing.
- **Grow your audience:** paid campaigns and social media marketing.
- **Shape your direction:** branding and business consultation, limited to verified offerings.

Avoid long introductory paragraphs, repeated CTAs between every section and compulsory motion before visitors can read or click.

## 5. 3D and motion specification

### Signature scene

Build a custom globe from curved blue ribbons inspired by the existing logo. Use Three.js for real depth, lighting and subtle pointer response. Start with procedural ribbon geometry so the scene can remain small and controllable. Use a softly lit material and a restrained background; the object must feel part of the MAK identity.

On entry, the ribbons assemble once. On desktop, gentle pointer movement changes viewing angle. During the hero's exit, the globe makes a short, restrained rotation. Elsewhere, flat ribbon motifs connect the sections; a second large WebGL scene is unnecessary.

The headline, navigation, actions and copy remain normal HTML. Show a static brand composition immediately, then load the interactive scene as an enhancement. Preserve the poster if WebGL is unavailable, initialization fails, data saving is enabled or the device cannot sustain the experience.

### Interaction rules

- Intro: approximately 600–900 ms, with content visible before it completes.
- Buttons and navigation: approximately 150–250 ms.
- Section reveals: approximately 400–650 ms with restrained staggering.
- Project images: mild depth/scale feedback on pointer devices; equivalent visible actions on touch and keyboard.
- Process: optional short desktop scroll sequence, with all steps available through regular scrolling on mobile.
- Header/menu: clear open/close states, keyboard focus handling and Escape support.
- Use native scrolling. Do not intercept wheel/touch scrolling or force visitors through a long pinned scene.
- Respect `prefers-reduced-motion`: static scene, immediate text, minimal transitions and no parallax.
- Pause 3D rendering while the scene is offscreen or the tab is hidden. Bound device pixel ratio and reduce geometry/effects on constrained devices.
- No essential content, form action or navigation depends on animation completing.

Implementation references: [GSAP responsive/reduced-motion handling](https://gsap.com/docs/v3/GSAP/gsap.matchMedia()/) and [Three.js responsive rendering](https://threejs.org/manual/pages/responsive.html).

## 6. Information architecture and page templates

Primary routes:

```text
/
/work
/work/{project}
/services
/services/{service}
/about
/insights
/insights/{article}
/careers
/contact
/privacy-policy
/terms-and-conditions
```

**Project detail:** client context, challenge, MAK's verified role, approach, real deliverables, gallery, available results and a relevant next step. Where results are unverified, show the work without numerical performance claims.

**Service detail:** business problem, ideal audience, deliverables, process, relevant projects, FAQs and contact action. Keep individual SEO landing pages useful and distinct.

**About:** company story, values, working approach and real people. Include team profiles only when authentic material is available.

**Insights:** migrate selected relevant articles with readable typography, dates, categories and related services. No empty categories.

**Careers:** company culture and real vacancies; provide a general enquiry path if no jobs are open. Do not invent openings.

**Contact:** a concise form plus direct contact channels. Scheduling integration is optional and only appears when a real booking destination exists.

FAQs belong within relevant service pages unless there is enough useful material for a separate help page. Verify and intentionally redirect old support/FAQ URLs during migration.

## 7. Content and asset preparation

The local source contains 78 archived URL records resolving to 64 final URLs, plus 484 downloaded assets. Use it as an evidence library, not automatic publication input.

1. Create a content map from source URLs to final routes and assign keep/rewrite/redirect/omit decisions.
2. Separate real company and client content from WordPress/theme demo material.
3. Rewrite copy for clarity while retaining verified facts and the original brand identity.
4. Select three initial project candidates from the archived portfolio. Stoneplus Surfaces, Hansline, KPL and Dr. Sachin Arya are candidates, subject to verifying usable imagery and MAK's actual role.
5. Inventory each selected image by source, dimensions, purpose, alt text and optimized output.
6. Keep publication-sensitive items visibly pending: portfolio results, testimonials, client usage permissions, address/email inconsistencies and team identities.
7. Omit unsupported counters and claims. The old site's “250+” claim needs verification before featuring it prominently.
8. Keep the original archive unchanged; store curated content separately.

Full copy preparation can continue without waiting for every missing item. Use verified material and leave disputed items unpublished. See `docs/content-review.md` for known source defects.

## 8. Laravel implementation architecture

Retain Laravel 13, Blade and Vite. Use modular vanilla JavaScript, GSAP/ScrollTrigger for sequencing, and a dynamically imported Three.js module for the hero. A client-side SPA framework is unnecessary for this content-oriented website.

Suggested feature locations:

```text
app/Http/Controllers/Frontend/
app/Http/Controllers/Admin/
app/Http/Requests/
app/Models/                        Curated services, projects, articles, enquiries
app/Services/                      Media preparation and enquiry handling
resources/views/layouts/
resources/views/components/
resources/views/frontend/
resources/views/admin/
resources/css/frontend/
resources/js/frontend/             app, navigation, motion, hero-scene, enquiry
database/migrations/
tests/Feature/
```

Use public storage only for approved display assets; keep archived source files and any private documents outside the web root. Provide width/height and responsive AVIF/WebP image variants. Use SQLite for local work and MySQL for the planned XAMPP/production environment when configured.

Curated data models should cover projects, services, articles, testimonials, enquiries and site settings. Add vacancies/applications only if a real recruitment flow is needed. Preserve `LegacyPage` as imported evidence rather than making its unreviewed JSON the public CMS.

## 9. Enquiries and content management

Deliver a focused Laravel admin area with authenticated access for managing services, projects, articles, testimonials, contact settings and enquiries. Use draft/published states, validated media uploads and authorization. Keep administration visually simple and separate from the animated public site.

Initial enquiry fields: name, email, optional phone, service interest and project description. Add budget/timeline only if it helps qualification without making the first contact burdensome.

Submission flow:

1. Validate input on the server with clear inline errors.
2. Apply CSRF protection, rate limits and basic bot filtering.
3. Save the enquiry transactionally.
4. Show success only after persistence succeeds; prevent accidental duplicate submissions.
5. Queue a notification when mail is configured, with retry and failure visibility.

The local environment uses logged mail until real SMTP credentials are supplied. A database success must not be described as a delivered email. The next build does not require live email, analytics or booking credentials.

## 10. Performance, accessibility and SEO acceptance criteria

These are implementation targets to measure, not claims about either website today.

| Area | Acceptance criteria |
| --- | --- |
| Performance | Aim for LCP ≤ 2.5 s, INP ≤ 200 ms, CLS ≤ 0.1 at the 75th percentile after sufficient real traffic; use repeatable lab checks before launch |
| Initial load | Start with a ≤ 1 MB compressed critical page budget and ≤ 200 KB compressed critical JavaScript; defer 3D and below-fold assets |
| 3D | Target ≤ 750 KB compressed for the lazy scene bundle/assets; revise effects or geometry if measured cost is too high |
| Mobile | Purpose-built layouts at 360/390/768 px; no horizontal page overflow, obscured controls or hover-only content |
| Accessibility | Visible focus, semantic landmarks/headings, labeled forms, keyboard operation, accessible menu behavior and reduced-motion support; target WCAG 2.2 AA |
| Reliability | Main content and navigation survive JavaScript/WebGL failure; all CTAs have working destinations |
| SEO | Server-rendered copy, unique metadata, canonical URLs, descriptive titles, XML sitemap, robots settings, social previews and truthful structured data |
| Migration | One-hop 301 redirects for legitimate moved pages; intentional handling for obsolete demo content; no blanket redirect of every missing URL to home |
| Quality | Test desktop Chrome/Edge/Firefox and real Safari/iOS/Android when available; emulation alone is not a real-device pass |

Core Web Vitals thresholds and the distinction between lab and field testing come from [web.dev](https://web.dev/articles/vitals). A Lighthouse score alone will not be treated as evidence of real-user INP or conversions.

Before launch, check footer/legal links, enquiry validation and persistence, mail failure behavior, mobile navigation, missing images, console errors and the old-to-new route map. Deploy with debug disabled, correct cache/storage configuration, HTTPS and database backups. Deployment credentials and verified business facts become required at that stage, not during local visual development.

## 11. Delivery sequence and completion gates

| Phase | Deliverable | Gate before proceeding |
| --- | --- | --- |
| 1. Content and art direction | Content map, design tokens, homepage composition, project/asset shortlist | Coherent MAK identity; verified initial copy and imagery |
| 2. Working homepage prototype | Header, hero, ribbon-globe prototype, selected work, services and contact composition at desktop/mobile sizes | Strong visual hierarchy; usable navigation; 3D fallback and responsive behavior work |
| 3. Complete public homepage | Remaining sections, polished motion, responsive images, contact form UI | Keyboard and reduced-motion checks pass; motion stays within budget |
| 4. Content templates and backend | Services, projects, articles, about/careers/contact; migrations, admin and enquiry persistence | Real data renders; authorized editing and form tests pass |
| 5. Migration and refinement | Curated source content, redirects, metadata, image optimization, copy cleanup | No accidental publication of demo copy or unverified claims |
| 6. Release readiness | Browser/device checks, performance measurements, enquiry delivery verification and deployment notes | Report actual results; resolve material failures before publishing |

Each phase produces a runnable result. Review can steer subsequent work, but routine implementation does not require a new permission request. Do not install speculative integrations or expand into a CRM, client portal, live chat or AI assistant without a concrete need.

**First implementation milestone:** a polished, responsive homepage prototype demonstrating the MAK ribbon globe, final typography direction, three real project candidates and the core service presentation. This establishes the visual standard before extending it across every page.

## 12. Definition of success

A visitor can quickly identify what MAK does, see relevant evidence and start an enquiry. The site feels original and recognizably MAK; its 3D has a clear visual purpose, and mobile visitors receive an equally intentional experience. Content managers can update business information without changing animation code. Speed, accessibility, working forms and credible content are assessed alongside visual polish.

Measure contact starts/completions, project-detail engagement and service-to-contact paths after launch when analytics is configured. Establish a baseline before claiming an uplift.

## 13. Implemented delivery ? 29 September 2026

Phases 1?5 are implemented locally: original MAK visual design, desktop/mobile homepage, optional mobile 3D, inner pages, curated source content, Laravel CMS, enquiries, notifications, metadata and redirects. The archive remains available separately; inaccessible pages and media are recorded rather than claimed as saved.

Phase 6 local validation includes 28 passing backend tests (280 assertions), a production asset build, responsive Chrome screenshots, keyboard/menu and reduced-motion checks, automated accessibility checks and Lighthouse measurement. Artifacts live in storage/app/review. Automated Chrome emulation does not establish real-device compatibility or field Core Web Vitals.

Remaining launch dependencies: production hosting/domain configuration, real SMTP delivery verification, client review of imported legal/business information and physical device testing. No deployment or real external notification has been performed. See README for exact local access and production configuration.

Final local Chrome mobile Lighthouse run: performance 95, accessibility 100, best practices 100. Largest Contentful Paint: 2.2 s; Total Blocking Time: 70 ms; Cumulative Layout Shift: 0.001. SEO is 69 because local robots/meta deliberately disable indexing. Automated accessibility recheck reports no violations on the checked public/admin pages; browser console reports no errors. These are local lab results, not production field measurements.
