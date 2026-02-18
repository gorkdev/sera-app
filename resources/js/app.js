import './bootstrap';
import { initLoginForm } from './login-form.js';
import { initRegisterForm } from './register-form.js';
import { initAuthFlip } from './auth-flip.js';

document.addEventListener('DOMContentLoaded', () => {
    initLoginForm();
    initRegisterForm();
    initAuthFlip();
});

// Şifremi unuttum - tasarım amaçlı, e-posta gönderilmiş gibi kapatır
window.handleForgotPassword = function () {
    const modal = document.getElementById('forgot_password_modal');
    const input = document.getElementById('forgot_email');
    if (input) input.value = '';
    modal?.close();
};
