// Registration page specific functionality
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const steps = document.querySelectorAll('.form-step');
    const stepIndicators = document.querySelectorAll('.step');
    const connectors = document.querySelectorAll('.step-connector');
    const successMessage = document.getElementById('successMessage');
    const step2Content = document.getElementById('step2Content');
    
    let currentStep = 1;
    let selectedAccountType = '';

    // Form templates for different account types
    const formTemplates = {
        consumer: `
            <div class="form-row">
                <div class="form-group">
                    <label for="firstName" class="form-label required">First Name</label>
                    <input 
                        type="text" 
                        id="firstName" 
                        name="firstName" 
                        class="form-input" 
                        placeholder="Enter first name"
                        required
                        autocomplete="given-name"
                        aria-describedby="firstName-error"
                    >
                    <div class="error-message" id="firstName-error" role="alert"></div>
                </div>

                <div class="form-group">
                    <label for="middleName" class="form-label">Middle Name (optional)</label>
                    <input 
                        type="text" 
                        id="middleName" 
                        name="middleName" 
                        class="form-input" 
                        placeholder="Enter middle name"
                        autocomplete="additional-name"
                    >
                </div>
            </div>

            <div class="form-group full-width">
                <label for="lastName" class="form-label required">Last Name</label>
                <input 
                    type="text" 
                    id="lastName" 
                    name="lastName" 
                    class="form-input" 
                    placeholder="Enter last name"
                    required
                    autocomplete="family-name"
                    aria-describedby="lastName-error"
                >
                <div class="error-message" id="lastName-error" role="alert"></div>
            </div>

            <div class="form-group full-width">
                <label for="email" class="form-label required">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input" 
                    placeholder="Enter email address"
                    required
                    autocomplete="email"
                    aria-describedby="email-error"
                >
                <div class="error-message" id="email-error" role="alert"></div>
            </div>

            <div class="form-group full-width">
                <label for="phone" class="form-label required">Phone Number</label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    class="form-input" 
                    placeholder="Enter phone number"
                    required
                    autocomplete="tel"
                    aria-describedby="phone-error"
                >
                <div class="error-message" id="phone-error" role="alert"></div>
            </div>
        `,
        business: `
            <div class="form-group full-width">
                <label for="businessName" class="form-label required">Business Name</label>
                <input 
                    type="text" 
                    id="businessName" 
                    name="businessName" 
                    class="form-input" 
                    placeholder="Enter Business Name"
                    required
                    aria-describedby="businessName-error"
                >
                <div class="error-message" id="businessName-error" role="alert"></div>
            </div>

            <div class="form-group full-width">
                <label for="businessType" class="form-label required">Business Type</label>
                <select 
                    id="businessType" 
                    name="businessType" 
                    class="form-select" 
                    required
                    aria-describedby="businessType-error"
                >
                    <option value="">Choose Business Type</option>
                    <option value="restaurant">Restaurant</option>
                    <option value="bakery">Bakery</option>
                    <option value="grocery">Grocery Store</option>
                    <option value="cafe">Cafe</option>
                    <option value="catering">Catering Service</option>
                    <option value="food-truck">Food Truck</option>
                    <option value="other">Other</option>
                </select>
                <div class="error-message" id="businessType-error" role="alert"></div>
            </div>

            <div class="form-group full-width">
                <label for="birCertificate" class="form-label required">Upload BIR Certificate</label>
                <div class="file-upload-wrapper">
                    <input 
                        type="file" 
                        id="birCertificate" 
                        name="birCertificate" 
                        class="file-upload-input"
                        accept=".pdf,.jpg,.jpeg,.png"
                        required
                        aria-describedby="birCertificate-error"
                    >
                    <label for="birCertificate" class="file-upload-label">
                        📄 Choose BIR Certificate file
                    </label>
                </div>
                <div class="error-message" id="birCertificate-error" role="alert"></div>
            </div>

            <div class="form-group full-width">
                <label for="email" class="form-label required">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input" 
                    placeholder="Enter email address"
                    required
                    autocomplete="email"
                    aria-describedby="email-error"
                >
                <div class="error-message" id="email-error" role="alert"></div>
            </div>

            <div class="form-group full-width">
                <label for="phone" class="form-label required">Phone Number</label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    class="form-input" 
                    placeholder="Enter phone number"
                    required
                    autocomplete="tel"
                    aria-describedby="phone-error"
                >
                <div class="error-message" id="phone-error" role="alert"></div>
            </div>
        `,
        foodbank: `
            <div class="form-group full-width">
                <label for="organizationName" class="form-label required">Organization Name</label>
                <input 
                    type="text" 
                    id="organizationName" 
                    name="organizationName" 
                    class="form-input" 
                    placeholder="Enter your Organization Name"
                    required
                    aria-describedby="organizationName-error"
                >
                <div class="error-message" id="organizationName-error" role="alert"></div>
            </div>

            <div class="form-group full-width">
                <label for="serviceArea" class="form-label required">Service Area</label>
                <input 
                    type="text" 
                    id="serviceArea" 
                    name="serviceArea" 
                    class="form-input" 
                    placeholder="Enter Service Area"
                    required
                    aria-describedby="serviceArea-error"
                >
                <div class="error-message" id="serviceArea-error" role="alert"></div>
            </div>

            <div class="form-group full-width">
                <label for="orgRegistration" class="form-label required">Upload Org. Registration No.</label>
                <div class="file-upload-wrapper">
                    <input 
                        type="file" 
                        id="orgRegistration" 
                        name="orgRegistration" 
                        class="file-upload-input"
                        accept=".pdf,.jpg,.jpeg,.png"
                        required
                        aria-describedby="orgRegistration-error"
                    >
                    <label for="orgRegistration" class="file-upload-label">
                        📄 Choose Registration file
                    </label>
                </div>
                <div class="error-message" id="orgRegistration-error" role="alert"></div>
            </div>

            <div class="form-group full-width">
                <label for="email" class="form-label required">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input" 
                    placeholder="Enter email address"
                    required
                    autocomplete="email"
                    aria-describedby="email-error"
                >
                <div class="error-message" id="email-error" role="alert"></div>
            </div>

            <div class="form-group full-width">
                <label for="phone" class="form-label required">Phone Number</label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    class="form-input" 
                    placeholder="Enter phone number"
                    required
                    autocomplete="tel"
                    aria-describedby="phone-error"
                >
                <div class="error-message" id="phone-error" role="alert"></div>
            </div>
        `
    };

    // Form titles for different account types
    const formTitles = {
        consumer: 'PERSONAL INFORMATION',
        business: 'BUSINESS INFORMATION',
        foodbank: 'ORGANIZATION INFORMATION'
    };

    // Account type selection
    const accountTypes = document.querySelectorAll('input[name="accountType"]');
    accountTypes.forEach(type => {
        type.addEventListener('change', function() {
            // Remove selected class from all account types
            document.querySelectorAll('.account-type').forEach(at => {
                at.classList.remove('selected');
            });
            
            // Add selected class to chosen type
            this.closest('.account-type').classList.add('selected');
            selectedAccountType = this.value;
        });
    });

    // Update step 2 content based on account type
    function updateStep2Content(accountType) {
        const step2Title = document.getElementById('step2Title');
        step2Title.textContent = formTitles[accountType];
        step2Content.innerHTML = formTemplates[accountType];
        
        // Update file upload labels when files are selected
        const fileInputs = step2Content.querySelectorAll('.file-upload-input');
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const label = this.nextElementSibling;
                if (this.files.length > 0) {
                    label.textContent = `📄 ${this.files[0].name}`;
                    label.style.color = '#347928';
                } else {
                    label.textContent = label.getAttribute('data-original-text') || '📄 Choose file';
                    label.style.color = '#666';
                }
            });
        });
    }

    // Step navigation functions
    function updateStepIndicators(step) {
        stepIndicators.forEach((indicator, index) => {
            const stepNum = index + 1;
            
            if (stepNum < step) {
                indicator.className = 'step completed';
            } else if (stepNum === step) {
                indicator.className = 'step active';
            } else {
                indicator.className = 'step inactive';
            }
        });

        connectors.forEach((connector, index) => {
            if (index + 1 < step) {
                connector.classList.add('completed');
            } else {
                connector.classList.remove('completed');
            }
        });
    }

    function showStep(step) {
        steps.forEach((stepEl, index) => {
            stepEl.classList.toggle('active', index + 1 === step);
        });
        updateStepIndicators(step);
        currentStep = step;
    }

    // Validation functions
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function validatePhone(phone) {
        const re = /^[\+]?[1-9][\d]{0,15}$/;
        return re.test(phone.replace(/\s+/g, ''));
    }

    function validatePassword(password) {
        return password.length >= 8;
    }

    function validateField(input, errorId, validationFn, errorMessage) {
        const errorElement = document.getElementById(errorId);
        const formGroup = input.closest('.form-group');
        
        if (!validationFn(input.value.trim())) {
            input.classList.add('error');
            formGroup.classList.add('has-error');
            errorElement.textContent = errorMessage;
            return false;
        } else {
            input.classList.remove('error');
            formGroup.classList.remove('has-error');
            errorElement.textContent = '';
            return true;
        }
    }

    function validateStep1() {
        return selectedAccountType !== '';
    }

    function validateStep2() {
        let validations = [];
        
        if (selectedAccountType === 'consumer') {
            const firstName = document.getElementById('firstName');
            const lastName = document.getElementById('lastName');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');

            validations = [
                validateField(firstName, 'firstName-error', val => val.length >= 2, 'First name must be at least 2 characters'),
                validateField(lastName, 'lastName-error', val => val.length >= 2, 'Last name must be at least 2 characters'),
                validateField(email, 'email-error', validateEmail, 'Please enter a valid email address'),
                validateField(phone, 'phone-error', validatePhone, 'Please enter a valid phone number')
            ];
        } else if (selectedAccountType === 'business') {
            const businessName = document.getElementById('businessName');
            const businessType = document.getElementById('businessType');
            const birCertificate = document.getElementById('birCertificate');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');

            validations = [
                validateField(businessName, 'businessName-error', val => val.length >= 2, 'Business name must be at least 2 characters'),
                validateField(businessType, 'businessType-error', val => val !== '', 'Please select a business type'),
                validateField(email, 'email-error', validateEmail, 'Please enter a valid email address'),
                validateField(phone, 'phone-error', validatePhone, 'Please enter a valid phone number')
            ];

            // Validate file upload
            if (birCertificate.files.length === 0) {
                const errorElement = document.getElementById('birCertificate-error');
                const formGroup = birCertificate.closest('.form-group');
                formGroup.classList.add('has-error');
                errorElement.textContent = 'Please upload your BIR Certificate';
                validations.push(false);
            } else {
                const errorElement = document.getElementById('birCertificate-error');
                const formGroup = birCertificate.closest('.form-group');
                formGroup.classList.remove('has-error');
                errorElement.textContent = '';
                validations.push(true);
            }
        } else if (selectedAccountType === 'foodbank') {
            const organizationName = document.getElementById('organizationName');
            const serviceArea = document.getElementById('serviceArea');
            const orgRegistration = document.getElementById('orgRegistration');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');

            validations = [
                validateField(organizationName, 'organizationName-error', val => val.length >= 2, 'Organization name must be at least 2 characters'),
                validateField(serviceArea, 'serviceArea-error', val => val.length >= 2, 'Service area must be at least 2 characters'),
                validateField(email, 'email-error', validateEmail, 'Please enter a valid email address'),
                validateField(phone, 'phone-error', validatePhone, 'Please enter a valid phone number')
            ];

            // Validate file upload
            if (orgRegistration.files.length === 0) {
                const errorElement = document.getElementById('orgRegistration-error');
                const formGroup = orgRegistration.closest('.form-group');
                formGroup.classList.add('has-error');
                errorElement.textContent = 'Please upload your Organization Registration';
                validations.push(false);
            } else {
                const errorElement = document.getElementById('orgRegistration-error');
                const formGroup = orgRegistration.closest('.form-group');
                formGroup.classList.remove('has-error');
                errorElement.textContent = '';
                validations.push(true);
            }
        }

        return validations.every(valid => valid);
    }

    function validateStep3() {
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        const location = document.getElementById('location');
        const terms = document.getElementById('terms');

        const validations = [
            validateField(username, 'username-error', val => val.length >= 3, 'Username must be at least 3 characters'),
            validateField(password, 'password-error', validatePassword, 'Password must be at least 8 characters'),
            validateField(confirmPassword, 'confirmPassword-error', val => val === password.value, 'Passwords do not match'),
            validateField(location, 'location-error', val => val.length >= 2, 'Please enter your location')
        ];

        if (!terms.checked) {
            alert('Please accept the Terms of Service to continue.');
            return false;
        }

        return validations.every(valid => valid);
    }

    // Step navigation event listeners
    document.getElementById('nextStep1').addEventListener('click', function() {
        if (validateStep1()) {
            updateStep2Content(selectedAccountType);
            showStep(2);
        } else {
            alert('Please select an account type to continue.');
        }
    });

    document.getElementById('nextStep2').addEventListener('click', function() {
        if (validateStep2()) {
            showStep(3);
        }
    });

    document.getElementById('prevStep2').addEventListener('click', function() {
        showStep(1);
    });

    document.getElementById('prevStep3').addEventListener('click', function() {
        showStep(2);
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateStep3()) {
            const registerBtn = document.getElementById('registerBtn');
            registerBtn.classList.add('loading');
            registerBtn.disabled = true;

            // Collect form data based on account type
            let finalFormData = {
                accountType: selectedAccountType,
                username: document.getElementById('username').value.trim(),
                password: document.getElementById('password').value,
                location: document.getElementById('location').value.trim(),
                email: document.getElementById('email').value.trim(),
                phone: document.getElementById('phone').value.trim(),
                terms: document.getElementById('terms').checked,
                newsletter: document.getElementById('newsletter').checked
            };

            // Add specific fields based on account type
            if (selectedAccountType === 'consumer') {
                finalFormData.firstName = document.getElementById('firstName').value.trim();
                finalFormData.middleName = document.getElementById('middleName').value.trim();
                finalFormData.lastName = document.getElementById('lastName').value.trim();
            } else if (selectedAccountType === 'business') {
                finalFormData.businessName = document.getElementById('businessName').value.trim();
                finalFormData.businessType = document.getElementById('businessType').value;
                finalFormData.birCertificate = document.getElementById('birCertificate').files[0];
            } else if (selectedAccountType === 'foodbank') {
                finalFormData.organizationName = document.getElementById('organizationName').value.trim();
                finalFormData.serviceArea = document.getElementById('serviceArea').value.trim();
                finalFormData.orgRegistration = document.getElementById('orgRegistration').files[0];
            }

            // Send data to Laravel backend
            fetch('/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    role: selectedAccountType,
                    username: finalFormData.username,
                    password: finalFormData.password,
                    password_confirmation: finalFormData.password,
                    email: finalFormData.email,
                    phone_no: finalFormData.phone,
                    address: finalFormData.location,
                    fname: finalFormData.firstName,
                    lname: finalFormData.lastName,
                    mname: finalFormData.middleName,
                    business_name: finalFormData.businessName,
                    business_type: finalFormData.businessType,
                    owner_fname: finalFormData.firstName,
                    owner_lname: finalFormData.lastName,
                    organization_name: finalFormData.organizationName,
                    contact_person: finalFormData.contactPerson,
                    registration_number: finalFormData.registrationNumber
                })
            })
            .then(response => {
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response');
                }
                
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success message
                    successMessage.classList.add('show');
                    form.style.display = 'none';
                    
                    // Redirect to dashboard after 2 seconds
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Registration failed');
                }
            })
            .catch(error => {
                console.error('Registration error:', error);
                
                // Show more detailed error message
                let errorMessage = 'Registration failed: ';
                if (error.message.includes('non-JSON response')) {
                    errorMessage += 'Server error. Please check the console for details.';
                } else {
                    errorMessage += error.message;
                }
                
                alert(errorMessage);
            })
            .finally(() => {
                // Reset button state
                registerBtn.classList.remove('loading');
                registerBtn.disabled = false;
            });
        }
    });

    // Initialize form
    showStep(1);
});
