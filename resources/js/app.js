import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
        return;
    }

    if (form.dataset.confirmed === '1') {
        delete form.dataset.confirmed;
        return;
    }

    event.preventDefault();

    window.dispatchEvent(
        new CustomEvent('erphido-confirm', {
            detail: {
                form,
                title: form.dataset.confirmTitle || 'Konfirmasi Tindakan',
                message: form.dataset.confirm,
                confirmText: form.dataset.confirmButton || 'Ya, Lanjutkan',
                variant: form.dataset.confirmVariant || 'primary',
            },
        }),
    );
});

Alpine.start();
