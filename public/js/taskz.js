document.addEventListener('DOMContentLoaded', () => {
    if (document.documentElement.dataset.theme === 'system') {
        document.documentElement.dataset.theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    const dialog = document.querySelector('[data-command-dialog]');
    const openers = document.querySelectorAll('[data-command-open]');
    const closers = document.querySelectorAll('[data-command-close]');

    openers.forEach((opener) => opener.addEventListener('click', (event) => {
        if (!dialog) return;
        event.preventDefault();
        dialog.showModal();
        dialog.querySelector('input[name="q"]')?.focus();
    }));

    closers.forEach((closer) => closer.addEventListener('click', () => dialog?.close()));

    document.addEventListener('keydown', (event) => {
        const active = document.activeElement?.tagName;
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(active)) return;
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            dialog?.showModal();
            dialog?.querySelector('input[name="q"]')?.focus();
        }
        if (event.key === '?') {
            document.body.classList.toggle('show-shortcuts');
        }
        if (event.key === 'Escape') {
            dialog?.close();
            document.querySelectorAll('details[open][data-task-details]').forEach((detail) => detail.removeAttribute('open'));
        }
    });

    document.querySelectorAll('[data-close-details]').forEach((button) => {
        button.addEventListener('click', () => button.closest('details')?.removeAttribute('open'));
    });

    document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
        const html = document.documentElement;
        html.dataset.theme = html.dataset.theme === 'dark' ? 'light' : 'dark';
    });
});
