# IFCG demo — adapted Laravel application

This is an independent copy of the existing ECOMMERCE-STORE Laravel application, adapted for IFCG. The original checkout, database, and environment are unchanged.

## Features
- Seven languages: English, Arabic (RTL), German, Portuguese, Russian, Spanish, French.
- Ten source-linked IFCG products and local copies of their public images.
- Product search, categories, detail pages and a persistent enquiry basket.
- Enquiries stored in a separate SQLite database, without stock deduction.
- Protected sales workspace with ownership, follow-up dates, notes and statuses.
- Versioned quotation creation with editable EGP/kg demo prices and weights.
- Immutable item/customer snapshots, integer minor-unit totals, approval and acceptance.
- Print-ready quotations with browser Print / Save as PDF, including Arabic.
- Email preview only: no external email is sent.
- Existing product/category administration retained through the original Laravel modules.

## Run locally
Use PHP 8.1+ with SQLite, mbstring, intl, fileinfo, openssl and the other extensions required by composer.lock.
Vendor dependencies were copied from the existing project.

From this directory:
1. Set .env to this copy's database/ifcg-demo.sqlite, not the original database.
2. Run php artisan migrate --force.
3. Run php artisan db:seed --class=IfcgDemoSeeder --force.
4. Run ./start-demo.ps1 (or pass -PhpPath to your PHP executable).
5. Open http://127.0.0.1:8197/.

Sales login: demo@ifcg.local

Set `IFCG_DEMO_PASSWORD` as a secret environment variable before running the demo seeder. Never commit the password or reuse it outside a disposable demo environment.

## Presentation flow
1. Change language from the header.
2. Browse the catalogue and add products to an enquiry.
3. Choose quantities in kg and enter fictional company/contact data.
4. Submit and note the reference.
5. Sign into Sales workspace, open the enquiry, assign an owner and follow-up.
6. Create a quotation in the customer's language and change the demo unit prices.
7. Approve the quotation, preview the email, or print/save a PDF.
8. Create another version from the enquiry; earlier quotes retain their snapshots.

## Boundaries
- Prices, SKUs and seeded clients are demonstration data, not real IFCG commercial terms.
- Product name translations and UI translations are drafts for client review.
- Public product specifications are copied as informational data, not independently certified.
- No live email integration, ERP connection, payment collection or commercial stock commitment.
- PDF uses the browser print dialog; this is not server-generated PDF delivery.
- Existing legacy inventory/order screens are retained but are not the B2B workflow.
- Old business-specific data conversion migrations were excluded from this copy.
- Use the explicit IfcgDemoSeeder, not the old default seed pipeline.
- Source product URLs and image attribution URLs are in data/catalog.json and data/images.json.



## Verification
52 integration checks passed via scripts/verify-demo.php against an in-memory SQLite database. The public enquiry-to-sales-to-Arabic-quotation flow was also exercised in the browser; 100 kg × 75 EGP + 50 kg × 110 EGP = 13,000 EGP. Desktop and mobile Arabic layouts were reviewed. PHPUnit development dependencies are absent in the copied vendor directory; the standalone integration runner was used instead.


## Corporate pages and request entry (2026-11-10)
- Added the original site's navigation structure and ten local pages: company profile, certificates, factory, brands, export, local sales, catalogues, articles, contact and demo privacy.
- Source URLs, selected published images, catalogue PDFs and article links are recorded in data/site-pages.json. Arabic and English page summaries are original adaptations; navigation and request controls cover all seven languages. Other locales currently use English summary text.
- Catalogue files and full articles open on the original website. Certificates are published source images, not a new certification claim. The privacy page describes this local demo and links separately to the original policy.
- Create-request icons in the header and floating action open the existing RFQ workflow. Products can now be selected directly in that form.
- Public requests are persisted as IfcgInquiry records and appear as New in the authenticated sales workspace; no purchase order, stock reservation or external email is created.
- 76 isolated integration checks pass, including public page routes, direct product selection markup and public-request visibility/details for sales.
- Browser test request RFQ-EE9EIVWYLC is intentionally labelled as a non-executable demo request.

## Commercial homepage content refresh
- Rewrote Arabic and English homepage copy for B2B purchasing: audience, product selection, supply channels, requirements and quotation CTA.
- Primary hero and journey actions now open the working supply-request form.
- Added factory imagery, certificate/catalogue links and IFCG-owned brand logos. New evidence-section labels are localized in all seven languages.
- No invented customer endorsements, certification claims, delivery commitments or performance statistics were added.

