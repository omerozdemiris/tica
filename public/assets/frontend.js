document.addEventListener("DOMContentLoaded", function () {
    initProductShow();
    initVariantPriceUpdate();
    initCustomSelects();
    initCartIndex();
    initAddresses();
    initReturnSelection();
    initPaymentOptions();
    initHomeHero();
    initCategoryTabs();
    initAnnouncementPopup();
    initMobileMenu();
    initNotifications();
    initCartCoupon();
    initForgotPassword();
    initLazyLoad();
});

function initLazyLoad() {
    if (typeof LazyLoad !== "undefined") {
        window.lazyLoadInstance = new LazyLoad({
            elements_selector: ".lazy",
        });
    }
}

function initCartCoupon() {
    const applyBtn = document.getElementById("apply_coupon_btn");
    const couponInput = document.getElementById("coupon_code_input");
    const promoCards = document.querySelectorAll(".promotion-card");

    if (applyBtn && couponInput) {
        applyBtn.addEventListener("click", function () {
            const code = couponInput.value.trim();
            if (!code) {
                if (window.showError)
                    window.showError("Lütfen bir kupon kodu girin.");
                return;
            }

            applyPromotion({ code: code, check_only: 1 });
        });
    }

    promoCards.forEach((card) => {
        card.addEventListener("click", function () {
            if (this.disabled || this.classList.contains("opacity-50")) return;

            const id = this.dataset.promotionId;
            const code = this.dataset.promotionCode;
            const discount = this.dataset.promotionDiscount;

            if (window.showConfirmModal) {
                window.showConfirmModal(
                    `<b>${code}</b> kodlu %${discount} indirim kuponunu sepetinize uygulamak istediğinize emin misiniz?`,
                    function () {
                        applyPromotion({ promotion_id: id });
                    }
                );
            }
        });
    });

    function applyPromotion(data) {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        fetch("/cart/apply-promotion", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify(data),
        })
            .then((response) => response.json())
            .then((res) => {
                if (res.code === 1) {
                    if (data.check_only) {
                        // If it was just a check, show confirmation modal with details
                        const promo = res.promotion;
                        const formatPrice = (val) =>
                            new Intl.NumberFormat("tr-TR", {
                                minimumFractionDigits: 2,
                            }).format(val) + " ₺";

                        if (window.showConfirmModal) {
                            window.showConfirmModal(
                                `<div class="space-y-3">
                  <p><b>${promo.code}</b> koduyla <b>%${
                                    promo.discount_rate
                                }</b> indirim kazandınız!</p>
                  <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 space-y-1">
                    <div class="flex justify-between text-xs"><span>İndirim Tutarı:</span><span class="text-green-600">-${formatPrice(
                        promo.discount_amount
                    )}</span></div>
                    <div class="flex justify-between font-bold"><span>Yeni Toplam:</span><span>${formatPrice(
                        promo.new_total
                    )}</span></div>
                  </div>
                  <p class="text-[10px] text-gray-400 italic">Onaylamanız durumunda indirim sepetinize kalıcı olarak uygulanacaktır.</p>
                </div>`,
                                function () {
                                    applyPromotion({ promotion_id: promo.id });
                                }
                            );
                        }
                    } else {
                        if (window.showSuccess) window.showSuccess(res.msg);
                        setTimeout(() => window.location.reload(), 500);
                    }
                } else {
                    if (window.showError) window.showError(res.msg);
                }
            })
            .catch((error) => {
                if (window.showError)
                    window.showError("Kupon uygulanırken bir hata oluştu.");
            });
    }
}

function initNotifications() {
    const markAllBtn = document.querySelector("[data-mark-notifications-read]");
    const clearAllBtn = document.querySelector("[data-clear-notifications]");

    if (markAllBtn) {
        markAllBtn.addEventListener("click", () => {
            const url = markAllBtn.dataset.url;
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
            }).then((response) => {
                if (response.ok) {
                    window.location.reload();
                }
            });
        });
    }

    if (clearAllBtn) {
        clearAllBtn.addEventListener("click", () => {
            const url = clearAllBtn.dataset.url;
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content");

            if (window.showConfirmModal) {
                window.showConfirmModal(
                    "Tüm bildirimleri silmek istediğinize emin misiniz? Bu işlem geri alınamaz.",
                    function () {
                        fetch(url, {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": csrfToken,
                                "Content-Type": "application/json",
                                Accept: "application/json",
                            },
                        }).then((response) => {
                            if (response.ok) {
                                if (window.showSuccess)
                                    window.showSuccess(
                                        "Tüm bildirimler temizlendi."
                                    );
                                setTimeout(() => window.location.reload(), 500);
                            }
                        });
                    }
                );
            }
        });
    }
}

