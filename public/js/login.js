// Login page specific functionality
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const successMessage = document.getElementById('successMessage');

    // Form validation
    function validateField(input, errorId, message) {
        const errorElement = document.getElementById(errorId);
        const formGroup = input.closest('.form-group');
        
        if (!input.value.trim()) {
            input.classList.add('error');
            formGroup.classList.add('has-error');
            errorElement.textContent = message;
            return false;
        } else {
            input.classList.remove('error');
            formGroup.classList.remove('has-error');
            errorElement.textContent = '';
            return true;
        }
    }

    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function validateForm() {
        const isUsernameValid = validateField(
            usernameInput, 
            'username-error', 
            'Username or email is required'
        );

        const isPasswordValid = validateField(
            passwordInput, 
            'password-error', 
            'Password is required'
        );

        // Additional email validation if input contains @ symbol
        if (usernameInput.value.includes('@') && !validateEmail(usernameInput.value)) {
            usernameInput.classList.add('error');
            usernameInput.closest('.form-group').classList.add('has-error');
            document.getElementById('username-error').textContent = 'Please enter a valid email address';
            return false;
        }

        return isUsernameValid && isPasswordValid;
    }

    // Real-time validation
    usernameInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            if (this.value.includes('@') && !validateEmail(this.value)) {
                validateField(this, 'username-error', 'Please enter a valid email address');
            } else {
                this.classList.remove('error');
                this.closest('.form-group').classList.remove('has-error');
                document.getElementById('username-error').textContent = '';
            }
        }
    });

    passwordInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            this.classList.remove('error');
            this.closest('.form-group').classList.remove('has-error');
            document.getElementById('password-error').textContent = '';
        }
    });

    // Form submission
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }

        // Show loading state
        loginBtn.classList.add('loading');
        loginBtn.textContent = '';

        // Simulate API call
        setTimeout(() => {
            // Hide loading state
            loginBtn.classList.remove('loading');
            loginBtn.textContent = 'Login';

            // Show success message
            successMessage.classList.add('show');

            // Simulate redirect after success
            setTimeout(() => {
                // In a real app, this would redirect to the dashboard
                alert('Login successful! Redirecting to dashboard...');
                // window.location.href = '/dashboard';
            }, 1500);
        }, 2000);
    });

    // Keyboard accessibility
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.matches('.form-input')) {
            const inputs = Array.from(document.querySelectorAll('.form-input'));
            const currentIndex = inputs.indexOf(e.target);
            
            if (currentIndex < inputs.length - 1) {
                inputs[currentIndex + 1].focus();
            } else {
                loginForm.dispatchEvent(new Event('submit'));
            }
        }
    });

    // Auto-focus first input
    usernameInput.focus();
});
