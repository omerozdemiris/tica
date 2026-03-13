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
