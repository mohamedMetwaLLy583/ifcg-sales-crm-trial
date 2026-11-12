
## Catalogue-led storefront (2026-11-11)
- Kilo-inspired utility header, category-aware search, illustrated navigation and product submenus using active catalogue records.
- Hover, click, keyboard, outside-click and Escape navigation; responsive RTL/LTR layout. Brand, supply and company menus retained.
- Homepage media preserved in a rounded showcase with two linked product promotions. Product cards use clearer enquiry actions.
- Existing 136 integration checks pass; search assertion now checks result cards separately from shared navigation. Arabic desktop/mobile menu and product navigation verified at 390px with no horizontal overflow.


## Illustrations and automatic slider (2026-11-11)
- Generated botanical food artwork stored locally at public/ifcg/images/harvest-illustration.png; used in the homepage slider and product guide. This is decorative illustration, not a product photograph.
- scripts/seed-illustration.php idempotently adds the artwork through the existing media model so it can be edited or hidden in banner management.
- Slider advances every 1500ms, wraps, and provides pause/resume. Hover, focus, playing video, hidden tab and reduced-motion preferences pause automatic movement.
- Verified 92 media/integration checks and scripts/verify-slider.cjs timing/interaction checks; inspected Arabic illustration slide in browser.

