// Login page specific functionality
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginInput = document.getElementById('login');
    const passwordInput = document.getElementById('password');
    const loginBtn = loginForm.querySelector('button[type="submit"]');
    const successMessage = document.getElementById('successMessage');

    // Clear error states on input
    if (loginInput) {
        loginInput.addEventListener('input', function() {
            this.classList.remove('error');
            const formGroup = this.closest('.form-group');
            if (formGroup) {
                formGroup.classList.remove('has-error');
            }
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            this.classList.remove('error');
            const formGroup = this.closest('.form-group');
            if (formGroup) {
                formGroup.classList.remove('has-error');
            }
        });
    }

    // Form submission - allow normal form submission for backend validation
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // Basic client-side validation
            let hasErrors = false;

            // Clear previous error states
            loginInput?.classList.remove('error');
            passwordInput?.classList.remove('error');
            document.querySelectorAll('.form-group').forEach(group => {
                group.classList.remove('has-error');
            });

            // Validate login field
            if (!loginInput || !loginInput.value.trim()) {
                if (loginInput) {
                    loginInput.classList.add('error');
                    const formGroup = loginInput.closest('.form-group');
                    if (formGroup) {
                        formGroup.classList.add('has-error');
                    }
                }
                hasErrors = true;
            }

            // Validate password field
            if (!passwordInput || !passwordInput.value.trim()) {
                if (passwordInput) {
                    passwordInput.classList.add('error');
                    const formGroup = passwordInput.closest('.form-group');
                    if (formGroup) {
                        formGroup.classList.add('has-error');
                    }
                }
                hasErrors = true;
            }

            // If client-side validation fails, prevent submission
            if (hasErrors) {
                e.preventDefault();
                return false;
            }

            // Show loading state on button
            if (loginBtn) {
                loginBtn.disabled = true;
                loginBtn.style.opacity = '0.7';
                loginBtn.style.cursor = 'not-allowed';
                const originalText = loginBtn.textContent;
                loginBtn.textContent = 'Logging in...';
            }

            // Allow form to submit normally - backend will handle authentication
            // If there are errors, the page will reload with error messages
        });
    }

    // If there's a general login error, apply error styling to both fields
    if (document.querySelector('.login-form > .error-message')) {
        if (loginInput) {
            loginInput.classList.add('error');
            const loginFormGroup = loginInput.closest('.form-group');
            if (loginFormGroup) {
                loginFormGroup.classList.add('has-error');
            }
        }
        if (passwordInput) {
            passwordInput.classList.add('error');
            const passwordFormGroup = passwordInput.closest('.form-group');
            if (passwordFormGroup) {
                passwordFormGroup.classList.add('has-error');
            }
        }
    }

    // Auto-focus first input if no errors present
    if (loginInput && !document.querySelector('.error-message')) {
        loginInput.focus();
    }
});
