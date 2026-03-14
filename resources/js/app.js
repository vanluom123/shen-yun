import './bootstrap';

function initPasswordToggles() {
    const toggles = document.querySelectorAll('[data-toggle-password]');
    if (!toggles.length) return;

    toggles.forEach((btn) => {
        const inputId = btn.getAttribute('data-toggle-password');
        if (!inputId) return;
        const input = document.getElementById(inputId);
        if (!(input instanceof HTMLInputElement)) return;

        const sync = () => {
            const isVisible = input.type === 'text';
            btn.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
            btn.textContent = isVisible ? 'ẨN' : 'HIỆN';
            btn.setAttribute('aria-label', isVisible ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        };

        btn.addEventListener('click', () => {
            input.type = input.type === 'password' ? 'text' : 'password';
            sync();
        });

        sync();
    });
}

function redirectToAccessOnReload() {
    const path = window.location.pathname || '';
    if (!path.startsWith('/register') || path === '/login') return;

    const nav = performance.getEntriesByType?.('navigation')?.[0];
    const type = nav?.type;
    if (type === 'reload') {
        window.location.replace('/login');
    }
}

function initSessionCards() {
    const radios = document.querySelectorAll('input[data-session-radio]');
    if (!radios.length) return;

    const update = () => {
        radios.forEach((radio) => {
            const card = radio.closest('label')?.querySelector('[data-session-card]');
            if (!card) return;
            if (radio.checked) card.classList.add('rsvp-card-selected');
            else card.classList.remove('rsvp-card-selected');
        });
    };

    radios.forEach((r) => r.addEventListener('change', update));
    update();
}

function initCounters() {
    const root = document.querySelector('[data-counter-root]');
    if (!root) return;

    const rows = root.querySelectorAll('[data-counter-row]');
    const totalEl = root.querySelector('[data-counter-total]');
    const selfExtra = Number(root.getAttribute('data-counter-self') || 0);

    const clamp = (v) => Math.max(0, Math.min(999, v));

    const computeTotal = () => {
        let total = 0;
        rows.forEach((row) => {
            const input = row.querySelector('[data-counter-input]');
            total += Number(input?.value || 0);
        });
        total += selfExtra;
        if (totalEl) totalEl.textContent = String(total);
    };

    const setRowValue = (row, nextValue) => {
        const input = row.querySelector('[data-counter-input]');
        const display = row.querySelector('[data-counter-display]');
        const v = clamp(Number(nextValue) || 0);
        if (input) input.value = String(v);
        if (display) display.textContent = String(v);
        computeTotal();
    };

    rows.forEach((row) => {
        const input = row.querySelector('[data-counter-input]');
        const display = row.querySelector('[data-counter-display]');
        const dec = row.querySelector('[data-counter-dec]');
        const inc = row.querySelector('[data-counter-inc]');

        const current = clamp(Number(input?.value || display?.textContent || 0));
        setRowValue(row, current);

        dec?.addEventListener('click', () => setRowValue(row, currentValue(row) - 1));
        inc?.addEventListener('click', () => setRowValue(row, currentValue(row) + 1));
    });

    function currentValue(row) {
        const input = row.querySelector('[data-counter-input]');
        return clamp(Number(input?.value || 0));
    }

    computeTotal();
}

document.addEventListener('DOMContentLoaded', () => {
    initPasswordToggles();
    redirectToAccessOnReload();
    initSessionCards();
    initCounters();
});
