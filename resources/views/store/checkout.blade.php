@extends('store.layout')

@section('title', 'إتمام الطلب | اثاث للديكور')
@section('meta_description', 'أكمل بيانات التوصيل والدفع لتأكيد طلبك.')

@php
    $vodafoneCashNumber = \App\Models\StoreSetting::get('vodafone_cash_number');
    $instapayAddress = \App\Models\StoreSetting::get('instapay_address');
@endphp

@section('content')
    <section class="checkout-hero">
        <div class="store-container">
            
            <h1>إتمام الطلب</h1>
            <p>بيانات التوصيل وطريقة الدفع.</p>


        </div>
    </section>

    <section class="store-section">
        <div class="store-container">
            <div class="checkout-layout">
                <form class="checkout-form store-checkout-form" data-checkout-form enctype="multipart/form-data">
                    <div class="checkout-section">
                        <div class="checkout-section__head">
                            <i class="ti ti-user"></i>
                            <div>
                                <h2>معلومات شخصية</h2>
                                <span>بيانات التواصل الأساسية</span>
                            </div>
                        </div>
                        <div class="store-form-grid">
                            <label>
                                <span>الاسم</span>
                                <input class="input" type="text" name="name" value="{{ auth()->user()?->name }}" required autocomplete="name" placeholder="اسم العميل">
                            </label>
                            <label>
                                <span>رقم الهاتف</span>
                                <input class="input" type="tel" name="phone" value="{{ auth()->user()?->phone }}" required autocomplete="tel" placeholder="01xxxxxxxxx">
                            </label>
                            <label>
                                <span>البريد الإلكتروني</span>
                                <input class="input" type="email" name="email" value="{{ auth()->check() && !\Illuminate\Support\Str::endsWith((string) auth()->user()->email, '@xstore.local') ? auth()->user()->email : '' }}" autocomplete="email" placeholder="اختياري">
                            </label>
                        </div>
                    </div>

                    <div class="checkout-section">
                        <div class="checkout-section__head">
                            <i class="ti ti-map-pin"></i>
                            <div>
                                <h2>عنوان التوصيل</h2>
                                <span>تكلفة الشحن تتغير حسب المدينة</span>
                            </div>
                        </div>
                        <div class="store-form-grid">
                            <label>
                                <span>المدينة</span>
                                <input class="input" type="text" name="city" value="{{ auth()->user()?->city }}" autocomplete="address-level2" placeholder="اختياري" data-shipping-city>
                                <small class="checkout-shipping-note" data-shipping-cost-message>اكتب المدينة لحساب تكلفة الشحن</small>
                            </label>
                        </div>
                        <label>
                            <span>العنوان التفصيلي</span>
                            <textarea class="textarea" name="address" required rows="3" autocomplete="street-address" placeholder="العنوان التفصيلي">{{ auth()->user()?->address }}</textarea>
                        </label>
                        <label>
                            <span>ملاحظات</span>
                            <textarea class="textarea" name="notes" rows="3" placeholder="أي تفاصيل إضافية للطلب"></textarea>
                        </label>
                    </div>

                    <div class="checkout-section" data-rental-checkout hidden>
                        <div class="checkout-section__head">
                            <i class="ti ti-calendar-event"></i>
                            <div>
                                <h2>بيانات الإيجار</h2>
                                <span>مواعيد تسليم وإرجاع المنتجات المؤجرة</span>
                            </div>
                        </div>
                        <div class="store-form-grid">
                            <label>
                                <span>تاريخ المناسبة / بداية الإيجار</span>
                                <input class="input" type="date" name="rental_start_date" min="{{ now()->toDateString() }}">
                            </label>
                            <label>
                                <span>تاريخ الإرجاع المتوقع</span>
                                <input class="input" type="date" name="rental_end_date" min="{{ now()->toDateString() }}">
                            </label>
                        </div>
                    </div>

                    <div class="checkout-section">
                        <div class="checkout-section__head">
                            <i class="ti ti-credit-card"></i>
                            <div>
                                <h2>طريقة الدفع</h2>
                                <span>اختر الطريقة الأنسب لك</span>
                            </div>
                        </div>
                        <div class="checkout-payment-options store-payment-options">
                            <label>
                                <input type="radio" name="payment_method" value="cash_on_delivery" checked>
                                <span><i class="ti ti-cash"></i><strong>الدفع عند الاستلام</strong><small>ادفع عند وصول الطلب</small></span>
                            </label>
                            <label>
                                <input type="radio" name="payment_method" value="whatsapp">
                                <span><i class="ti ti-brand-whatsapp"></i><strong>واتساب</strong><small>تأكيد الطلب عبر واتساب</small></span>
                            </label>
                            <label class="{{ $vodafoneCashNumber ? '' : 'is-unavailable' }}">
                                <input type="radio" name="payment_method" value="vodafone_cash" {{ $vodafoneCashNumber ? '' : 'disabled' }}>
                                <span>
                                    <i class="ti ti-device-mobile-dollar"></i>
                                    <strong>فودافون كاش</strong>
                                    @if ($vodafoneCashNumber)
                                        <small>حوّل إلى <bdi dir="ltr">{{ $vodafoneCashNumber }}</bdi></small>
                                    @else
                                        <small>غير متاح حالياً</small>
                                    @endif
                                </span>
                            </label>
                            <label class="{{ $instapayAddress ? '' : 'is-unavailable' }}">
                                <input type="radio" name="payment_method" value="instapay" {{ $instapayAddress ? '' : 'disabled' }}>
                                <span>
                                    <i class="ti ti-building-bank"></i>
                                    <strong>إنستاباي</strong>
                                    @if ($instapayAddress)
                                        <small>حوّل إلى <bdi dir="ltr">{{ $instapayAddress }}</bdi></small>
                                    @else
                                        <small>غير متاح حالياً</small>
                                    @endif
                                </span>
                            </label>
                        </div>
                        <div class="store-form-grid mt-3" data-payment-reference hidden>
                            <label>
                                <span>رقم العملية / مرجع التحويل</span>
                                <input class="input" type="text" name="payment_reference" maxlength="120" autocomplete="off" placeholder="حوّل المبلغ أولاً ثم اكتب رقم العملية">
                                <small>سيظل الطلب غير مدفوع حتى يراجع المتجر التحويل ويؤكده.</small>
                            </label>
                            <label data-payment-receipt hidden>
                                <span>صورة إيصال التحويل</span>
                                <input class="input" type="file" name="payment_receipt" accept="image/jpeg,image/png,image/webp">
                                <small>الصورة مطلوبة لإرسال الطلب. JPG أو PNG أو WEBP، بحد أقصى 2 ميجابايت.</small>
                                <img data-payment-receipt-preview alt="معاينة إيصال التحويل" hidden style="max-width: 180px; max-height: 180px; border-radius: 8px; object-fit: contain;">
                            </label>
                        </div>
                    </div>

                    <div class="checkout-section">
                        <div class="checkout-section__head">
                            <i class="ti ti-ticket"></i>
                            <div>
                                <h2>كود الخصم</h2>
                                <span>اختياري، وسيتم التحقق منه قبل إنشاء الطلب</span>
                            </div>
                        </div>
                        <label>
                            <span>الكوبون</span>
                            <input class="input" type="text" name="coupon_code" autocomplete="off" placeholder="أدخل كود الخصم">
                        </label>
                    </div>

                    <div class="store-form-message" data-checkout-message></div>

                    <button type="submit" class="btn-primary checkout-submit">
                        <i class="ti ti-send"></i>
                        تأكيد الطلب
                    </button>
                </form>

                <aside class="checkout-summary store-order-summary">
                    <details class="checkout-summary__mobile" open>
                        <summary>ملخص الطلب</summary>
                        <div data-checkout-summary></div>
                    </details>
                    <div class="store-order-success" data-checkout-result hidden></div>
                </aside>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cityInput = document.querySelector('[data-shipping-city]');
            const shippingMessage = document.querySelector('[data-shipping-cost-message]');
            const summaryTarget = document.querySelector('[data-checkout-summary]');
            const paymentReferenceSection = document.querySelector('[data-payment-reference]');
            const paymentReferenceInput = document.querySelector('[name="payment_reference"]');
            const paymentReceiptSection = document.querySelector('[data-payment-receipt]');
            const paymentReceiptInput = document.querySelector('[name="payment_receipt"]');
            const paymentReceiptPreview = document.querySelector('[data-payment-receipt-preview]');
            const cartKey = 'xstore_cart_v1';
            let shippingCost = 0;
            let shippingZone = null;
            let shippingTimer = null;

            const updatePaymentReference = () => {
                const method = document.querySelector('[name="payment_method"]:checked')?.value || '';
                const needsReference = ['vodafone_cash', 'instapay'].includes(method);
                if (paymentReferenceSection) paymentReferenceSection.hidden = !needsReference;
                if (paymentReferenceInput) paymentReferenceInput.required = needsReference;
                if (paymentReceiptSection) paymentReceiptSection.hidden = !needsReference;
                if (paymentReceiptInput) paymentReceiptInput.required = needsReference;
            };

            const formatter = new Intl.NumberFormat('ar-EG', {
                style: 'currency',
                currency: 'EGP',
                maximumFractionDigits: 2
            });

            const money = (value) => formatter.format(Number(value) || 0);
            const shippingText = (value) => `${new Intl.NumberFormat('en-US', {
                maximumFractionDigits: 2
            }).format(Number(value) || 0)} EGP`;
            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char]));

            const readCart = () => {
                try {
                    const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
                    return Array.isArray(cart) ? cart : [];
                } catch (error) {
                    return [];
                }
            };

            const renderCheckoutSummary = () => {
                if (!summaryTarget) {
                    return;
                }

                const cart = readCart();
                const subtotal = cart.reduce((total, item) => {
                    return total + ((Number(item.price) || 0) * (Number(item.quantity) || 0));
                }, 0);

                if (!cart.length) {
                    summaryTarget.innerHTML = `
                        <div class="store-empty">
                            <i class="ti ti-shopping-cart-off"></i>
                            <p>أضف منتجاً للسلة قبل تأكيد الطلب.</p>
                            <a href="/shop" class="btn-primary">تصفح المنتجات</a>
                        </div>
                    `;
                    return;
                }

                summaryTarget.innerHTML = `
                    ${cart.map((item) => `
                        <div class="store-order-summary__item">
                            <div>
                                <strong>${escapeHtml(item.name)}</strong>
                                <small>${Number(item.quantity) || 1} × ${money(item.price)}</small>
                            </div>
                            <span>${money((Number(item.price) || 0) * (Number(item.quantity) || 0))}</span>
                        </div>
                    `).join('')}
                    <div class="store-order-summary__row">
                        <span>إجمالي المنتجات</span>
                        <strong>${money(subtotal)}</strong>
                    </div>
                    <div class="store-order-summary__row">
                        <span>الشحن${shippingZone ? ` - ${escapeHtml(shippingZone)}` : ''}</span>
                        <strong>${shippingCost > 0 ? money(shippingCost) : '0 EGP'}</strong>
                    </div>
                    <div class="store-order-summary__row store-order-summary__total">
                        <span>الإجمالي</span>
                        <strong>${money(subtotal + shippingCost)}</strong>
                    </div>
                `;
            };

            const updateShippingCost = async () => {
                const city = cityInput?.value.trim() || '';

                if (!city) {
                    shippingCost = 0;
                    shippingZone = null;
                    if (shippingMessage) {
                        shippingMessage.textContent = 'اكتب المدينة لحساب تكلفة الشحن';
                    }
                    renderCheckoutSummary();
                    return;
                }

                try {
                    const response = await fetch(`{{ url('/shipping-cost') }}?city=${encodeURIComponent(city)}`, {
                        headers: { Accept: 'application/json' }
                    });
                    const data = await response.json();

                    shippingCost = Number(data.cost) || 0;
                    shippingZone = data.zone || null;

                    if (shippingMessage) {
                        if (shippingCost > 0) {
                            shippingMessage.textContent = `تكلفة الشحن: ${shippingText(shippingCost)}${shippingZone ? ` - ${shippingZone}` : ''}`;
                        } else {
                            shippingMessage.textContent = shippingZone ? `شحن مجاني - ${shippingZone}` : 'شحن مجاني أو غير محدد';
                        }
                    }
                } catch (error) {
                    shippingCost = 0;
                    shippingZone = null;
                    if (shippingMessage) {
                        shippingMessage.textContent = 'تعذر حساب تكلفة الشحن الآن';
                    }
                }

                renderCheckoutSummary();
            };

            if (cityInput) {
                cityInput.addEventListener('input', () => {
                    clearTimeout(shippingTimer);
                    shippingTimer = setTimeout(updateShippingCost, 500);
                });
            }

            renderCheckoutSummary();
            document.querySelectorAll('[name="payment_method"]').forEach((input) => {
                input.addEventListener('change', updatePaymentReference);
            });
            paymentReceiptInput?.addEventListener('change', () => {
                const file = paymentReceiptInput.files?.[0];
                if (!file || !paymentReceiptPreview) {
                    if (paymentReceiptPreview) paymentReceiptPreview.hidden = true;
                    return;
                }
                paymentReceiptPreview.src = URL.createObjectURL(file);
                paymentReceiptPreview.hidden = false;
            });
            updatePaymentReference();

            if (cityInput?.value.trim()) {
                updateShippingCost();
            }
        });
    </script>
@endpush
