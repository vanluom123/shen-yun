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

        if (input && input.value !== String(v)) {
            input.value = String(v);
        }
        if (display && display !== input) {
            display.textContent = String(v);
        }
        computeTotal();
    };

    rows.forEach((row) => {
        const input = row.querySelector('[data-counter-input]');
        const dec = row.querySelector('[data-counter-dec]');
        const inc = row.querySelector('[data-counter-inc]');

        if (input) {
            input.addEventListener('keydown', (e) => {
                // Prevent -, e, E, ., and , as we only want non-negative integers
                if (['-', 'e', 'E', '.', ','].includes(e.key)) {
                    e.preventDefault();
                }
            });

            input.addEventListener('input', () => {
                // We don't use setRowValue here to avoid overwriting cursor position while typing,
                // but we still need to compute total and maybe clamp on blur.
                computeTotal();
            });

            input.addEventListener('blur', () => {
                setRowValue(row, input.value);
            });
        }

        dec?.addEventListener('click', () => setRowValue(row, currentValue(row) - 1));
        inc?.addEventListener('click', () => setRowValue(row, currentValue(row) + 1));
    });

    function currentValue(row) {
        const input = row.querySelector('[data-counter-input]');
        return Number(input?.value || 0);
    }

    computeTotal();
}

function initCustomSelects() {
    const selects = document.querySelectorAll('.custom-select');
    if (!selects.length) return;

    const closeAll = (except = null) => {
        selects.forEach((s) => {
            if (s !== except) s.classList.remove('open');
        });
    };

    const getOptions = (container) => {
        return Array.from(container.querySelectorAll('.custom-select-option'));
    };

    selects.forEach((select) => {
        const trigger = select.querySelector('.custom-select-trigger');
        const dropdown = select.querySelector('.custom-select-dropdown');
        const hiddenInput = select.querySelector('input[type="hidden"]');
        const options = getOptions(dropdown);

        const updateDisplay = () => {
            const selected = options.find((o) => o.classList.contains('selected'));
            if (selected) {
                trigger.textContent = selected.textContent;
            }
        };

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = select.classList.contains('open');
            closeAll(isOpen ? null : select);
            select.classList.toggle('open');
            if (select.classList.contains('open')) {
                trigger.setAttribute('aria-expanded', 'true');
                options.forEach((o) => o.classList.remove('highlighted'));
                const selected = options.find((o) => o.classList.contains('selected'));
                if (selected) {
                    selected.classList.add('highlighted');
                    selected.scrollIntoView({ block: 'nearest' });
                }
            } else {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });

        options.forEach((option) => {
            option.addEventListener('click', () => {
                options.forEach((o) => o.classList.remove('selected', 'highlighted'));
                option.classList.add('selected', 'highlighted');
                const value = option.dataset.value ?? '';
                if (hiddenInput) hiddenInput.value = value;
                select.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
                const onchangeHandler = trigger.dataset.onchange;
                if (onchangeHandler) {
                    try {
                        // Execute handler with hidden input value available as 'value'
                        const handler = new Function('value', 'window', onchangeHandler);
                        handler(value, window);
                    } catch (e) {
                        console.error('Error executing onchange handler:', e);
                    }
                }
                trigger.dispatchEvent(new Event('change', { bubbles: true }));
                updateDisplay();
            });
        });

        trigger.addEventListener('keydown', (e) => {
            if (!select.classList.contains('open')) {
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    select.classList.add('open');
                    options.forEach((o) => o.classList.remove('highlighted'));
                    const selected = options.find((o) => o.classList.contains('selected'));
                    if (selected) selected.classList.add('highlighted');
                    else options[0]?.classList.add('highlighted');
                }
                return;
            }

            const highlighted = options.find((o) => o.classList.contains('highlighted'));
            const idx = highlighted ? options.indexOf(highlighted) : -1;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                highlighted?.classList.remove('highlighted');
                const next = options[Math.min(idx + 1, options.length - 1)];
                next.classList.add('highlighted');
                next.scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                highlighted?.classList.remove('highlighted');
                const prev = options[Math.max(idx - 1, 0)];
                prev.classList.add('highlighted');
                prev.scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (highlighted) {
                    highlighted.click();
                }
            } else if (e.key === 'Escape') {
                select.classList.remove('open');
            }
        });

        updateDisplay();
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.custom-select')) {
            closeAll();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initPasswordToggles();
    redirectToAccessOnReload();
    initSessionCards();
    initCounters();
    initCustomSelects();
});