function initMobileMenu() {
    const trigger = document.querySelector("[data-mobile-menu-trigger]");

    const close = document.querySelector("[data-mobile-menu-close]");

    const overlay = document.querySelector("[data-mobile-menu-overlay]");

    const sidebar = document.querySelector("[data-mobile-menu-sidebar]");

    if (!trigger || !sidebar || !overlay) return;

    function openMenu() {
        sidebar.classList.remove("-translate-x-full");

        overlay.classList.remove("hidden", "opacity-0");

        document.body.style.overflow = "hidden";
    }

    function closeMenu() {
        sidebar.classList.add("-translate-x-full");

        overlay.classList.add("opacity-0");

        // Reset panels

        const allPanels = document.querySelectorAll(
            ".mobile-menu-panel[id^='mobile-panel-']"
        );

        allPanels.forEach((panel) => {
            panel.classList.add("translate-x-full");

            setTimeout(() => panel.classList.add("hidden"), 300);
        });

        setTimeout(() => {
            overlay.classList.add("hidden");
        }, 300);

        document.body.style.overflow = "";
    }

    trigger.addEventListener("click", openMenu);

    if (close) close.addEventListener("click", closeMenu);

    overlay.addEventListener("click", closeMenu);

    // Sliding Panels Logic (Event Delegation)

    document.addEventListener("click", (e) => {
        const openBtn = e.target.closest("[data-open-panel]");

        if (openBtn) {
            e.preventDefault();

            const targetId = openBtn.dataset.openPanel;

            const targetPanel = document.getElementById(
                "mobile-panel-" + targetId
            );

            if (targetPanel) {
                targetPanel.classList.remove("hidden");

                requestAnimationFrame(() => {
                    targetPanel.classList.remove("translate-x-full");
                });
            }

            return;
        }

        const closeBtn = e.target.closest("[data-close-panel]");

        if (closeBtn) {
            e.preventDefault();

            const targetId = closeBtn.dataset.closePanel;

            const targetPanel = document.getElementById(
                "mobile-panel-" + targetId
            );

            if (targetPanel) {
                targetPanel.classList.add("translate-x-full");

                targetPanel.addEventListener(
                    "transitionend",

                    () => {
                        targetPanel.classList.add("hidden");
                    },

                    { once: true }
                );
            }
        }
    });
}

function initProductShow() {
    const form = document.querySelector("form[data-cart-add-form]");

    if (!form) return;

    const variantSelect = document.querySelector("#variant-select");

    const variantInput = document.querySelector("#variant-input");

    if (variantSelect && variantInput) {
        variantSelect.addEventListener("change", function () {
            variantInput.value = this.value || "";
        });
    }

    const galleryMain = document.querySelector(".product-gallery-main");

    const galleryThumbs = document.querySelectorAll(
        ".product-gallery-thumbs .swiper-slide"
    );

    if (galleryMain && galleryThumbs.length > 1) {
        const thumbsSwiper = new Swiper(".productThumbsSwiper", {
            spaceBetween: 12,

            slidesPerView: "auto",

            freeMode: true,

            watchSlidesProgress: true,
        });

        const mainSwiper = new Swiper(".productMainSwiper", {
            spaceBetween: 10,

            loop: false,

            effect: "fade",

            fadeEffect: {
                crossFade: true,
            },

            thumbs: {
                swiper: thumbsSwiper,
            },
        });

        function updateActiveThumb(activeIndex) {
            document

                .querySelectorAll(".product-gallery-thumbs .swiper-slide")

                .forEach(function (slide, index) {
                    const thumbDiv = slide.querySelector("div");

                    if (thumbDiv) {
                        if (index === activeIndex) {
                            thumbDiv.classList.add("ring-2", "ring-blue-500");
                        } else {
                            thumbDiv.classList.remove(
                                "ring-2",

                                "ring-blue-500"
                            );
                        }
                    }
                });
        }

        document

            .querySelectorAll(".product-gallery-thumbs .swiper-slide")

            .forEach(function (slide, index) {
                slide.addEventListener("click", function () {
                    mainSwiper.slideTo(index);

                    updateActiveThumb(index);
                });
            });

        mainSwiper.on("slideChange", function () {
            updateActiveThumb(mainSwiper.activeIndex);
        });

        updateActiveThumb(0);
    }

    if (typeof Fancybox !== "undefined") {
        Fancybox.bind('[data-fancybox="product-gallery"]', {
            Toolbar: {
                display: {
                    left: ["infobar"],

                    middle: [],

                    right: ["slideshow", "download", "thumbs", "close"],
                },
            },
        });
    }
}

