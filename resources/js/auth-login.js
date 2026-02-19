const togglePasswordBtn = document.getElementById('toggle-password');
const passwordInput = document.getElementById('password');
const eyeOpen = document.getElementById('eye-open');
const eyeClosed = document.getElementById('eye-closed');
const loginForm = document.getElementById('login-form');
const submitText = document.getElementById('submit-text');

if (togglePasswordBtn && passwordInput) {
  togglePasswordBtn.addEventListener('click', () => {
    const isHidden = passwordInput.type === 'password';
    passwordInput.type = isHidden ? 'text' : 'password';
    eyeOpen?.classList.toggle('hidden');
    eyeClosed?.classList.toggle('hidden');
  });
}

if (loginForm) {
  loginForm.addEventListener('submit', () => {
    const submitBtn = loginForm.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
    }

    if (submitText) {
      submitText.textContent = 'Verificando...';
    }
  });
}
