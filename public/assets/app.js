$(function () {
    const token = $('meta[name="csrf-token"]').attr("content");
    if (token) {
        $.ajaxSetup({ headers: { "X-CSRF-TOKEN": token } });
    }

    const cartBadge = $("[data-cart-count]");
    const storeAlert = (type = "success", message = "") => {
        if (type === "success" && typeof window.showSuccess === "function") {
            window.showSuccess(message);
            return;
        }
        if (type !== "success" && typeof window.showError === "function") {
            window.showError(message);
            return;
        }
        if (typeof window.showAlert === "function") {
            window.showAlert(type, message);
            return;
        }
        alert(message);
    };

    const updateCartSummary = (summary) => {
        if (!summary || !cartBadge.length) return;
        cartBadge.text(summary.count ?? 0);
    };

    window.showConfirmModal = function (message, onConfirm, onCancel) {
        const $overlay = $(
            '<div class="fixed inset-0 bg-black/50 z-[9999] flex items-center justify-center p-4"></div>'
        );
        const $box = $(
            '<div class="max-w-md w-full rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl animate__animated animate__zoomIn animate__faster"></div>'
        );
        $box.append(
            '<div class="text-lg font-bold mb-2 text-gray-900">Onay Gerekiyor</div>'
        );
        $box.append(
            '<div class="text-sm text-gray-600 mb-6">' + message + "</div>"
        );
        const $actions = $(
            '<div class="flex items-center justify-end gap-3"></div>'
        );
        const $cancel = $(
            '<button type="button" class="px-4 py-2 rounded-full border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50 transition-colors">Vazgeç</button>'
        );
        const $ok = $(
            '<button type="button" class="px-4 py-2 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">Evet, Uygula</button>'
        );
        $actions.append($cancel, $ok);
        $box.append($actions);
        $overlay.append($box);
        $("body").append($overlay);
        $cancel.on("click", function () {
            $overlay.remove();
            if (typeof onCancel === "function") onCancel();
        });
        $ok.on("click", function () {
            $overlay.remove();
            if (typeof onConfirm === "function") onConfirm();
        });
    };

    $(document).on("submit", "[data-cart-add-form]", function (e) {
        e.preventDefault();
        const $form = $(this);
        const url = $form.attr("action");
        const formData = $form.serialize();

        $.ajax({
            url,
            method: "POST",
            data: formData,
            dataType: "json",
            headers: { Accept: "application/json" },
        })
            .done((response) => {
                if (response.code === 1) {
                    storeAlert("success", response.msg ?? "Sepete eklendi.");
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    storeAlert("error", response.msg ?? "Bir hata oluştu.");
                    setTimeout(() => window.location.reload(), 500);
                }
            })
            .fail((xhr) => {
                const errors = xhr.responseJSON?.errors;
                const message = Object.values(
                    errors ?? { hata: ["Bir hata oluştu."] }
                )
                    .flat()
                    .join("\n");
                storeAlert("error", message);
                setTimeout(() => window.location.reload(), 500);
            });
    });

    $(document).on("submit", "[data-cart-update-form]", function (e) {
        e.preventDefault();
        const $form = $(this);
        const url = $form.attr("action");
        const formData = $form.serialize();

        $.ajax({
            url,
            method: "PATCH",
            data: formData,
            dataType: "json",
            headers: { Accept: "application/json" },
        })
            .done((response) => {
                if (response.code === 1) {
                    updateCartSummary(response.data?.summary);
                    storeAlert("success", response.msg ?? "Sepet güncellendi.");
                    window.location.reload();
                } else {
                    storeAlert("error", response.msg ?? "Bir hata oluştu.");
                }
            })
            .fail((xhr) => {
                const errors = xhr.responseJSON?.errors;
                const message = Object.values(
                    errors ?? { hata: ["Bir hata oluştu."] }
                )
                    .flat()
                    .join("\n");
                storeAlert("error", message);
            });
    });

    $(document).on("click", "[data-cart-remove]", function (e) {
        e.preventDefault();
        const $button = $(this);
        const url = $button.data("cart-remove");

        $.ajax({
            url,
            method: "DELETE",
            dataType: "json",
            headers: { Accept: "application/json" },
        })
            .done((response) => {
                if (response.code === 1) {
                    updateCartSummary(response.data?.summary);
                    storeAlert(
                        "success",
                        response.msg ?? "Ürün sepetten kaldırıldı."
                    );
                    window.location.reload();
                } else {
                    storeAlert("error", response.msg ?? "Bir hata oluştu.");
                }
            })
            .fail((xhr) => {
                const errors = xhr.responseJSON?.errors;
                const message = Object.values(
                    errors ?? { hata: ["Bir hata oluştu."] }
                )
                    .flat()
                    .join("\n");
                storeAlert("error", message);
            });
    });

    $(document).on("submit", "[data-auth-form]", function (e) {
        e.preventDefault();
        const $form = $(this);
        const url = $form.attr("action");
        const method = $form.attr("method") ?? "POST";
        const formData = $form.serialize();

        $.ajax({
            url,
            method,
            data: formData,
            dataType: "json",
            headers: { Accept: "application/json" },
        })
            .done((response) => {
                const redirect =
                    response?.redirect ??
                    $form.data("redirect") ??
                    window.location.href;
                storeAlert("success", response?.message ?? "İşlem başarılı");
                setTimeout(() => {
                    window.location.href = redirect;
                }, 400);
            })
            .fail((xhr) => {
                const errors = xhr.responseJSON?.errors;
                const message = Object.values(
                    errors ?? { hata: ["Bir hata oluştu."] }
                )
                    .flat()
                    .join("\n");
                storeAlert("error", message);
            });
    });
});