function initVariantPriceUpdate() {
    const priceDisplays = document.querySelectorAll(".product-price-display");
    const priceContainer = document.querySelector("[data-base-price]");

    if (!priceContainer || priceDisplays.length === 0) return;

    const basePrice = parseFloat(priceContainer.dataset.basePrice || 0);

    window.updateVariantPrice = function () {
        let currentPrice = basePrice;
        // Gizli inputlardaki seçili değerlere bakalım
        const selectedInputs = document.querySelectorAll(
            ".variant-hidden-input"
        );

        selectedInputs.forEach((input) => {
            const container = input.closest(".custom-select-container");
            if (container) {
                const selectedOption = container.querySelector(
                    `[data-value="${input.value}"]`
                );
                if (selectedOption) {
                    const variantPrice = selectedOption.dataset.price;
                    if (variantPrice && parseFloat(variantPrice) > 0) {
                        currentPrice = parseFloat(variantPrice);
                    }
                }
            }
        });

        priceDisplays.forEach((display) => {
            display.innerText =
                new Intl.NumberFormat("tr-TR", {
                    minimumFractionDigits: 2,
                }).format(currentPrice) + " ₺";
        });
    };

    window.updateVariantPrice();
}

function initCustomSelects() {
    const selects = document.querySelectorAll(".custom-select-container");

    selects.forEach((container) => {
        const btn = container.querySelector(".custom-select-button");
        const menu = container.querySelector(".custom-select-menu");
        const options = container.querySelectorAll(".custom-select-option");
        const hiddenInput = container.querySelector(".variant-hidden-input");
        const selectedText = container.querySelector(".selected-text");
        const selectedColor = container.querySelector(
            ".selected-color-preview"
        );

        if (!btn || !menu) return;

        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            document.querySelectorAll(".custom-select-menu").forEach((m) => {
                if (m !== menu) m.classList.add("hidden");
            });
            menu.classList.toggle("hidden");
        });

        options.forEach((opt) => {
            opt.addEventListener("click", () => {
                const val = opt.dataset.value;
                const name = opt.dataset.name;
                const color = opt.dataset.color;

                hiddenInput.value = val;
                selectedText.innerText = name;

                if (color) {
                    selectedColor.style.backgroundColor = color;
                    selectedColor.classList.remove("hidden");
                } else {
                    selectedColor.classList.add("hidden");
                }

                menu.classList.add("hidden");
                if (window.updateVariantPrice) window.updateVariantPrice();
            });
        });
    });

    document.addEventListener("click", () => {
        document
            .querySelectorAll(".custom-select-menu")
            .forEach((m) => m.classList.add("hidden"));
    });
}

function initCartIndex() {
    document

        .querySelectorAll(".decrement, .increment")

        .forEach(function (button) {
            if (button.dataset.qBound) {
                return;
            }

            button.dataset.qBound = "1";

            button.addEventListener("click", function (e) {
                e.preventDefault();

                const container = this.closest("form");

                const input = container?.querySelector(
                    'input[name="quantity"]'
                );

                if (!input) return;

                let current = parseInt(input.value, 10);

                current = Number.isNaN(current) ? 1 : current;

                if (this.classList.contains("decrement")) {
                    current = Math.max(1, current - 1);
                } else {
                    current += 1;
                }

                input.value = current;
            });
        });
}

