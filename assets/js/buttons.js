// Delegated handlers for buttons using data-action attributes
document.addEventListener('DOMContentLoaded', () => {
    // Click delegation for buttons and links with data-action
    document.body.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-action], a[data-action]');
        if (!btn) return;

        // prevent accidental form submit
        if (btn.tagName === 'BUTTON') e.preventDefault();

        const action = btn.dataset.action;
        try {
            switch (action) {
                case 'delete':
                    await handleDelete(btn);
                    break;
                case 'toggle':
                    await handleToggle(btn);
                    break;
                case 'edit':
                    handleEdit(btn);
                    break;
                case 'save':
                    await handleSave(btn);
                    break;
                default:
                    console.warn('Unhandled action:', action);
            }
        } catch (err) {
            console.error('Action error', action, err);
        }
    });
});

async function handleDelete(btn) {
    if (!confirm(btn.dataset.confirm || 'Are you sure?')) return;
    const url = btn.dataset.url;
    if (!url) return console.warn('No data-url for delete button');

    const res = await fetch(url, { method: 'POST', headers: jsonHeaders(), body: JSON.stringify({ _method: 'DELETE' }) });
    if (!res.ok) return alert('Delete failed');

    // remove row / element if present
    const row = btn.closest('tr, .item, [data-id]');
    if (row) row.remove();
}

async function handleToggle(btn) {
    const url = btn.dataset.url;
    if (!url) return console.warn('No data-url for toggle button');

    const res = await fetch(url, { method: 'POST', headers: jsonHeaders() });
    if (!res.ok) return alert('Toggle failed');

    // toggle UI state
    btn.classList.toggle('active');
    const payload = await res.json().catch(() => ({}));
    if (payload.state !== undefined) {
        btn.dataset.state = payload.state;
    }
}

function handleEdit(btn) {
    const targetSelector = btn.dataset.target;
    if (!targetSelector) return;
    const container = document.querySelector(targetSelector);
    if (!container) return;
    container.contentEditable = 'true';
    container.focus();
    btn.dataset.original = container.innerHTML;
    btn.closest('form')?.querySelector('button[data-action="save"]')?.classList.remove('hidden');
}

async function handleSave(btn) {
    const form = btn.closest('form');
    if (!form) return console.warn('Save button not inside a form');
    const url = form.action || btn.dataset.url;
    const formData = new FormData(form);
    const res = await fetch(url, { method: form.method || 'POST', headers: fetchHeaders(), body: formData });
    if (!res.ok) return alert('Save failed');
    const payload = await res.json().catch(() => ({}));
    // Optionally update UI with payload
    alert(payload.message || 'Saved');
}

function jsonHeaders() {
    const headers = { 'Content-Type': 'application/json' };
    const token = getCsrfToken();
    if (token) headers['X-CSRF-Token'] = token;
    return headers;
}

function fetchHeaders() {
    const headers = {};
    const token = getCsrfToken();
    if (token) headers['X-CSRF-Token'] = token;
    return headers;
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute('content');
    const input = document.querySelector('input[name="csrf_token"], input[name="_token"]');
    return input?.value;
}

<script src="/assets/js/buttons.js"></script>