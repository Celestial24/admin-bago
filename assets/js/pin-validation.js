document.addEventListener('DOMContentLoaded', () => {
    const pinInput = document.getElementById('pin');
    if (pinInput) {
        // Only allow numbers
        pinInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        // Validate PIN on form submit
        pinInput.closest('form')?.addEventListener('submit', (e) => {
            const pin = pinInput.value;
            if (pin.length !== 4 || !/^\d{4}$/.test(pin)) {
                e.preventDefault();
                alert('Please enter a valid 4-digit PIN');
                pinInput.focus();
            }
        });
    }
});

<script src="/assets/js/pin-validation.js"></script>