## Managed homepage media and footer (2026-11-10)
- Admin media manager: /sales/banners. Supports JPEG, PNG, WebP, MP4 and WebM up to 20 MB, optional video poster, localized text, internal CTA link, ordering, publication and hiding.
- Run migrations then scripts/seed-home-media.php to initialize the two demo slides. The seed is idempotent. Starter assets come from IFCG: factory image (1000x667), and https://ifcgegypt.com/wp-content/uploads/2026/09/IFCG-Frozen-Sweet-Potato-Fries.webm (1280x720). Uploading does not upscale media; 1920x1080 or larger originals are recommended.
- start-demo.ps1 sets PHP upload_max_filesize=24M and post_max_size=32M. Production hosting must configure equivalent limits and a persistent public_uploads disk.
- Slider navigation is manual and pauses video when leaving a slide. Videos have controls and do not autoplay. With no published slides, the original localized hero remains available.
- Footer includes official branding, corporate/business links, company contacts, privacy and sales access. The request invitation is omitted on home to avoid duplicating its existing closing CTA.
- scripts/verify-media.php passes 92 isolated checks including the existing suite, admin authorization, uploads, MIME rejection, unsafe CTA rejection, locale visibility, edits, hiding and fallback. Blade views compile successfully.

## Native 4K homepage video
- Replaced the default 720p clip with another official IFCG production video, served locally without transcoding: public/ifcg/images/official-production-4k.webm.
- Source: https://ifcgegypt.com/wp-content/uploads/2023/02/b-ed-music-v1-1-1.webm; WordPress media ID 7050. Native dimensions 3840x2160, duration 20.018 seconds, file size 14,482,718 bytes. Browser decoding verified.
- scripts/upgrade-home-video.php updates only the previous default asset path; custom uploads, slide order, copy and publication settings are preserved. Future seeds use the 4K asset.

## Product experience refresh (2026-11-11)
- Reviewed the official product gallery/enlargement flow and all ten product pages. Raw snapshots and parsed references are in data/product-sources and data/product-reference.json. Local image originals and secondary serving images are in public/ifcg/images/products.
- Added data/product-details.json with original Arabic/English summaries, source links and per-product preparation guidance. New detail copy falls back to English for the other locales. Existing admin-managed names, images, shelf life and cut values continue to take precedence.
- Rebuilt catalogue and product pages with an interactive gallery, native modal enlargement, keyboard navigation, storage/preparation/supply sections, related products, search result counts and preserved RFQ basket workflow.
- CSS transforms/transitions, IntersectionObserver and progressive cross-document View Transitions provide motion without a new frontend dependency. Reduced-motion preference disables animation; content remains usable without JavaScript. Native dialog restores focus, supports Escape and prevents background interaction.
- Numeric package sizes and nutritional/allergen claims are not inferred from ambiguous icons; the page asks the buyer to request confirmed packaging and technical specifications.
- Verified at 390px width with no horizontal overflow, desktop gallery switching and Escape, and adding a product through to the request form. scripts/verify-experience.php passes 136 isolated checks, including gallery assets for all ten products, language fallback, preparation differences and combined search/category filtering.

## Potato GIF gallery and section colors (2026-11-11)
- Added six actual animated GIFs (16 frames / two seconds each, 480px wide) extracted from the existing official IFCG 4K film, plus JPEG posters. Source provenance remains documented above. Total GIF payload is approximately 4.8 MiB; each is 0.58–1.02 MiB.
- Homepage and catalogue share the gallery. IntersectionObserver loads animation only while visible; offscreen/hidden-tab images revert to stills. A visible pause/play control and reduced-motion preference are supported. Without JS the gallery displays static posters.
- Sections and product categories use IFCG green/orange tints with readable dark copy. Color is supplementary to existing headings/category labels.
- scripts/build-potato-gifs.py reproduces assets using imageio-ffmpeg installed locally under ignored .tools/video. No runtime Python or FFmpeg dependency is required to serve them.
- Verified all six GIFs contain 16 frames, loaded in the browser, and pause replaces every animation with its poster. JavaScript syntax and Blade compilation pass.
