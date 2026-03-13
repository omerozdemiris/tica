function resolveToastFn() {
    if (typeof window.toast === "function") return window.toast;
    if (window.notAToast && typeof window.notAToast.toast === "function")
        return window.notAToast.toast;
    if (window.naToast && typeof window.naToast.toast === "function")
        return window.naToast.toast;
    return null;
}

function inlineToast(opts) {
    const box = document.createElement("div");
    box.className =
        "fixed z-50 top-12 transition-all duration-300 right-1/2 translate-x-1/2 max-w-sm px-5 py-3 rounded-full shadow text-sm " +
        (opts.type === "danger"
            ? "bg-red-600 text-white"
            : opts.type === "success"
            ? "bg-green-600 text-white"
            : "bg-gray-800 text-white");
    box.textContent = opts.message || "";
    document.body.appendChild(box);
    setTimeout(() => box.remove(), opts.duration || 100000000);
}

window.showSuccess = function (message) {
    const fn = resolveToastFn();
    const payload = {
        message: message || "✅ İşlem başarıyla tamamlandı!",
        type: "success",
        duration: 100000000,
        position: "top-right",
    };
    if (fn) fn(payload);
    else inlineToast(payload);
};

window.showError = function (message) {
    const fn = resolveToastFn();
    const payload = {
        message: message || "❌ Bir hata oluştu!",
        type: "danger",
        duration: 100000000,
        position: "top-right",
    };
    if (fn) fn(payload);
    else inlineToast(payload);
};

window.showAlert = function (type, message) {
    if (type === "success") {
        window.showSuccess(message);
    } else {
        window.showError(message);
    }
};

window.showConfirmModal = function (message, onConfirm, onCancel) {
    const overlay = document.createElement("div");
    overlay.className =
        "fixed inset-0 bg-black/60 z-[9999] flex items-center justify-center p-4 animate__animated animate__fadeIn animate__faster";

    const box = document.createElement("div");
    box.className =
        "max-w-md w-full rounded-2xl bg-white p-6 shadow-2xl animate__animated animate__zoomIn animate__faster";

    box.innerHTML = `
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                <i class="ri-error-warning-line ri-2x"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Onay Gerekiyor</h3>
        </div>
        <div class="text-sm text-gray-600 mb-6 leading-relaxed">
            ${message}
        </div>
        <div class="flex items-center justify-end gap-3">
            <button type="button" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-500 hover:bg-gray-100 transition-colors" id="confirm-cancel">
                Vazgeç
            </button>
            <button type="button" class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-black text-white hover:bg-gray-800 transition-all" id="confirm-ok">
                Evet, Devam Et
            </button>
        </div>
    `;

    overlay.appendChild(box);
    document.body.appendChild(overlay);

    const cancelBtn = box.querySelector("#confirm-cancel");
    const okBtn = box.querySelector("#confirm-ok");

    cancelBtn.onclick = function () {
        overlay.classList.replace("animate__fadeIn", "animate__fadeOut");
        box.classList.replace("animate__zoomIn", "animate__zoomOut");
        setTimeout(() => overlay.remove(), 200);
        if (typeof onCancel === "function") onCancel();
    };

    okBtn.onclick = function () {
        overlay.classList.replace("animate__fadeIn", "animate__fadeOut");
        box.classList.replace("animate__zoomIn", "animate__zoomOut");
        setTimeout(() => overlay.remove(), 200);
        if (typeof onConfirm === "function") onConfirm();
    };
};
