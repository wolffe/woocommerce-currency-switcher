document.addEventListener('DOMContentLoaded', function () {
    const currencySelect = document.getElementById('currency-select');

    if (!currencySelect) return;

    // Store initial value
    currencySelect.dataset.previousValue = currencySelect.value;

    // Handle currency switch
    currencySelect.addEventListener('change', function () {
        const select = this;
        const currency = select.value;

        // Disable select while processing
        select.disabled = true;

        fetch(wcCurrencySwitcher.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'switch_currency',
                currency: currency
            })
        })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    // Reload the page after successful currency switch
                    window.location.reload();
                } else {
                    // Revert to previous value on error
                    select.value = select.dataset.previousValue;
                }
            })
            .catch(() => {
                // Revert to previous value on error
                select.value = select.dataset.previousValue;
            })
            .finally(() => {
                // Re-enable select
                select.disabled = false;
            });
    });
});
