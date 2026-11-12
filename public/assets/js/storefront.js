(function () {
    const cartKey = "athath_cart_v1";
    const lastOrderKey = "xstore_last_order_v1";
    const routes = window.xstoreRoutes || {};
    const defaultCatalogCategorySlug = "fruits";

    const formatter = new Intl.NumberFormat("ar-EG", {
        style: "currency",
        currency: "EGP",
        maximumFractionDigits: 2
    });

    function money(value) {
        return formatter.format(Number(value) || 0);
    }

    function readCart() {
        try {
            const value = JSON.parse(localStorage.getItem(cartKey) || "[]");
            return Array.isArray(value) ? value : [];
        } catch (error) {
            return [];
        }
    }

    function writeCart(items) {
        localStorage.setItem(cartKey, JSON.stringify(items));
        renderAll();
    }

    function readLastOrder() {
        try {
            return JSON.parse(localStorage.getItem(lastOrderKey) || "null");
        } catch (error) {
            return null;
        }
    }

    function writeLastOrder(order) {
        localStorage.setItem(lastOrderKey, JSON.stringify({
            order,
            saved_at: new Date().toISOString()
        }));
    }

    function cartItemKey(item) {
        return `${item.product_id}:${item.variation_id || "base"}:${item.order_type || "sale"}`;
    }

    function escapeHtml(value) {
        return String(value || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function notify(message) {
        const toast = document.querySelector("[data-store-toast]");
        if (!toast) {
            return;
        }

        toast.textContent = message;
        toast.classList.add("is-visible");
        window.clearTimeout(notify.timer);
        notify.timer = window.setTimeout(() => toast.classList.remove("is-visible"), 2200);
    }

    function normalizeItem(rawItem, quantity) {
        const stock = Number(rawItem.stock) || 999999;

        return {
            product_id: Number(rawItem.product_id),
            variation_id: rawItem.variation_id ? Number(rawItem.variation_id) : null,
            name: rawItem.name || "فاكهة طازجة",
            price: Number(rawItem.price) || 0,
            stock,
            image: rawItem.image || "",
            url: rawItem.url || "#",
            order_type: rawItem.order_type === "rental" ? "rental" : "sale",
            service: rawItem.service || rawItem.order_type || "sale",
            rental_price: rawItem.rental_price ? Number(rawItem.rental_price) : null,
            deposit: rawItem.deposit ? Number(rawItem.deposit) : 0,
            rental_days: rawItem.rental_days ? Number(rawItem.rental_days) : null,
            quantity: Math.max(1, Math.min(Number(quantity) || 1, stock))
        };
    }

    function addToCart(rawItem, quantity) {
        const nextItem = normalizeItem(rawItem, quantity);

        if (!nextItem.product_id || nextItem.stock <= 0) {
            notify("هذا المنتج غير متاح حاليًا");
            return;
        }

        const cart = readCart();
        const key = cartItemKey(nextItem);
        const existing = cart.find((item) => cartItemKey(item) === key);

        if (existing) {
            existing.quantity = Math.min(existing.stock, existing.quantity + nextItem.quantity);
            existing.price = nextItem.price;
            existing.stock = nextItem.stock;
            existing.order_type = nextItem.order_type;
            existing.service = nextItem.service;
            existing.rental_price = nextItem.rental_price;
            existing.deposit = nextItem.deposit;
            existing.rental_days = nextItem.rental_days;
        } else {
            cart.push(nextItem);
        }

        writeCart(cart);
        notify("تمت إضافة المنتج للسلة");
    }

    function cartItemKey(item) {
        return `${item.product_id}:${item.variation_id || "base"}:${item.order_type || "sale"}:${item.rental_start_date || "no-start"}:${item.rental_end_date || "no-end"}`;
    }

    function normalizeItem(rawItem, quantity) {
        const stock = Number(rawItem.stock) || 999999;

        return {
            product_id: Number(rawItem.product_id),
            variation_id: rawItem.variation_id ? Number(rawItem.variation_id) : null,
            name: rawItem.name || "بدلة رجالي",
            price: Number(rawItem.price) || 0,
            stock,
            image: rawItem.image || "",
            url: rawItem.url || "#",
            order_type: rawItem.order_type === "rental" ? "rental" : "sale",
            service: rawItem.service || rawItem.order_type || "sale",
            rental_price: rawItem.rental_price ? Number(rawItem.rental_price) : null,
            deposit: rawItem.deposit ? Number(rawItem.deposit) : 0,
            rental_days: rawItem.rental_days ? Number(rawItem.rental_days) : null,
            rental_start_date: rawItem.rental_start_date || null,
            rental_end_date: rawItem.rental_end_date || null,
            quantity: Math.max(1, Math.min(Number(quantity) || 1, stock))
        };
    }

    function addToCart(rawItem, quantity) {
        const nextItem = normalizeItem(rawItem, quantity);

        if (!nextItem.product_id || nextItem.stock <= 0) {
            notify("هذا المنتج غير متاح حاليا");
            return false;
        }

        if (nextItem.order_type === "rental" && !nextItem.rental_start_date) {
            notify("اختر تاريخ الإيجار قبل إضافة البدلة للسلة");
            if (nextItem.url && !document.querySelector("[data-product-buy-form]")) {
                window.location.href = nextItem.url;
            }
            return false;
        }

        const cart = readCart();
        const key = cartItemKey(nextItem);
        const existing = cart.find((item) => cartItemKey(item) === key);

        if (existing) {
            existing.quantity = Math.min(existing.stock, existing.quantity + nextItem.quantity);
            existing.price = nextItem.price;
            existing.stock = nextItem.stock;
            existing.order_type = nextItem.order_type;
            existing.service = nextItem.service;
            existing.rental_price = nextItem.rental_price;
            existing.deposit = nextItem.deposit;
            existing.rental_days = nextItem.rental_days;
            existing.rental_start_date = nextItem.rental_start_date;
            existing.rental_end_date = nextItem.rental_end_date;
        } else {
            cart.push(nextItem);
        }

        writeCart(cart);
        notify("تمت إضافة المنتج للسلة");
        return true;
    }

    function apiUrl(path, params = {}) {
        const url = new URL(path, window.location.origin);
        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== "") {
                url.searchParams.set(key, value);
            }
        });
        return url.toString();
    }

    async function fetchJson(url) {
        const response = await fetch(url, { headers: { Accept: "application/json" } });
        const data = await response.json().catch(() => ({}));

        if (!response.ok || data.success === false || data.status === false) {
            throw new Error(data.message || "تعذر تحميل البيانات");
        }

        return data;
    }

    function collectionFrom(data) {
        return Array.isArray(data.data) ? data.data : [];
    }

    function productImage(product) {
        if (product.primary_image) {
            return product.primary_image;
        }

        if (Array.isArray(product.images) && product.images.length) {
            return product.images[0].url || "";
        }

        return "";
    }

    function activeVariations(product) {
        return Array.isArray(product.variations)
            ? product.variations.filter((variation) => variation.is_active !== false)
            : [];
    }

    function defaultVariation(product) {
        const variations = activeVariations(product);
        return variations.find((variation) => Number(variation.stock) > 0) || variations[0] || null;
    }

    function productPrice(product) {
        const variation = defaultVariation(product);
        if (variation) {
            return Number(variation.sale_price || variation.price || 0);
        }

        if (product.price_range && product.price_range.min) {
            return Number(product.price_range.min);
        }

        return Number(product.price || 0);
    }

    function productStock(product) {
        const variations = activeVariations(product);
        if (variations.length) {
            return variations.reduce((total, variation) => total + (Number(variation.stock) || 0), 0);
        }

        return Number(product.stock ?? product.quantity ?? 0);
    }

    function productHref(product) {
        return `/product/${encodeURIComponent(product.slug || product.id)}`;
    }

    function initials(name) {
        return String(name || "XS").trim().slice(0, 2) || "XS";
    }

    function freshIcon(item) {
        const text = `${item?.name || ""} ${item?.category?.name || ""} ${item?.slug || ""}`.toLowerCase();

        if (text.includes("banana") || text.includes("موز")) {
            return "ti-banana";
        }

        if (text.includes("orange") || text.includes("برتقال")) {
            return "ti-citrus";
        }

        if (text.includes("fruit") || text.includes("فاكه") || text.includes("تفاح") || text.includes("مانجو") || text.includes("عنب")) {
            return "ti-apple";
        }

        return "ti-apple";
    }

    function emptyState(icon, text) {
        return `
            <div class="store-empty">
                <i class="ti ${icon}"></i>
                <p>${escapeHtml(text)}</p>
            </div>
        `;
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
    }

    function wishlistUrl(productId) {
        return String(routes.wishlistToggle || "/account/wishlist/__PRODUCT__")
            .replace("__PRODUCT__", encodeURIComponent(productId));
    }

    function wishlistControlHtml(product, withText = false) {
        const productId = Number(product.id);
        const label = withText ? "<span>المفضلة</span>" : "";

        if (!productId) {
            return "";
        }

        if (!window.xstoreCustomer?.authenticated) {
            return `
                <a href="${routes.loginPage || "/account/login"}"
                    class="store-btn store-btn--ghost store-wishlist-btn"
                    aria-label="المفضلة">
                    <i class="ti ti-heart"></i>
                    ${label}
                </a>
            `;
        }

        return `
            <button type="button"
                class="store-btn store-btn--ghost store-wishlist-btn"
                data-wishlist-toggle
                data-product-id="${productId}"
                aria-label="المفضلة">
                <i class="ti ti-heart"></i>
                ${label}
            </button>
        `;
    }

    function productCardHtml(product) {
        const variation = defaultVariation(product);
        const price = productPrice(product);
        const stock = productStock(product);
        const image = productImage(product);
        const url = productHref(product);
        const canAdd = stock > 0 && (!product.has_variations || variation);
        const media = image
            ? `<img src="${escapeHtml(image)}" alt="${escapeHtml(product.name)}" loading="lazy" onerror="this.closest('.store-product-media').classList.add('is-fallback'); this.remove();">`
            : "";

        return `
            <article class="store-product-card">
                <a class="store-product-media" href="${url}" aria-label="${escapeHtml(product.name)}">
                    ${media}
                    <span class="store-product-fallback">
                        <i class="ti ${freshIcon(product)}"></i>
                        <em>${escapeHtml(initials(product.name))}</em>
                    </span>
                    <span class="store-badge">${stock > 0 ? "متاح" : "نفد"}</span>
                </a>
                <div class="store-product-card__body">
                    <div class="store-product-card__top">
                        <a href="${url}"><h3>${escapeHtml(product.name)}</h3></a>
                        <span>${escapeHtml(product.category?.name || "منتجات طازجة")}</span>
                    </div>
                    <p>${escapeHtml(String(product.description || "فاكهة طازجة متاحة للطلب من الفلاح للفاكهة.").replace(/<[^>]*>/g, "").slice(0, 92))}</p>
                    <div class="store-product-card__meta">
                        <strong>${money(price)}</strong>
                        <span class="${stock > 0 ? "is-available" : "is-empty"}">${stock > 0 ? "متاح" : "نفد المخزون"}</span>
                    </div>
                    <div class="store-product-card__actions">
                        ${canAdd ? `
                            <button type="button"
                                class="store-btn store-btn--primary"
                                data-cart-add
                                data-product-id="${Number(product.id)}"
                                data-variation-id="${variation ? Number(variation.id) : ""}"
                                data-name="${escapeHtml(product.name)}"
                                data-price="${price}"
                                data-stock="${variation ? Number(variation.stock) : stock}"
                                data-image="${escapeHtml(image)}"
                                data-url="${url}">
                                <i class="ti ti-shopping-cart-plus"></i>
                                أضف للسلة
                            </button>
                        ` : `
                            <a href="${url}" class="store-btn store-btn--primary">
                                <i class="ti ti-list-details"></i>
                                التفاصيل
                            </a>
                        `}
                        <a href="${url}" class="store-btn store-btn--ghost" aria-label="تفاصيل ${escapeHtml(product.name)}">
                            <i class="ti ti-eye"></i>
                        </a>
                        ${wishlistControlHtml(product)}
                    </div>
                </div>
            </article>
        `;
    }

    function categoryTileHtml(category) {
        const image = category.image ? `/storage/${category.image}` : "";
        const media = image
            ? `<img src="${escapeHtml(image)}" alt="${escapeHtml(category.name)}" loading="lazy" onerror="this.remove();">`
            : `<span>${escapeHtml(initials(category.name))}</span>`;

        return `
            <a href="/shop?category_id=${encodeURIComponent(category.id || "")}" class="store-category-tile">
                ${media}
                <div>
                    <strong>${escapeHtml(category.name)}</strong>
                    <small>قسم نشط</small>
                </div>
            </a>
        `;
    }

    function headerCategoryHtml(category) {
        return `<a href="/shop?category_id=${encodeURIComponent(category.id || "")}">${escapeHtml(category.name)}</a>`;
    }

    function departmentLinkHtml(category) {
        return `
            <a href="/shop?category_id=${encodeURIComponent(category.id || "")}" class="classic-department-link">
                <span>${escapeHtml(initials(category.name))}</span>
                <strong>${escapeHtml(category.name)}</strong>
                <i class="ti ti-chevron-left"></i>
            </a>
        `;
    }

    function heroProductHtml(product) {
        return `
            <a href="${productHref(product)}" class="store-hero-product">
                <span>
                    <i class="ti ${freshIcon(product)}"></i>
                    <em>${escapeHtml(initials(product.name))}</em>
                </span>
                <div>
                    <strong>${escapeHtml(product.name)}</strong>
                    <small>${money(productPrice(product))}</small>
                </div>
                <i class="ti ti-arrow-up-left"></i>
            </a>
        `;
    }

    async function loadHome() {
        const homeProducts = document.querySelector("[data-home-products]");
        const heroProducts = document.querySelector("[data-hero-products]");
        const categoriesTarget = document.querySelector("[data-home-categories]");
        const statsTarget = document.querySelector("[data-store-stats]");

        if (!homeProducts && !heroProducts && !categoriesTarget) {
            return;
        }

        try {
            const [productsData, categoriesData] = await Promise.all([
                fetchJson(apiUrl("/api/products", { category_slug: defaultCatalogCategorySlug, per_page: 8, with_variations: 1 })),
                fetchJson(apiUrl("/api/categories", { per_page: 8 }))
            ]);
            const products = collectionFrom(productsData);
            const categories = collectionFrom(categoriesData);

            if (homeProducts) {
                homeProducts.innerHTML = products.length
                    ? products.map(productCardHtml).join("")
                    : emptyState("ti-basket", "ابدأ بإضافة فواكه مفعلة من لوحة التحكم.");
            }

            if (heroProducts) {
                heroProducts.innerHTML = products.length
                    ? products.slice(0, 3).map(heroProductHtml).join("")
                    : emptyState("ti-basket", "أضف أول منتج طازج من لوحة التحكم ليظهر هنا.");
            }

            if (categoriesTarget) {
                categoriesTarget.innerHTML = categories.length
                    ? categories.map(departmentLinkHtml).join("")
                    : emptyState("ti-category", "لا توجد أقسام فاكهة مفعلة بعد.");
            }

            if (statsTarget) {
                const ready = products.filter((product) => productStock(product) > 0).length;
                statsTarget.innerHTML = `
                    <span><strong>${productsData.meta?.total ?? products.length}</strong> منتج</span>
                    <span><strong>${categoriesData.meta?.total ?? categories.length}</strong> قسم طازج</span>
                    <span><strong>${ready}</strong> جاهز للبيع</span>
                `;
            }
        } catch (error) {
            if (homeProducts) {
                homeProducts.innerHTML = emptyState("ti-alert-circle", error.message);
            }
            if (heroProducts) {
                heroProducts.innerHTML = emptyState("ti-alert-circle", error.message);
            }
            if (categoriesTarget) {
                categoriesTarget.innerHTML = emptyState("ti-alert-circle", error.message);
            }
        }
    }

    async function loadHeaderCategories() {
        const target = document.querySelector("[data-header-categories]");
        if (!target) {
            return;
        }

        try {
            const data = await fetchJson(apiUrl("/api/categories", { per_page: 6 }));
            const categories = collectionFrom(data);
            target.innerHTML = categories.map(headerCategoryHtml).join("");
        } catch (error) {
            target.innerHTML = "";
        }
    }

    async function loadCategoriesIntoFilter() {
        const select = document.querySelector("[data-category-select]");
        if (!select) {
            return;
        }

        const selected = new URLSearchParams(window.location.search).get("category") || "";
        const data = await fetchJson(apiUrl("/api/categories", { per_page: 100 }));
        const categories = collectionFrom(data);

        select.innerHTML = `<option value="">كل أقسام الفاكهة</option>` + categories.map((category) => `
            <option value="${escapeHtml(category.slug)}" ${selected === category.slug ? "selected" : ""}>
                ${escapeHtml(category.name)}
            </option>
        `).join("");
    }

    function shopParamsFromLocation() {
        const params = new URLSearchParams(window.location.search);
        return {
            q: params.get("q") || "",
            category: params.get("category") || "",
            sort: params.get("sort") || "latest",
            page: params.get("page") || 1
        };
    }

    function apiProductParams(params) {
        const apiParams = {
            per_page: 12,
            page: params.page || 1,
            with_variations: 1
        };

        if (params.q) {
            apiParams.search = params.q;
        }
        if (params.category) {
            apiParams.category_slug = params.category;
        } else {
            apiParams.category_slug = defaultCatalogCategorySlug;
        }

        if (params.sort === "price_asc") {
            apiParams.sort_by = "price";
            apiParams.sort_direction = "asc";
        } else if (params.sort === "price_desc") {
            apiParams.sort_by = "price";
            apiParams.sort_direction = "desc";
        } else if (params.sort === "name") {
            apiParams.sort_by = "name";
            apiParams.sort_direction = "asc";
        } else {
            apiParams.sort_by = "created_at";
            apiParams.sort_direction = "desc";
        }

        return apiParams;
    }

    async function loadShop(params = shopParamsFromLocation(), pushState = false) {
        const grid = document.querySelector("[data-products-grid]");
        if (!grid) {
            return;
        }

        const count = document.querySelector("[data-products-count]");
        const clear = document.querySelector("[data-clear-filters]");
        const pagination = document.querySelector("[data-products-pagination]");

        grid.innerHTML = emptyState("ti-loader-2", "جاري تحميل المنتجات الطازجة.");

        if (pushState) {
            const url = new URL(window.location.href);
            url.search = "";
            Object.entries(params).forEach(([key, value]) => {
                if (value && !(key === "sort" && value === "latest") && !(key === "page" && Number(value) === 1)) {
                    url.searchParams.set(key, value);
                }
            });
            window.history.pushState({}, "", url);
        }

        const data = await fetchJson(apiUrl("/api/products", apiProductParams(params)));
        const products = collectionFrom(data);
        const meta = data.meta || {};

        grid.innerHTML = products.length
            ? products.map(productCardHtml).join("")
            : emptyState("ti-search-off", "لا توجد منتجات طازجة مطابقة.");

        if (count) {
            count.textContent = `${meta.total ?? products.length} منتج طازج`;
        }

        if (clear) {
            clear.hidden = !(params.q || params.category || (params.sort && params.sort !== "latest"));
        }

        if (pagination) {
            const current = Number(meta.current_page || params.page || 1);
            const last = Number(meta.last_page || 1);
            pagination.innerHTML = last > 1 ? `
                <div class="store-pages">
                    <button class="store-btn store-btn--ghost" data-page-link="${Math.max(1, current - 1)}" ${current <= 1 ? "disabled" : ""}>
                        <i class="ti ti-chevron-right"></i>
                    </button>
                    <span>${current} / ${last}</span>
                    <button class="store-btn store-btn--ghost" data-page-link="${Math.min(last, current + 1)}" ${current >= last ? "disabled" : ""}>
                        <i class="ti ti-chevron-left"></i>
                    </button>
                </div>
            ` : "";
        }
    }

    function variationLabel(variation) {
        if (Array.isArray(variation.attributes) && variation.attributes.length) {
            return variation.attributes.map((attribute) => {
                const name = attribute.attribute_name || "";
                const value = attribute.option_value || "";
                return name ? `${name}: ${value}` : value;
            }).filter(Boolean).join(" - ");
        }

        return variation.sku || "اختيار";
    }

    function productDetailHtml(product) {
        const variations = activeVariations(product);
        const variation = defaultVariation(product);
        const image = productImage(product);
        const price = productPrice(product);
        const stock = productStock(product);
        const url = productHref(product);
        const media = image
            ? `<img src="${escapeHtml(image)}" alt="${escapeHtml(product.name)}" onerror="this.closest('.store-product-gallery__main').classList.add('is-fallback'); this.remove();">`
            : "";

        return `
            <div class="store-product-detail">
                <div class="store-product-gallery">
                    <div class="store-product-gallery__main">
                        ${media}
                        <span>
                            <i class="ti ${freshIcon(product)}"></i>
                            <em>${escapeHtml(initials(product.name))}</em>
                        </span>
                    </div>
                </div>
                <div class="store-product-summary">
                    <span class="store-eyebrow">${escapeHtml(product.category?.name || "منتجات طازجة")}</span>
                    <h1>${escapeHtml(product.name)}</h1>
                    <p>${escapeHtml(String(product.description || "فاكهة طازجة متاحة للطلب من الفلاح للفاكهة.").replace(/<[^>]*>/g, ""))}</p>
                    <div class="store-product-price">
                        <strong data-product-price>${money(price)}</strong>
                        <span class="${stock > 0 ? "is-available" : "is-empty"}">${stock > 0 ? "متاح للطلب" : "نفد المخزون"}</span>
                    </div>
                    <form class="store-buy-box"
                        data-product-buy-form
                        data-product-id="${Number(product.id)}"
                        data-name="${escapeHtml(product.name)}"
                        data-image="${escapeHtml(image)}"
                        data-url="${url}"
                        data-base-price="${price}"
                        data-stock="${variation ? Number(variation.stock || 0) : stock}">
                        ${variations.length ? `
                            <label>
                                <span>الاختيار</span>
                                <select name="variation_id" data-variation-select required>
                                    ${variations.map((item) => `
                                        <option value="${Number(item.id)}"
                                            data-price="${Number(item.sale_price || item.price || 0)}"
                                            data-stock="${Number(item.stock || 0)}"
                                            ${variation && Number(variation.id) === Number(item.id) ? "selected" : ""}>
                                            ${escapeHtml(variationLabel(item))} - ${money(item.sale_price || item.price || 0)}
                                        </option>
                                    `).join("")}
                                </select>
                            </label>
                        ` : ""}
                        <label>
                            <span>الكمية</span>
                            <input type="number" name="quantity" value="1" min="1" max="${Math.max(stock, 1)}" data-product-quantity>
                        </label>
                        <div class="store-buy-box__actions">
                            <button type="submit" class="store-btn store-btn--primary store-btn--large" data-action="add" ${stock <= 0 ? "disabled" : ""}>
                                <i class="ti ti-shopping-cart-plus"></i>
                                أضف للسلة
                            </button>
                            <button type="submit" class="store-btn store-btn--dark store-btn--large" data-action="buy" ${stock <= 0 ? "disabled" : ""}>
                                <i class="ti ti-bolt"></i>
                                شراء الآن
                            </button>
                            ${wishlistControlHtml(product, true)}
                        </div>
                    </form>
                </div>
            </div>
        `;
    }

    async function loadProductPage() {
        const page = document.querySelector("[data-product-page]");
        if (!page) {
            return;
        }

        const slug = page.dataset.productSlug;
        const detailTarget = document.querySelector("[data-product-detail]");
        const relatedTarget = document.querySelector("[data-related-products]");
        const relatedSection = document.querySelector("[data-related-section]");
        const breadcrumb = document.querySelector("[data-product-breadcrumb]");

        try {
            const data = await fetchJson(`/api/products/${encodeURIComponent(slug)}`);
            const product = data.data;
            detailTarget.innerHTML = productDetailHtml(product);
            document.title = `${product.name} | ?????? ?????? ????????`;
            if (breadcrumb) {
                breadcrumb.textContent = product.name;
            }

            const related = Array.isArray(data.related_products) ? data.related_products : [];
            if (related.length && relatedTarget && relatedSection) {
                relatedTarget.innerHTML = related.slice(0, 4).map(productCardHtml).join("");
                relatedSection.hidden = false;
            }

            document.querySelectorAll("[data-variation-select]").forEach(syncVariationState);
        } catch (error) {
            detailTarget.innerHTML = emptyState("ti-alert-circle", error.message);
        }
    }

    async function initStorefrontData() {
        await Promise.allSettled([
            loadHeaderCategories(),
            loadHome(),
            loadCategoriesIntoFilter().then(() => loadShop()),
            loadProductPage()
        ]);
    }

    function buttonItem(button) {
        return {
            product_id: button.dataset.productId,
            variation_id: button.dataset.variationId,
            name: button.dataset.name,
            price: button.dataset.orderType === "rental" && button.dataset.rentalPrice ? button.dataset.rentalPrice : button.dataset.price,
            stock: button.dataset.stock,
            image: button.dataset.image,
            url: button.dataset.url,
            order_type: button.dataset.orderType || "sale",
            service: button.dataset.service || button.dataset.orderType || "sale",
            rental_price: button.dataset.rentalPrice,
            deposit: button.dataset.deposit,
            rental_days: button.dataset.rentalDays
        };
    }

    async function toggleWishlist(button) {
        const productId = button.dataset.productId;

        if (!window.xstoreCustomer?.authenticated) {
            window.location.href = routes.loginPage || "/account/login";
            return;
        }

        button.disabled = true;

        try {
            const response = await fetch(wishlistUrl(productId), {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken()
                }
            });
            const data = await response.json().catch(() => ({}));

            if (response.status === 401 || response.status === 419) {
                window.location.href = routes.loginPage || "/account/login";
                return;
            }

            if (!response.ok || data.status === false) {
                throw new Error(data.message || "تعذر تحديث المفضلة");
            }

            button.classList.toggle("is-active", Boolean(data.active));
            notify(data.message || "تم تحديث المفضلة");
        } catch (error) {
            notify(error.message || "تعذر تحديث المفضلة");
        } finally {
            button.disabled = false;
        }
    }

    function cartTotals() {
        const cart = readCart();
        return cart.reduce((totals, item) => {
            const quantity = Number(item.quantity) || 0;
            const deposit = item.order_type === "rental" ? (Number(item.deposit) || 0) * quantity : 0;
            totals.count += quantity;
            totals.total += ((Number(item.price) || 0) * quantity) + deposit;
            return totals;
        }, { count: 0, total: 0 });
    }

    function renderCartCount() {
        const totals = cartTotals();
        document.querySelectorAll("[data-cart-count]").forEach((element) => {
            element.textContent = totals.count;
        });
    }

    function renderCartPage() {
        const target = document.querySelector("[data-cart-lines]");
        if (!target) {
            return;
        }

        const cart = readCart();
        const totals = cartTotals();

        document.querySelectorAll("[data-cart-items-count]").forEach((element) => {
            element.textContent = totals.count;
        });

        document.querySelectorAll("[data-cart-total]").forEach((element) => {
            element.textContent = money(totals.total);
        });

        if (!cart.length) {
            target.innerHTML = `
                <div class="store-empty">
                    <i class="ti ti-shopping-bag-x"></i>
                    <p>السلة فارغة الآن.</p>
                    <a href="/shop" class="store-btn store-btn--primary">تصفح المنتجات</a>
                </div>
            `;
            return;
        }

        target.innerHTML = cart.map((item) => {
            const key = escapeHtml(cartItemKey(item));
            const image = item.image
                ? `<img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}" onerror="this.remove();">`
                : escapeHtml(item.name).slice(0, 2);

            return `
                <div class="store-cart-line">
                    <a href="${escapeHtml(item.url)}" class="store-cart-line__media">${image}</a>
                    <div>
                        <a href="${escapeHtml(item.url)}"><h3>${escapeHtml(item.name)}</h3></a>
                        <small>${money(item.price)} للوحدة</small>
                        <span>${money((Number(item.price) || 0) * (Number(item.quantity) || 0))}</span>
                    </div>
                    <input type="number" min="1" max="${Number(item.stock) || 999999}" value="${Number(item.quantity) || 1}" data-cart-quantity data-key="${key}" aria-label="كمية ${escapeHtml(item.name)}">
                    <button type="button" class="store-cart-remove" data-cart-remove data-key="${key}" aria-label="حذف ${escapeHtml(item.name)}">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            `;
        }).join("");
    }

    function renderCheckoutSummary() {
        const rentalSection = document.querySelector('[data-rental-checkout]');
        if (rentalSection) rentalSection.hidden = !readCart().some(item => item.order_type === 'rental');
        const target = document.querySelector("[data-checkout-summary]");
        if (!target) {
            return;
        }

        const cart = readCart();
        const totals = cartTotals();

        if (!cart.length) {
            target.innerHTML = `
                <div class="store-empty">
                    <i class="ti ti-shopping-cart-off"></i>
                    <p>أضف منتجًا طازجًا للسلة قبل تأكيد الطلب.</p>
                    <a href="/shop" class="store-btn store-btn--primary">تصفح المنتجات</a>
                </div>
            `;
            return;
        }

        target.innerHTML = `
            ${cart.map((item) => `
                <div class="store-order-summary__item">
                    <div>
                        <strong>${escapeHtml(item.name)}</strong>
                        <small>${Number(item.quantity) || 1} × ${money(item.price)}</small>
                    </div>
                    <span>${money((Number(item.price) || 0) * (Number(item.quantity) || 0))}</span>
                </div>
            `).join("")}
            <div class="store-order-summary__row store-order-summary__total">
                <span>الإجمالي</span>
                <strong>${money(totals.total)}</strong>
            </div>
        `;
    }

    function statusLabel(value) {
        return {
            pending: "قيد الاستلام",
            processing: "قيد التجهيز",
            completed: "مكتمل",
            cancelled: "ملغي"
        }[value] || value || "قيد الاستلام";
    }

    function paymentStatusLabel(value) {
        return {
            unpaid: "غير مدفوع",
            paid: "مدفوع",
            refunded: "مسترد"
        }[value] || value || "غير مدفوع";
    }

    function paymentMethodLabel(value) {
        return {
            cash_on_delivery: "الدفع عند الاستلام",
            whatsapp: "واتساب",
            vodafone_cash: "فودافون كاش",
            instapay: "إنستاباي"
        }[value] || value || "الدفع عند الاستلام";
    }

    function renderOrderSuccessPage() {
        const target = document.querySelector("[data-order-success]");
        if (!target) {
            return;
        }

        const saved = readLastOrder();
        let order = saved && saved.order ? saved.order : null;
        const queryOrder = new URLSearchParams(window.location.search).get("order");
        const savedOrderRef = order ? String(order.order_number || order.id || "") : "";

        if (order && queryOrder && savedOrderRef !== queryOrder) {
            order = null;
        }

        if (!order) {
            target.innerHTML = `
                <div class="store-success-card">
                    <div class="store-success-icon store-success-icon--muted">
                        <i class="ti ti-receipt"></i>
                    </div>
                    <div>
                        <span class="store-eyebrow">تم إنشاء الطلب</span>
                        <h2>${queryOrder ? `رقم الطلب ${escapeHtml(queryOrder)}` : "تفاصيل الطلب غير محفوظة على هذا الجهاز"}</h2>
                        <p>إذا تم إنشاء الطلب من جهاز آخر، ستجده داخل لوحة التحكم. يمكنك متابعة التسوق أو إرسال رقم الطلب لفريق المتجر.</p>
                    </div>
                    <div class="store-success-actions">
                        <a href="/shop" class="store-btn store-btn--primary">
                            <i class="ti ti-layout-grid"></i>
                            متابعة التسوق
                        </a>
                        <a href="/checkout" class="store-btn store-btn--ghost">
                            <i class="ti ti-cash"></i>
                            طلب جديد
                        </a>
                    </div>
                </div>
            `;
            return;
        }

        const items = Array.isArray(order.items) ? order.items : [];
        const customer = order.customer || {};
        const orderNumber = order.order_number || queryOrder || order.id || "";
        const message = encodeURIComponent(`مرحبا، أريد متابعة الطلب رقم ${orderNumber}`);

        target.innerHTML = `
            <div class="store-success-card">
                <div class="store-success-icon">
                    <i class="ti ti-circle-check"></i>
                </div>
                <div class="store-success-card__main">
                    <span class="store-eyebrow">تم إنشاء الطلب</span>
                    <h2>طلبك رقم ${escapeHtml(orderNumber)}</h2>
                    <p>تم استلام الطلب وتسجيله داخل لوحة التحكم. احتفظ برقم الطلب للمتابعة.</p>
                    <div class="store-success-actions">
                        <a href="/shop" class="store-btn store-btn--primary">
                            <i class="ti ti-layout-grid"></i>
                            متابعة التسوق
                        </a>
                        <a href="https://wa.me/?text=${message}" class="store-btn store-btn--dark" target="_blank" rel="noopener">
                            <i class="ti ti-brand-whatsapp"></i>
                            متابعة عبر واتساب
                        </a>
                    </div>
                </div>
            </div>

            <div class="store-success-grid">
                <section class="store-success-panel">
                    <h2>حالة الطلب</h2>
                    <div class="store-success-facts">
                        <div>
                            <span>حالة الطلب</span>
                            <strong>${escapeHtml(statusLabel(order.status))}</strong>
                        </div>
                        <div>
                            <span>حالة الدفع</span>
                            <strong>${escapeHtml(paymentStatusLabel(order.payment_status))}</strong>
                        </div>
                        <div>
                            <span>طريقة الدفع</span>
                            <strong>${escapeHtml(paymentMethodLabel(order.payment_method))}</strong>
                        </div>
                        <div>
                            <span>الإجمالي</span>
                            <strong>${money(order.total)}</strong>
                        </div>
                    </div>
                </section>

                <section class="store-success-panel">
                    <h2>بيانات العميل</h2>
                    <div class="store-success-list">
                        <span>${escapeHtml(customer.name || "غير محدد")}</span>
                        <span>${escapeHtml(customer.phone || "لا يوجد هاتف")}</span>
                        <span>${escapeHtml(customer.city || "المدينة غير محددة")}</span>
                        <span>${escapeHtml(customer.address || "العنوان غير محدد")}</span>
                    </div>
                </section>
            </div>

            <section class="store-success-panel">
                <h2>منتجات الطلب</h2>
                <div class="store-success-items">
                    ${items.length ? items.map((item) => `
                        <div class="store-success-item">
                            <div>
                                <strong>${escapeHtml(item.name || "منتج طازج")}</strong>
                                <small>${Number(item.quantity) || 1} × ${money(item.price)}</small>
                            </div>
                            <span>${money(item.subtotal)}</span>
                        </div>
                    `).join("") : "<p>لا توجد منتجات محفوظة في هذا المتصفح.</p>"}
                </div>
            </section>
        `;
    }

    function renderAll() {
        renderCartCount();
        renderCartPage();
        renderCheckoutSummary();
        renderOrderSuccessPage();
    }

    function updateCartQuantity(key, quantity) {
        const cart = readCart();
        const item = cart.find((entry) => cartItemKey(entry) === key);

        if (!item) {
            return;
        }

        item.quantity = Math.max(1, Math.min(Number(quantity) || 1, Number(item.stock) || 999999));
        writeCart(cart);
    }

    function removeCartItem(key) {
        writeCart(readCart().filter((item) => cartItemKey(item) !== key));
        notify("تم حذف المنتج من السلة");
    }

    function productFormItem(form) {
        const select = form.querySelector("[data-variation-select]");
        const selected = select ? select.selectedOptions[0] : null;
        const quantity = Number(form.querySelector("[data-product-quantity]")?.value || 1);
        const rentalStart = form.querySelector("[data-rental-start]")?.value || "";
        const rentalEnd = form.querySelector("[data-rental-end]")?.value || "";

        return {
            item: {
                product_id: form.dataset.productId,
                variation_id: selected ? selected.value : "",
                name: form.dataset.name,
                price: form.dataset.orderType === "rental" && form.dataset.rentalPrice ? form.dataset.rentalPrice : (selected ? selected.dataset.price : form.dataset.basePrice),
                stock: selected ? selected.dataset.stock : form.dataset.stock,
                image: form.dataset.image,
                url: form.dataset.url,
                order_type: form.dataset.orderType || "sale",
                service: form.dataset.service || form.dataset.orderType || "sale",
                rental_price: form.dataset.rentalPrice,
                deposit: form.dataset.deposit,
                rental_days: form.dataset.rentalDays,
                rental_start_date: rentalStart,
                rental_end_date: rentalEnd
            },
            quantity
        };
    }

    function syncVariationState(select) {
        const form = select.closest("[data-product-buy-form]");
        if (!form) {
            return;
        }

        const selected = select.selectedOptions[0];
        const price = Number(selected?.dataset.price || form.dataset.basePrice || 0);
        const stock = Number(selected?.dataset.stock || form.dataset.stock || 0);
        const priceTarget = document.querySelector("[data-product-price]");
        const quantityInput = form.querySelector("[data-product-quantity]");

        if (priceTarget) {
            priceTarget.textContent = money(price);
        }

        if (quantityInput) {
            quantityInput.max = Math.max(stock, 1);
            quantityInput.value = Math.min(Number(quantityInput.value) || 1, Math.max(stock, 1));
        }
    }

    async function checkRentalAvailability(form) {
        if (!form || form.dataset.orderType !== "rental") {
            return true;
        }

        const message = document.querySelector("[data-rental-availability-message]");
        const start = form.querySelector("[data-rental-start]")?.value || "";
        const end = form.querySelector("[data-rental-end]")?.value || "";
        const quantity = form.querySelector("[data-product-quantity]")?.value || 1;
        const select = form.querySelector("[data-variation-select]");
        const variationId = select?.value || "";

        if (!start) {
            if (message) {
                message.textContent = "اختر تاريخ بداية الإيجار لفحص الإتاحة.";
                message.className = "text-muted";
            }
            return false;
        }

        if (message) {
            message.textContent = "جاري فحص الإتاحة...";
            message.className = "text-muted";
        }

        try {
            const response = await fetch(apiUrl(`/rental-availability/${form.dataset.productId}`, {
                start_date: start,
                end_date: end,
                quantity,
                variation_id: variationId
            }), { headers: { Accept: "application/json" } });
            const data = await response.json().catch(() => ({}));
            const available = response.ok && data.available !== false;

            if (message) {
                message.textContent = data.message || (available ? "البدلة متاحة." : "البدلة غير متاحة في هذا التاريخ.");
                message.className = available ? "text-success" : "text-danger";
            }

            return available;
        } catch (error) {
            if (message) {
                message.textContent = "تعذر فحص الإتاحة الآن.";
                message.className = "text-danger";
            }
            return false;
        }
    }

    async function submitCheckout(form) {
        const message = document.querySelector("[data-checkout-message]");
        const result = document.querySelector("[data-checkout-result]");
        const cart = readCart();

        if (!cart.length) {
            if (message) {
                message.textContent = "السلة فارغة. أضف منتجًا طازجًا قبل تأكيد الطلب.";
                message.classList.remove("is-success");
            }
            return;
        }

        if (!form.reportValidity()) {
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        const checkoutItems = cart
            .map((item) => ({
                product_id: Number(item.product_id),
                variation_id: item.variation_id ? Number(item.variation_id) : null,
                quantity: Number(item.quantity) || 1,
                order_type: item.order_type === "rental" ? "rental" : "sale",
                rental_start_date: item.order_type === "rental" ? (formData.get("rental_start_date") || item.rental_start_date || null) : null,
                rental_end_date: item.order_type === "rental" ? (formData.get("rental_end_date") || item.rental_end_date || null) : null
            }))
            .filter((item) => Number.isInteger(item.product_id) && item.product_id > 0 && item.quantity > 0);

        if (!checkoutItems.length) {
            writeCart([]);
            if (message) {
                message.textContent = "السلة تحتوي على بيانات قديمة. أضف المنتجات مرة أخرى ثم أتم الطلب.";
                message.classList.remove("is-success");
            }
            return;
        }

        const payload = {
            name: formData.get("name"),
            phone: formData.get("phone"),
            email: formData.get("email") || null,
            address: formData.get("address"),
            city: formData.get("city") || null,
            notes: formData.get("notes") || null,
            payment_method: formData.get("payment_method") || "cash_on_delivery",
            payment_reference: formData.get("payment_reference") || null,
            coupon_code: formData.get("coupon_code") || null,
            items: checkoutItems
        };

        const requestBody = new FormData();
        Object.entries(payload).forEach(([key, value]) => {
            if (key !== "items" && value !== null && value !== undefined) {
                requestBody.append(key, value);
            }
        });
        const paymentReceipt = formData.get("payment_receipt");
        if (["vodafone_cash", "instapay"].includes(payload.payment_method)
            && paymentReceipt instanceof File
            && paymentReceipt.size > 0) {
            requestBody.append("payment_receipt", paymentReceipt);
        }
        checkoutItems.forEach((item, index) => {
            Object.entries(item).forEach(([key, value]) => {
                if (value !== null && value !== undefined) {
                    requestBody.append(`items[${index}][${key}]`, value);
                }
            });
        });

        if (message) {
            message.textContent = "جاري إرسال الطلب...";
            message.classList.add("is-success");
        }

        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const response = await fetch(routes.checkoutApi || "/api/checkout", {
                method: "POST",
                headers: {
                    "Accept": "application/json"
                },
                body: requestBody
            });
            const data = await response.json().catch(() => ({}));

            if (!response.ok || data.status === false) {
                const errorMessage = data.message || "تعذر إرسال الطلب";

                if (
                    response.status === 422
                    && (
                        errorMessage.includes("غير متاح")
                        || errorMessage.includes("غير صالحة")
                        || errorMessage.includes("invalid")
                    )
                ) {
                    writeCart([]);
                }

                throw new Error(errorMessage);
            }

            writeCart([]);
            form.reset();

            const order = data.data && data.data.order ? data.data.order : {};
            writeLastOrder(order);
            if (message) {
                message.textContent = "تم استلام الطلب بنجاح.";
                message.classList.add("is-success");
            }
            if (result) {
                result.hidden = false;
                result.innerHTML = `
                    <strong>تم إنشاء الطلب</strong>
                    <p>رقم الطلب: ${escapeHtml(order.order_number || order.id || "")}</p>
                    <a href="/shop" class="store-inline-link">متابعة التسوق</a>
                `;
            }
            notify("تم إرسال الطلب إلى لوحة التحكم");
            const successPage = routes.orderSuccessPage || "/order-success";
            const orderRef = order.order_number || order.id || "";
            window.location.href = orderRef
                ? `${successPage}?order=${encodeURIComponent(orderRef)}`
                : successPage;
        } catch (error) {
            if (message) {
                message.textContent = error.message || "تعذر إرسال الطلب";
                message.classList.remove("is-success");
            }
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    }

    document.addEventListener("click", (event) => {
        const wishlistButton = event.target.closest("[data-wishlist-toggle]");
        if (wishlistButton) {
            event.preventDefault();
            toggleWishlist(wishlistButton);
            return;
        }

        const addButton = event.target.closest("[data-cart-add]");
        if (addButton) {
            event.preventDefault();
            addToCart(buttonItem(addButton), Number(addButton.dataset.quantity || 1));
            return;
        }

        const buyButton = event.target.closest("[data-buy-now]");
        if (buyButton) {
            event.preventDefault();
            addToCart(buttonItem(buyButton), Number(buyButton.dataset.quantity || 1));
            window.location.href = routes.checkoutPage || "/checkout";
            return;
        }

        const removeButton = event.target.closest("[data-cart-remove]");
        if (removeButton) {
            event.preventDefault();
            removeCartItem(removeButton.dataset.key);
            return;
        }

        const pageButton = event.target.closest("[data-page-link]");
        if (pageButton) {
            event.preventDefault();
            const params = shopParamsFromLocation();
            params.page = Number(pageButton.dataset.pageLink) || 1;
            loadShop(params, true).catch((error) => notify(error.message));
            return;
        }
    });

    document.addEventListener("change", (event) => {
        const quantityInput = event.target.closest("[data-cart-quantity]");
        if (quantityInput) {
            updateCartQuantity(quantityInput.dataset.key, quantityInput.value);
            return;
        }

        const variationSelect = event.target.closest("[data-variation-select]");
        if (variationSelect) {
            syncVariationState(variationSelect);
            checkRentalAvailability(variationSelect.closest("[data-product-buy-form]"));
        }

        const rentalDateInput = event.target.closest("[data-rental-start], [data-rental-end]");
        if (rentalDateInput) {
            checkRentalAvailability(rentalDateInput.closest("[data-product-buy-form]"));
        }
    });

    document.addEventListener("submit", async (event) => {
        const buyForm = event.target.closest("[data-product-buy-form]");
        if (buyForm) {
            event.preventDefault();
            const available = await checkRentalAvailability(buyForm);
            if (buyForm.dataset.orderType === "rental" && !available) {
                return;
            }
            const data = productFormItem(buyForm);
            const added = addToCart(data.item, data.quantity);

            if (added && event.submitter?.dataset.action === "buy") {
                window.location.href = routes.checkoutPage || "/checkout";
            }
            return;
        }

        const checkoutForm = event.target.closest("[data-checkout-form]");
        if (checkoutForm) {
            event.preventDefault();
            submitCheckout(checkoutForm);
            return;
        }

        const filterForm = event.target.closest("[data-store-filter]");
        if (filterForm) {
            event.preventDefault();
            const formData = new FormData(filterForm);
            const params = {
                q: formData.get("q") || "",
                category: formData.get("category") || "",
                sort: formData.get("sort") || "latest",
                page: 1
            };
            loadShop(params, true).catch((error) => notify(error.message));
        }
    });

    function initShellInteractions() {
        const header = document.querySelector("[data-store-header]");
        const drawer = document.querySelector("[data-mobile-drawer]");
        const overlay = document.querySelector("[data-mobile-overlay]");
        const openButton = document.querySelector("[data-mobile-menu-toggle]");
        const closeButton = document.querySelector("[data-mobile-menu-close]");

        const syncHeader = () => {
            if (header) {
                header.classList.toggle("is-scrolled", window.scrollY > 12);
            }
        };

        const openDrawer = () => {
            if (!drawer || !overlay) {
                return;
            }

            drawer.classList.add("is-open");
            drawer.setAttribute("aria-hidden", "false");
            overlay.hidden = false;
            openButton?.setAttribute("aria-expanded", "true");
            document.body.classList.add("drawer-open");
        };

        const closeDrawer = () => {
            if (!drawer || !overlay) {
                return;
            }

            drawer.classList.remove("is-open");
            drawer.setAttribute("aria-hidden", "true");
            overlay.hidden = true;
            openButton?.setAttribute("aria-expanded", "false");
            document.body.classList.remove("drawer-open");
        };

        syncHeader();
        window.addEventListener("scroll", syncHeader, { passive: true });
        openButton?.addEventListener("click", openDrawer);
        closeButton?.addEventListener("click", closeDrawer);
        overlay?.addEventListener("click", closeDrawer);
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                closeDrawer();
            }
        });
    }

    function initPasswordToggles() {
        document.querySelectorAll("[data-password-toggle]").forEach((button) => {
            const input = document.querySelector(button.dataset.passwordToggle);
            if (!input) {
                return;
            }

            button.addEventListener("click", () => {
                const isHidden = input.type === "password";
                const icon = button.querySelector("i");
                input.type = isHidden ? "text" : "password";
                button.setAttribute("aria-label", isHidden ? "إخفاء كلمة المرور" : "إظهار كلمة المرور");

                if (icon) {
                    icon.classList.toggle("ti-eye", !isHidden);
                    icon.classList.toggle("ti-eye-off", isHidden);
                }
            });
        });
    }

    document.querySelectorAll("[data-variation-select]").forEach(syncVariationState);
    initShellInteractions();
    initPasswordToggles();
    initStorefrontData();
    renderAll();
})();
