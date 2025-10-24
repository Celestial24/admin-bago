// Central delegated handler for buttons and simple modal support
document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-action], a[data-action]');
        if (!btn) return;

        // prevent forms submitting when button not meant to submit
        if (btn.tagName === 'BUTTON' && (btn.getAttribute('type') === null)) {
            btn.setAttribute('type', 'button');
        }

        const action = btn.dataset.action;
        try {
            switch (action) {
                case 'open-modal':
                    openModal(btn.dataset.target);
                    break;
                case 'close-modal':
                    closeModal(btn.closest('.modal') || document.querySelector(btn.dataset.target));
                    break;
                case 'modal-submit':
                    await submitModalForm(btn);
                    break;
                case 'delete':
                    await handleDelete(btn);
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
            console.error('Button action error', action, err);
        }
    });

    // close modal on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.open').forEach(closeModal);
        }
    });
});

function openModal(selector) {
    if (!selector) return;
    const modal = document.querySelector(selector);
    if (!modal) return;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    // focus first focusable element
    const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (focusable) focusable.focus();
}

function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
}

async function submitModalForm(btn) {
    const form = btn.closest('form');
    if (!form) return console.warn('modal-submit but no form found');
    // If form has data-ajax attribute -> submit with fetch
    if (form.dataset.ajax === 'true') {
        const url = form.action || btn.dataset.url;
        const method = (form.method || 'POST').toUpperCase();
        const body = new FormData(form);
        const res = await fetch(url, { method, body, headers: form.dataset.json === 'true' ? { 'Content-Type': 'application/json' } : undefined });
        if (!res.ok) return alert('Request failed');
        const payload = await res.json().catch(() => ({}));
        // close modal and optionally update UI
        closeModal(btn.closest('.modal'));
        if (payload.message) alert(payload.message);
        return;
    }
    // fallback: regular submit
    form.submit();
}

async function handleDelete(btn) {
    if (!confirm(btn.dataset.confirm || 'Are you sure?')) return;
    const url = btn.dataset.url;
    if (!url) return console.warn('No data-url for delete');
    const res = await fetch(url, { method: btn.dataset.method || 'POST', headers: jsonHeaders(), body: JSON.stringify({ _method: 'DELETE' }) });
    if (!res.ok) return alert('Delete failed');
    const row = btn.closest('tr, .item, [data-id]');
    if (row) row.remove();
}

function handleEdit(btn) {
    const target = document.querySelector(btn.dataset.target);
    if (!target) return;
    target.classList.add('editing');
    // open modal if target is inside modal or btn has data-target
    if (btn.dataset.target && document.querySelector(btn.dataset.target)) {
        openModal(btn.dataset.target);
    }
}

async function handleSave(btn) {
    const form = btn.closest('form');
    if (!form) return console.warn('save clicked but no form');
    if (form.dataset.ajax === 'true') {
        await submitModalForm(btn);
        return;
    }
    form.submit();
}

function jsonHeaders() {
    const headers = { 'Content-Type': 'application/json' };
    const token = getCsrfToken();
    if (token) headers['X-CSRF-Token'] = token;
    return headers;
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute('content');
    const input = document.querySelector('input[name="_token"], input[name=\"csrf_token\"]');
    return input ? input.value : null;
}