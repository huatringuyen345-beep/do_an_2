'use strict';

const themeToggle = document.getElementById('themeToggleBtn');
const themeIcon = document.getElementById('themeIcon');
const spotlight = document.getElementById('cursorSpotlight');
const loginForm = document.getElementById('loginForm');
const loginButton = document.getElementById('loginSubmitBtn');

if (themeToggle && themeIcon) {
  themeToggle.addEventListener('click', () => {
    const nextTheme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
    document.body.dataset.theme = nextTheme;
    themeIcon.className = nextTheme === 'light' ? 'ri-sun-line' : 'ri-moon-clear-line';
  });
}

document.querySelectorAll('[data-toggle-password]').forEach((button) => {
  button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.togglePassword);
    const icon = button.querySelector('i');
    if (!input || !icon) return;

    const showPassword = input.type === 'password';
    input.type = showPassword ? 'text' : 'password';
    icon.className = showPassword ? 'ri-eye-off-line' : 'ri-eye-line';
    button.setAttribute('aria-label', showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
  });
});

if (spotlight && window.matchMedia('(pointer: fine)').matches) {
  window.addEventListener('mousemove', (event) => {
    spotlight.style.left = `${event.clientX}px`;
    spotlight.style.top = `${event.clientY}px`;
  });
}

if (loginForm && loginButton) {
  loginForm.addEventListener('submit', () => {
    if (loginForm.checkValidity()) loginButton.classList.add('loading');
  });
}
