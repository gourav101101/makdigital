# Legacy content review

Source: https://mdarena.in/ — public content archived on 2026-09-29.

The archive preserves source text, including errors, for reference. It is not approved final website copy.

## Archive results

- 78 requested page URLs saved, resolving to 64 distinct final page URLs.
- All 78 records imported into the local Laravel `legacy_pages` table.
- 484 assets saved, totaling 30,493,304 bytes; every successful manifest entry was verified on disk.
- 16 page URLs were unavailable: 12 returned HTTP 500 and 4 returned HTTP 404.
- 24 asset URLs returned HTTP 404. Exact URLs are listed in `storage/app/legacy/mdarena/report.json`.
- No discovered page URLs remain queued. This describes completion of the public crawl, not access to private or unlinked website data.
- Inline data URIs and JavaScript object-URL expressions are excluded from download failures; the original references remain in archived HTML and the discovery audit.

## Main content groups

- Company: homepage, about, mission, vision, values, process and testimonials.
- Services: business consultation, web development, app development, ecommerce, SEO, PPC, content marketing, social media and digital marketing.
- Industries and articles: healthcare, education, branding, digital marketing trends.
- Business pages: careers, contact, portfolio, FAQs, support, privacy and terms.
- Other discovered content: category/tag/author archives, pagination, old redirect URLs, sample pages and theme demo portfolio entries.

## Contact information found

- Phone: +91 98264 70100.
- Email: info@mdarena.in.
- Footer email: makdigitalind@gmail.com.
- Address: House no. 68-C, near Brilliant Aura, Bulandshahr, Scheme Number 136, Indore, Madhya Pradesh 452010.

Verify these before publication. The contact body also contains `makdigitalind@gmai.com`, while the footer uses `gmail.com`; the raw archive retains both.

## Items needing editorial review

- Contact page contains stray Elementor-style placeholder text beginning “Add Your Heading”.
- About page contains unrelated wording “bitcoin tumbler”. Do not carry that into new copy without review.
- Several portfolio entries, sample pages, and taxonomy labels appear to be leftover theme content.
- Old and current URLs can redirect to the same page. Archive counts represent requested URLs, not unique editorial pages; consult `effective_url` before mapping new routes.
- Copyright footer says 2024. Statistics, testimonials, project descriptions and legal pages need a publication review.

## Coverage limits

The crawler discovers public pages via sitemaps and internal links, saves their HTML and extracted content, and downloads referenced same-domain assets including CSS dependencies. External links and externally hosted media are inventoried. It does not log into WordPress, export its database, retrieve private submissions, or execute JavaScript/API interactions. Original HTML files are historical evidence, not a fully functional offline mirror. `report.json` lists download failures and remaining URLs.
