/**
 * Bayi kayıt formu validasyonu
 */
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const rules = {
    company_name: (v) => (!v || !v.trim() ? 'Şirket adı gerekli.' : null),
    contact_name: (v) => (!v || !v.trim() ? 'Yetkili adı gerekli.' : null),
    email: (v) => {
        const val = (v ?? '').trim();
        if (!val) return 'E-posta gerekli.';
        if (!emailRegex.test(val)) return 'Geçerli bir e-posta adresi girin.';
        return null;
    },
    phone: () => null,
    password: (v) => {
        if (!v) return 'Şifre gerekli.';
        if (v.length < 6) return 'Şifre en az 6 karakter olmalı.';
        return null;
    },
    password_confirmation: (form) => {
        const pwd = form.querySelector('input[name="password"]')?.value ?? '';
        const conf = form.querySelector('input[name="password_confirmation"]')?.value ?? '';
        return pwd !== conf ? 'Şifreler eşleşmiyor.' : null;
    },
};

function validateRegisterForm(form) {
    const errors = {};
    ['company_name', 'contact_name', 'email', 'phone', 'password'].forEach((name) => {
        const input = form.querySelector(`input[name="${name}"]`);
        const err = rules[name](input?.value ?? '');
        if (err) errors[name] = err;
    });
    const confErr = rules.password_confirmation(form);
    if (confErr) errors.password_confirmation = confErr;
    return errors;
}

function showFieldErrors(form, errors) {
    const fields = ['company_name', 'contact_name', 'email', 'phone', 'password', 'password_confirmation'];
    fields.forEach((name) => {
        const input = form.querySelector(`input[name="${name}"]`);
        const errorEl = form.querySelector(`[data-error-for="${name}"]`);
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

export function initRegisterForm(formSelector = '[data-register-form]') {
    const form = document.querySelector(formSelector);
    if (!form) return;

    form.addEventListener('submit', (e) => {
        const errors = validateRegisterForm(form);
        clearFieldErrors(form);

        if (Object.keys(errors).length > 0) {
            e.preventDefault();
            showFieldErrors(form, errors);
            const firstErrorField = form.querySelector('.input-error');
            if (firstErrorField) firstErrorField.focus();
            return false;
        }
    });

    form.querySelectorAll('input').forEach((input) => {
        input.addEventListener('input', () => {
            const errors = validateRegisterForm(form);
            ['company_name', 'contact_name', 'email', 'phone', 'password', 'password_confirmation'].forEach((name) => {
                const inp = form.querySelector(`input[name="${name}"]`);
                const errEl = form.querySelector(`[data-error-for="${name}"]`);
                if (inp && errEl && !errEl.classList.contains('hidden') && !errors[name]) {
                    inp.classList.remove('input-error');
                    errEl.textContent = '';
                    errEl.classList.add('hidden');
                }
            });
        });
    });
}