function fillAddressForm(address) {
    const form = document.querySelector("form[data-address-form]");

    if (!form) return;

    const addressIdInput = form.querySelector('input[name="address_id"]');

    if (addressIdInput) {
        addressIdInput.value = address.id || "";
    }

    const titleInput = form.querySelector('input[name="title"]');

    if (titleInput) {
        titleInput.value = address.title || "";
    }

    const fullnameInput = form.querySelector('input[name="fullname"]');

    if (fullnameInput) {
        fullnameInput.value = address.fullname || "";
    }

    const phoneInput = form.querySelector('input[name="phone"]');
    const tcInput = form.querySelector('input[name="tc"]');
    if (tcInput) {
        tcInput.value = address.tc || "";
    }

    if (phoneInput) {
        phoneInput.value = address.phone || "";
    }

    const emailInput = form.querySelector('input[name="email"]');

    if (emailInput) {
        emailInput.value = address.email || "";
    }

    const cityInput = form.querySelector('input[name="city_name"]');

    if (cityInput) {
        cityInput.value = address.city || "";
    }

    const stateInput = form.querySelector('input[name="state_name"]');

    if (stateInput) {
        stateInput.value = address.state || "";
    }

    const zipInput = form.querySelector('input[name="zip"]');

    if (zipInput) {
        zipInput.value = address.zip || "";
    }

    const addressTextarea = form.querySelector('textarea[name="address"]');

    if (addressTextarea) {
        addressTextarea.value = address.address || "";
    }

    const isDefaultInput = form.querySelector('input[name="is_default"]');

    if (isDefaultInput) {
        isDefaultInput.checked = !!address.is_default;
    }

    window.scrollTo({
        top: form.offsetTop - 120,

        behavior: "smooth",
    });
}

function initAddresses() {
    window.fillAddressForm = fillAddressForm;
}

function initReturnSelection() {
    const cards = document.querySelectorAll("[data-return-card]");

    if (!cards.length) return;

    cards.forEach(function (card) {
        const checkbox = card.querySelector('input[type="checkbox"]');

        if (!checkbox || checkbox.disabled) return;

        function updateState() {
            card.classList.toggle("is-selected", checkbox.checked);
        }

        checkbox.addEventListener("change", updateState);

        updateState();
    });
}

function initPaymentOptions() {
    const options = document.querySelectorAll("[data-payment-option]");

    if (!options.length) return;

    options.forEach(function (option) {
        const input = option.querySelector('input[type="radio"]');

        if (!input) return;

        input.addEventListener("change", function () {
            options.forEach(function (opt) {
                opt.classList.toggle(
                    "is-selected",

                    opt.querySelector('input[type="radio"]').checked
                );
            });
        });

        if (input.checked) {
            option.classList.add("is-selected");
        }
    });
}

function initHomeHero() {
    const sliderEl = document.querySelector(".js-home-hero");

    if (!sliderEl || typeof Swiper === "undefined") return;

    const slideCount = sliderEl.querySelectorAll(".swiper-slide").length;

    const enableLoop = slideCount > 1;

    const paginationEl = sliderEl.querySelector(".swiper-pagination");

    const nextEl = sliderEl.querySelector(".home-hero__nav--next");

    const prevEl = sliderEl.querySelector(".home-hero__nav--prev");

    new Swiper(sliderEl, {
        slidesPerView: 1,

        effect: "slide",

        speed: 700,

        allowTouchMove: enableLoop,

        loop: enableLoop,

        autoplay: enableLoop
            ? {
                  delay: 2000,

                  disableOnInteraction: false,
              }
            : false,

        pagination: false,

        navigation:
            nextEl && prevEl
                ? {
                      nextEl,

                      prevEl,
                  }
                : undefined,

        fadeEffect: {
            crossFade: false,
        },
    });
}

function initCategoryTabs() {
    const container = document.querySelector("[data-category-tabs]");

    if (!container) return;

    const tabButtons = container.querySelectorAll("[data-category-tab-button]");

    const tabPanels = container.querySelectorAll("[data-category-tab-panel]");

    if (!tabButtons.length || !tabPanels.length) return;

    function applyButtonState(button, isActive) {
        const activeClasses = (button.dataset.activeClass || "")

            .split(" ")

            .filter(Boolean);

        const inactiveClasses = (button.dataset.inactiveClass || "")

            .split(" ")

            .filter(Boolean);

        if (isActive) {
            if (activeClasses.length) button.classList.add(...activeClasses);

            if (inactiveClasses.length)
                button.classList.remove(...inactiveClasses);

            button.setAttribute("aria-selected", "true");
        } else {
            if (activeClasses.length) button.classList.remove(...activeClasses);

            if (inactiveClasses.length)
                button.classList.add(...inactiveClasses);

            button.setAttribute("aria-selected", "false");
        }
    }

    function activateTab(targetId) {
        if (!targetId) return;

        tabPanels.forEach((panel) => {
            const isActive = panel.id === targetId;

            panel.classList.toggle("hidden", !isActive);

            panel.setAttribute("aria-hidden", String(!isActive));
        });

        tabButtons.forEach((button) => {
            applyButtonState(button, button.dataset.tabTarget === targetId);
        });
    }

    tabButtons.forEach((button) => {
        button.addEventListener("click", function () {
            activateTab(this.dataset.tabTarget);
        });
    });

    const initialButton =
        Array.from(tabButtons).find(
            (button) => button.getAttribute("aria-selected") === "true"
        ) || tabButtons[0];

    if (initialButton) {
        activateTab(initialButton.dataset.tabTarget);
    }
}

