const isControl = (element) => (
    element instanceof HTMLInputElement
    || element instanceof HTMLSelectElement
    || element instanceof HTMLTextAreaElement
);

const getFields = (form) => Array.from(form.elements).filter((field) => (
    isControl(field)
    && !['button', 'submit', 'reset', 'image'].includes(field.type)
));

const getRequiredGroups = (form) => Array.from(
    form.querySelectorAll('[role="group"][data-required]'),
);

const getInvalidMsgEl = (field) => (
    field.closest('[role="group"], .field')?.querySelector('.invalid-msg') || null
);

const ensureInvalidMsgEl = (field) => {
    const existing = getInvalidMsgEl(field);
    if (existing) return existing;

    const invalidMsg = document.createElement('div');
    invalidMsg.className = 'invalid-msg';
    invalidMsg.id = field.id
        ? `${field.id}-error`
        : `field-error-${globalThis.crypto?.randomUUID?.() || Date.now()}`;
    invalidMsg.setAttribute('aria-live', 'polite');
    invalidMsg.hidden = true;
    field.insertAdjacentElement('afterend', invalidMsg);

    const describedby = (field.getAttribute('aria-describedby') || '').trim();
    field.setAttribute('aria-describedby', [invalidMsg.id, describedby].filter(Boolean).join(' '));

    return invalidMsg;
};

const setError = (invalidMsg, message) => {
    if (!invalidMsg) return;
    invalidMsg.textContent = message;
    invalidMsg.hidden = message === '';
};

const clearField = (field) => {
    field.removeAttribute('aria-invalid');
    field.setCustomValidity('');
    setError(getInvalidMsgEl(field), '');
};

const validateField = (field) => {
    field.setCustomValidity('');
    const { validity } = field;

    if (field.checkValidity()) {
        clearField(field);
        return true;
    }

    let message = '';
    if (
        (validity.typeMismatch || validity.stepMismatch || validity.tooShort || validity.tooLong)
        && field.dataset.typemismatch
    ) {
        message = field.dataset.typemismatch;
    }
    if (validity.patternMismatch && field.dataset.patternmismatch) {
        message = field.dataset.patternmismatch;
    }
    if (validity.valueMissing && field.dataset.mandatory) {
        message = field.dataset.mandatory;
    }
    if (message) field.setCustomValidity(message);

    field.setAttribute('aria-invalid', 'true');
    setError(ensureInvalidMsgEl(field), field.validationMessage);
    return false;
};

const groupInputs = (group) => Array.from(
    group.querySelectorAll('input[type="checkbox"], input[type="radio"]'),
);

const validateGroup = (group) => {
    const inputs = groupInputs(group);
    const enabledInputs = inputs.filter((input) => !input.disabled);
    const valid = enabledInputs.some((input) => input.checked);

    if (valid) {
        inputs.forEach(clearField);
        setError(group.querySelector('.invalid-msg'), '');
        return true;
    }

    inputs.forEach((input) => input.setAttribute('aria-invalid', 'true'));
    const anchor = enabledInputs[0] || inputs[0];
    let message = (group.dataset.mandatory || '').trim();

    if (!message && anchor) {
        const wasRequired = anchor.required;
        anchor.required = true;
        message = anchor.validationMessage;
        anchor.required = wasRequired;
    }

    setError(group.querySelector('.invalid-msg'), message);
    return false;
};

const FormValidate = function (form, onSend) {
    if (!(form instanceof HTMLFormElement)) return;

    const controller = new AbortController();
    const { signal } = controller;
    const initialNoValidate = form.noValidate;
    let submitted = false;

    // Avant l’hydratation, la validation HTML native reste active. Une fois le
    // module chargé, ce dernier prend la main pour afficher les messages dédiés.
    form.noValidate = true;

    const reset = () => {
        submitted = false;
        getFields(form).forEach(clearField);
        getRequiredGroups(form).forEach((group) => {
            groupInputs(group).forEach((input) => input.removeAttribute('aria-invalid'));
            setError(group.querySelector('.invalid-msg'), '');
        });
    };

    const validateAll = () => {
        let firstInvalid = null;

        for (const field of getFields(form)) {
            if (field.closest('[role="group"][data-required]')) continue;
            if (!validateField(field) && !firstInvalid) firstInvalid = field;
        }

        for (const group of getRequiredGroups(form)) {
            if (validateGroup(group) || firstInvalid) continue;
            firstInvalid = groupInputs(group).find((input) => !input.disabled) || group;
        }

        return { valid: firstInvalid === null, firstInvalid };
    };

    const validateChangedField = (event) => {
        if (!submitted || !isControl(event.target)) return;
        const group = event.target.closest('[role="group"][data-required]');
        if (group) {
            validateGroup(group);
            return;
        }
        validateField(event.target);
    };

    form.addEventListener('input', validateChangedField, { signal });
    form.addEventListener('change', validateChangedField, { signal });
    form.addEventListener('reset', reset, { signal });

    form.addEventListener('submit', (event) => {
        submitted = true;
        const { valid, firstInvalid } = validateAll();

        if (!valid) {
            event.preventDefault();
            firstInvalid?.focus();
            return;
        }

        if (typeof onSend === 'function') {
            event.preventDefault();
            onSend(this);
        }
    }, { signal });

    this.reset = reset;
    this.destroy = () => {
        controller.abort();
        reset();
        form.noValidate = initialNoValidate;
    };
};

export { FormValidate };

export default function initForm(form) {
    const instance = new FormValidate(form);
    if (!instance?.destroy) return;

    return () => instance.destroy();
}
