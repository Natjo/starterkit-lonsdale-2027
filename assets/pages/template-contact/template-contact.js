import { FormValidate } from '@components/field/field';

const getFeedback = (form) => {
    let feedback = form.previousElementSibling;

    if (!(feedback instanceof HTMLElement) || !feedback.matches('[data-contact-feedback]')) {
        feedback = document.createElement('div');
        feedback.className = 'text contact-notice';
        feedback.dataset.contactFeedback = '';
        feedback.setAttribute('aria-live', 'polite');
        feedback.tabIndex = -1;
        feedback.hidden = true;
        form.before(feedback);
    }

    return feedback;
};

const showFeedback = (feedback, message, success) => {
    feedback.textContent = message;
    feedback.classList.toggle('contact-success', success);
    feedback.classList.toggle('contact-error', !success);
    feedback.setAttribute('role', success ? 'status' : 'alert');
    feedback.hidden = message === '';
    if (!success) feedback.focus();
};

export default function initContactForm(form) {
    if (!(form instanceof HTMLFormElement)) return undefined;

    const endpoint = (form.dataset.contactEndpoint || '').trim();
    if (!endpoint) return new FormValidate(form);

    const controller = new AbortController();
    const feedback = getFeedback(form);
    const submitButton = form.querySelector('[type="submit"]');
    let pending = false;

    const submit = async () => {
        if (pending) return;

        pending = true;
        form.setAttribute('aria-busy', 'true');
        if (submitButton instanceof HTMLButtonElement) submitButton.disabled = true;
        showFeedback(feedback, '', false);

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                signal: controller.signal,
            });
            const payload = await response.json();
            const success = response.ok && payload?.success === true;
            const message = typeof payload?.message === 'string' ? payload.message : '';

            showFeedback(feedback, message, success);
            if (success) form.reset();
        } catch (error) {
            if (error?.name !== 'AbortError') {
                HTMLFormElement.prototype.submit.call(form);
            }
        } finally {
            pending = false;
            form.removeAttribute('aria-busy');
            if (submitButton instanceof HTMLButtonElement) submitButton.disabled = false;
        }
    };

    const validation = new FormValidate(form, submit);

    return () => {
        controller.abort();
        validation.destroy();
    };
}