function initAnnouncementPopup() {
    const popup = document.querySelector("[data-announcement-popup]");

    if (!popup) {
        return;
    }

    const card = popup.querySelector("[data-popup-card]");

    const body = popup.querySelector("[data-popup-body]");

    const cta = popup.querySelector("[data-popup-cta]");

    const themeColor =
        document.documentElement.style.getPropertyValue("--theme-color");

    const applyStyles = (element, background, color) => {
        if (!element) return;

        if (background) {
            element.style.backgroundColor = background;
        }

        if (color) {
            element.style.color = color;
        }
    };

    if (card && themeColor) {
        card.style.backgroundColor = themeColor;

        card.classList.add("text-white");
    }

    if (body && themeColor) {
        body.style.backgroundColor = "rgba(255,255,255,0.05)";
    }

    if (cta && themeColor) {
        applyStyles(cta, "#fff", themeColor);
    }

    popup.querySelectorAll("[data-popup-close]").forEach((trigger) => {
        trigger.addEventListener("click", () => popup.remove());
    });
}

function initForgotPassword() {
    const emailWrapper = document.getElementById("forgot-email-wrapper");
    const phoneWrapper = document.getElementById("forgot-phone-wrapper");
    const emailInput = document.getElementById("forgot-email");
    const phoneInput = document.getElementById("forgot-phone");
    const searchBtn = document.getElementById("search-btn");
    const sendSmsBtn = document.getElementById("send-sms-btn");
    const methodButtons = document.querySelectorAll("[data-forgot-method]");
    const PHONE_COOLDOWN_KEY = "forgot_reset_phone_expires";

    if (
        !emailWrapper ||
        !phoneWrapper ||
        !emailInput ||
        !phoneInput ||
        !searchBtn ||
        !sendSmsBtn ||
        !methodButtons.length
    ) {
        return;
    }

    let currentMethod = "email";
    let phoneCooldownInterval = null;

    function startPhoneCooldown(remainingSeconds) {
        if (!window.sessionStorage) return;

        const csrfSafeSpan = sendSmsBtn.querySelector("span");
        const originalText =
            sendSmsBtn.dataset.originalText ||
            (csrfSafeSpan ? csrfSafeSpan.textContent : "");
        if (!sendSmsBtn.dataset.originalText && originalText) {
            sendSmsBtn.dataset.originalText = originalText;
        }

        if (phoneCooldownInterval) {
            clearInterval(phoneCooldownInterval);
            phoneCooldownInterval = null;
        }

        let seconds = remainingSeconds;
        const expiresAt = Date.now() + seconds * 1000;
        sessionStorage.setItem(PHONE_COOLDOWN_KEY, String(expiresAt));

        function tick() {
            if (seconds <= 0) {
                sendSmsBtn.disabled = false;
                sendSmsBtn.classList.remove("opacity-50");
                if (csrfSafeSpan) {
                    csrfSafeSpan.textContent =
                        sendSmsBtn.dataset.originalText || originalText;
                }
                sessionStorage.removeItem(PHONE_COOLDOWN_KEY);
                if (phoneCooldownInterval) {
                    clearInterval(phoneCooldownInterval);
                    phoneCooldownInterval = null;
                }
                return;
            }

            sendSmsBtn.disabled = true;
            sendSmsBtn.classList.add("opacity-50");
            if (csrfSafeSpan) {
                csrfSafeSpan.textContent =
                    seconds + " sn sonra tekrar gönderebilirsiniz";
            }
            seconds -= 1;
        }

        tick();
        phoneCooldownInterval = setInterval(tick, 1000);
    }

    function initPhoneCooldownFromStorage() {
        if (!window.sessionStorage) return;
        const expires = sessionStorage.getItem(PHONE_COOLDOWN_KEY);
        if (!expires) return;
        const diff = parseInt(expires, 10) - Date.now();
        if (diff > 0) {
            const remaining = Math.ceil(diff / 1000);
            startPhoneCooldown(remaining);
        } else {
            sessionStorage.removeItem(PHONE_COOLDOWN_KEY);
        }
    }

    function updateMethodButtons(method) {
        methodButtons.forEach((btn) => {
            const isActive = btn.dataset.forgotMethod === method;
            if (isActive) {
                btn.classList.add("bg-white", "shadow", "text-gray-900");
                btn.classList.remove("text-gray-500");
            } else {
                btn.classList.remove("bg-white", "shadow", "text-gray-900");
                btn.classList.add("text-gray-500");
            }
        });
    }

    function switchMethod(method) {
        if (method === currentMethod) return;

        currentMethod = method;

        if (method === "email") {
            // Email moda geçerken telefon inputunu temizle
            phoneInput.value = "";
            phoneWrapper.classList.add("hidden");
            emailWrapper.classList.remove("hidden");

            sendSmsBtn.classList.add("hidden");
            searchBtn.classList.remove("hidden");

            emailInput.focus();
        } else {
            // Telefon moda geçerken email inputunu temizle
            emailInput.value = "";
            emailWrapper.classList.add("hidden");
            phoneWrapper.classList.remove("hidden");

            searchBtn.classList.add("hidden");
            sendSmsBtn.classList.remove("hidden");

            phoneInput.focus();
        }

        updateMethodButtons(method);
    }

    methodButtons.forEach((btn) => {
        btn.addEventListener("click", function () {
            const method = this.dataset.forgotMethod || "email";
            switchMethod(method);
        });
    });

    // Başlangıçta email aktif
    updateMethodButtons(currentMethod);

    // Sayfa yenilendiğinde varsa mevcut telefon cooldown'unu başlat
    initPhoneCooldownFromStorage();

    sendSmsBtn.addEventListener("click", function () {
        const phone = phoneInput.value.trim();
        if (!phone) {
            if (window.showError) {
                window.showError("Lütfen telefon numaranızı girin.");
            } else {
                alert("Lütfen telefon numaranızı girin.");
            }
            return;
        }

        const url = sendSmsBtn.dataset.resetSmsUrl;
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta
            ? csrfTokenMeta.getAttribute("content")
            : null;

        if (!url || !csrfToken) {
            return;
        }

        sendSmsBtn.disabled = true;
        sendSmsBtn.classList.add("opacity-50");
        const span = sendSmsBtn.querySelector("span");
        const originalText = span ? span.textContent : "";
        if (span) span.textContent = "Gönderiliyor...";

        fetch(url, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify({
                phone: phone,
                via: "phone",
            }),
        })
            .then((response) => response.json())
            .then((res) => {
                if (res.success) {
                    if (window.showSuccess) {
                        window.showSuccess(
                            res.msg ||
                                "Şifre sıfırlama bağlantısı SMS olarak gönderildi."
                        );
                    } else {
                        alert(
                            res.msg ||
                                "Şifre sıfırlama bağlantısı SMS olarak gönderildi."
                        );
                    }
                    if (res.cooldown && res.remaining) {
                        startPhoneCooldown(res.remaining);
                    }
                } else {
                    if (window.showError) {
                        window.showError(res.msg || "Bir hata oluştu.");
                    } else {
                        alert(res.msg || "Bir hata oluştu.");
                    }
                    if (res.cooldown && res.remaining) {
                        startPhoneCooldown(res.remaining);
                    }
                }
            })
            .catch(() => {
                if (window.showError) {
                    window.showError("Bir hata oluştu.");
                } else {
                    alert("Bir hata oluştu.");
                }
            })
            .finally(() => {
                const expires = window.sessionStorage
                    ? sessionStorage.getItem(PHONE_COOLDOWN_KEY)
                    : null;
                if (!expires) {
                    sendSmsBtn.disabled = false;
                    sendSmsBtn.classList.remove("opacity-50");
                    if (span) span.textContent = originalText;
                }
            });
    });
}
