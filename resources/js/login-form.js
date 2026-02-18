/**
 * Login form validation - required yerine özel kontroller
 */
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function validateLoginForm(form) {
    const emailInput = form.querySelector('input[name="email"]');
    const passwordInput = form.querySelector('input[name="password"]');
    const errors = {};

    // E-posta kontrolü
    const email = (emailInput?.value ?? '').trim();
    if (!email) {
        errors.email = 'E-posta adresi gerekli.';
    } else if (!emailRegex.test(email)) {
        errors.email = 'Geçerli bir e-posta adresi girin.';
    }

    // Şifre kontrolü
    const password = passwordInput?.value ?? '';
    if (!password) {
        errors.password = 'Şifre gerekli.';
    }

    return errors;
}

function showFieldErrors(form, errors) {
    const fields = ['email', 'password'];
    fields.forEach((name) => {
        const input = form.querySelector(`input[name="${name}"]`);
        const errorEl = form.querySelector(`[data-error-for="${name}"]`);
        const group = input?.closest('.flex');

        if (input) {
            if (errors[name]) {
                input.classList.add('input-error');
                if (errorEl) {
                    errorEl.textContent = errors[name];
                    errorEl.classList.remove('hidden');
                }
            } else {
                input.classList.remove('input-error');
                if (errorEl) {
                    errorEl.textContent = '';
                    errorEl.classList.add('hidden');
                }
            }
        }
    });
}

function clearFieldErrors(form) {
    form.querySelectorAll('input.input-error').forEach((el) => el.classList.remove('input-error'));
    form.querySelectorAll('[data-error-for]').forEach((el) => {
        el.textContent = '';
        el.classList.add('hidden');
    });
}

export function initLoginForm(formSelector = '[data-login-form]') {
    const form = document.querySelector(formSelector);
    if (!form) return;

    form.addEventListener('submit', (e) => {
        const errors = validateLoginForm(form);
        clearFieldErrors(form);

        if (Object.keys(errors).length > 0) {
            e.preventDefault();
            showFieldErrors(form, errors);

            // İlk hatalı alana focus
            const firstErrorField = form.querySelector('.input-error');
            if (firstErrorField) {
                firstErrorField.focus();
            }
            return false;
        }
    });

    // Input değiştiğinde hata temizle
    form.querySelectorAll('input[name="email"], input[name="password"]').forEach((input) => {
        input.addEventListener('input', () => {
            const name = input.name;
            const errorEl = form.querySelector(`[data-error-for="${name}"]`);
            if (errorEl?.classList.contains('hidden') === false) {
                const errors = validateLoginForm(form);
                if (!errors[name]) {
                    input.classList.remove('input-error');
                    errorEl.textContent = '';
                    errorEl.classList.add('hidden');
                }
            }
        });
    });
}
