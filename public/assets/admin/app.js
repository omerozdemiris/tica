// CSRF setup
$(function () {
    const token = $('meta[name="csrf-token"]').attr("content");
    if (token) {
        $.ajaxSetup({ headers: { "X-CSRF-TOKEN": token } });
    }
    if (typeof window.applyLogoTheme === "function") {
        window.applyLogoTheme();
    }
});

// Toast helpers (not-a-toast first, never fallback to browser alert)
(function () {
    function resolveToastFn() {
        if (typeof window.toast === "function") return window.toast;
        if (window.notAToast && typeof window.notAToast.toast === "function")
            return window.notAToast.toast;
        if (window.naToast && typeof window.naToast.toast === "function")
            return window.naToast.toast;
        return null;
    }
    function inlineToast(opts) {
        // minimal inline toast as last resort (no blocking alert)
        var box = document.createElement("div");
        box.className =
            "fixed z-50 top-4 right-4 max-w-sm px-4 py-3 rounded shadow text-sm " +
            (opts.type === "danger"
                ? "bg-red-600 text-white"
                : opts.type === "success"
                ? "bg-green-600 text-white"
                : "bg-gray-800 text-white");
        box.textContent = opts.message || "";
        document.body.appendChild(box);
        setTimeout(function () {
            box.remove();
        }, opts.duration || 3000);
    }
    window.showSuccess = function (message) {
        var fn = resolveToastFn();
        var payload = {
            message: message || "✅ İşlem başarıyla tamamlandı!",
            type: "success",
            duration: 3000,
            position: "top-right",
        };
        if (fn) fn(payload);
        else inlineToast(payload);
    };
    window.showError = function (message) {
        var fn = resolveToastFn();
        var payload = {
            message: message || "❌ Bir hata oluştu!",
            type: "danger",
            duration: 3500,
            position: "top-right",
        };
        if (fn) fn(payload);
        else inlineToast(payload);
    };
})();

// Dropdowns
$(document).on("click", "[data-dropdown]", function () {
    const key = $(this).data("dropdown");
    const panel = $(`[data-dropdown-panel="${key}"]`);
    panel.toggleClass("hidden");
});

function showConfirmModal(message, onConfirm, onCancel) {
    const $overlay = $(
        '<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"></div>'
    );
    const $box = $(
        '<div class="max-w-md w-full rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-4"></div>'
    );
    $box.append('<div class="font-semibold mb-2">Onay</div>');
    $box.append('<div class="text-sm mb-4">' + message + "</div>");
    const $actions = $(
        '<div class="flex items-center justify-end gap-2"></div>'
    );
    const $cancel = $(
        '<button type="button" class="px-3 py-2 rounded-md border border-gray-200 dark:border-gray-800">Vazgeç</button>'
    );
    const $ok = $(
        '<button type="button" class="px-3 py-2 rounded-md text-sm border border-gray-200 dark:border-gray-800 bg-black text-white dark:bg-white dark:text-black">Evet</button>'
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
}

// Delete via AJAX (generic) with confirm
$(document).on("click", "[data-delete]", function (e) {
    e.preventDefault();
    const $btn = $(this);
    const id = $btn.data("id");
    const url =
        $btn.data("url") || (id ? window.location.pathname + "/" + id : null);
    if (!url) return;
    const method = $btn.data("method") || "DELETE";
    const message =
        $btn.data("confirm") || "Bu kaydı silmek istediğinize emin misiniz?";
    showConfirmModal(message, function () {
        $.ajax({
            url,
            type: "POST",
            data: { _method: method },
            success: function (res) {
                showSuccess(res?.msg || "Silindi");
                setTimeout(() => location.reload(), 600);
            },
            error: function (xhr) {
                let msg = "Hata";
                try {
                    msg = xhr.responseJSON?.msg || msg;
                } catch (_) {}
                showError(msg);
            },
        });
    });
});

// Tables namespace for future DataTable/simple-datatables init
window.AdminTables = {
    products: function () {
        const el = document.querySelector("#products-table");
        if (el && window.simpleDatatables) {
            new window.simpleDatatables.DataTable(el, {
                searchable: true,
                perPageSelect: [10, 25, 50],
                classes: {
                    input: "px-3 py-2 border border-gray-200 dark:border-gray-800 rounded",
                    selector:
                        "px-2 py-1 border border-gray-200 dark:border-gray-800 rounded",
                    paginationList: "flex gap-1",
                    paginationListItem:
                        "px-2 py-1 border border-gray-200 dark:border-gray-800 rounded",
                },
            });
        }
    },
};

// Tom Select init for searchable select lists
$(function () {
    $(".js-select").each(function () {
        const multiple = $(this).attr("multiple") !== undefined;
        new TomSelect(this, {
            create: false,
            copyClassesToDropdown: true,
            plugins: multiple ? ["remove_button"] : [],
            render: {
                option: function (data, escape) {
                    return (
                        '<div class="text-sm px-2 py-1">' +
                        escape(data.text) +
                        "</div>"
                    );
                },
            },
        });
    });
});

// File input handler - update label when file is selected
$(document).on("change", "[data-file-input]", function () {
    const $input = $(this);
    const $label = $input.closest("label").find("[data-file-label]");
    const files = this.files;
    if (files && files.length > 0) {
        const fileName = files[0].name;
        $label.text(fileName);
    } else {
        const defaultText = $label.data("default-text") || "Dosya seçin...";
        $label.text(defaultText);
    }
});

window.applyLogoTheme = function () {
    const lightLogo = document.getElementById("light-logo");
    const darkLogo = document.getElementById("dark-logo");
    if (!lightLogo || !darkLogo) return;
    const isDark = document.documentElement.classList.contains("dark");

    if (isDark) {
        lightLogo.classList.add("opacity-0", "pointer-events-none");
        darkLogo.classList.remove("opacity-0", "pointer-events-none");
    } else {
        darkLogo.classList.add("opacity-0", "pointer-events-none");
        lightLogo.classList.remove("opacity-0", "pointer-events-none");
    }
};

// WOW.js init (animate on scroll) if available
$(function () {
    if (window.WOW) {
        try {
            new WOW({ mobile: true, live: true }).init();
        } catch (e) {}
    }
});
