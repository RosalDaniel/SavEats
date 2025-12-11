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
                    placeholder="09123456789"
                    required
                    autocomplete="tel"
                    aria-describedby="phone-error"
                >
                <div class="error-message" id="phone-error" role="alert"></div>
            </div>
        `,
        business: `
            <div class="form-row">
                <div class="form-group">
                    <label for="firstName" class="form-label required">Owner First Name</label>
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
                    <label for="lastName" class="form-label required">Owner Last Name</label>
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
            </div>

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
                    placeholder="09123456789"
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
                    placeholder="09123456789"
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
        
        // Handle address field visibility based on account type (Step 3)
        if (step === 3) {
            const establishmentAddressContainer = document.getElementById('establishmentAddressContainer');
            const simpleAddressContainer = document.getElementById('simpleAddressContainer');
            
            if (selectedAccountType === 'business') {
                // Show map-based address for establishments
                if (establishmentAddressContainer) establishmentAddressContainer.style.display = 'block';
                if (simpleAddressContainer) simpleAddressContainer.style.display = 'none';
                
                // Initialize map for establishments only
                setTimeout(() => {
                    initializeAddressMap();
                }, 100);
            } else {
                // Show simple text input for consumers and foodbanks
                if (establishmentAddressContainer) establishmentAddressContainer.style.display = 'none';
                if (simpleAddressContainer) simpleAddressContainer.style.display = 'block';
                
                // Destroy map if it exists (for non-establishments)
                if (addressMap) {
                    addressMap.remove();
                    addressMap = null;
                    addressMarker = null;
                }
            }
        }
    }

    // Validation functions
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function validatePhone(phone) {
        // Format: 09123456789 (exactly 11 digits, starting with 0)
        const cleaned = phone.replace(/\D/g, '');
        if (cleaned.length < 11 || cleaned.length > 12) {
            return false;
        }
        const re = /^0\d{10}$/;
        return re.test(cleaned);
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
                validateField(phone, 'phone-error', validatePhone, 'Please enter a valid phone number (11 digits, format: 09123456789)')
            ];
        } else if (selectedAccountType === 'business') {
            const firstName = document.getElementById('firstName');
            const lastName = document.getElementById('lastName');
            const businessName = document.getElementById('businessName');
            const businessType = document.getElementById('businessType');
            const birCertificate = document.getElementById('birCertificate');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');

            validations = [
                validateField(firstName, 'firstName-error', val => val.length >= 2, 'First name must be at least 2 characters'),
                validateField(lastName, 'lastName-error', val => val.length >= 2, 'Last name must be at least 2 characters'),
                validateField(businessName, 'businessName-error', val => val.length >= 2, 'Business name must be at least 2 characters'),
                validateField(businessType, 'businessType-error', val => val !== '', 'Please select a business type'),
                validateField(email, 'email-error', validateEmail, 'Please enter a valid email address'),
                validateField(phone, 'phone-error', validatePhone, 'Please enter a valid phone number (11 digits, format: 09123456789)')
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
                validateField(phone, 'phone-error', validatePhone, 'Please enter a valid phone number (11 digits, format: 09123456789)')
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
        const terms = document.getElementById('terms');

        const validations = [
            validateField(username, 'username-error', val => val.length >= 3, 'Username must be at least 3 characters'),
            validateField(password, 'password-error', validatePassword, 'Password must be at least 8 characters'),
            validateField(confirmPassword, 'confirmPassword-error', val => val === password.value, 'Passwords do not match')
        ];

        // Validate location - only required for business accounts
        if (selectedAccountType === 'business') {
            const location = document.getElementById('location');
            const latitude = document.getElementById('latitude');
            const longitude = document.getElementById('longitude');
            const locationError = document.getElementById('location-error');
            
            if (!latitude?.value || !longitude?.value || !location?.value || location.value === 'Address will be filled from map selection' || location.value === 'Loading address...') {
                locationError.textContent = 'Please select your location on the map';
                location.closest('.form-group')?.classList.add('has-error');
                validations.push(false);
            } else {
                locationError.textContent = '';
                location.closest('.form-group')?.classList.remove('has-error');
                validations.push(true);
            }
        }

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
            // Initialize map when step 3 is shown
            setTimeout(() => {
                initializeAddressMap();
            }, 100);
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
                email: document.getElementById('email').value.trim(),
                phone: document.getElementById('phone').value.trim(),
                terms: document.getElementById('terms').checked
            };

            // Add specific fields based on account type
            if (selectedAccountType === 'consumer') {
                finalFormData.firstName = document.getElementById('firstName').value.trim();
                finalFormData.middleName = document.getElementById('middleName').value.trim();
                finalFormData.lastName = document.getElementById('lastName').value.trim();
            } else if (selectedAccountType === 'business') {
                finalFormData.firstName = document.getElementById('firstName').value.trim();
                finalFormData.lastName = document.getElementById('lastName').value.trim();
                finalFormData.businessName = document.getElementById('businessName').value.trim();
                finalFormData.businessType = document.getElementById('businessType').value;
                finalFormData.birCertificate = document.getElementById('birCertificate').files[0];
            } else if (selectedAccountType === 'foodbank') {
                finalFormData.organizationName = document.getElementById('organizationName').value.trim();
                finalFormData.serviceArea = document.getElementById('serviceArea').value.trim();
                finalFormData.orgRegistration = document.getElementById('orgRegistration').files[0];
                // Map serviceArea to contact_person for backend compatibility
                finalFormData.contactPerson = finalFormData.serviceArea || finalFormData.organizationName;
                finalFormData.registrationNumber = '';
            }

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('CSRF Token:', csrfToken);
            
            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }

            // Check if there are files to upload
            const hasFiles = finalFormData.birCertificate || finalFormData.orgRegistration;
            
            let requestBody;
            let headers;
            
            if (hasFiles) {
                // Use FormData for file uploads
                const formData = new FormData();
                formData.append('role', selectedAccountType === 'business' ? 'establishment' : selectedAccountType);
                formData.append('username', finalFormData.username);
                formData.append('password', finalFormData.password);
                formData.append('password_confirmation', finalFormData.password);
                formData.append('email', finalFormData.email);
                formData.append('phone_no', finalFormData.phone);
                
                // Handle address based on account type
                if (selectedAccountType === 'business') {
                    // For establishments: use map-based location (required)
                    const location = document.getElementById('location')?.value || '';
                    const latitude = document.getElementById('latitude')?.value;
                    const longitude = document.getElementById('longitude')?.value;
                    
                    formData.append('address', location);
                    if (latitude && longitude) {
                        formData.append('latitude', latitude);
                        formData.append('longitude', longitude);
                        formData.append('formatted_address', location); // Store formatted address
                    }
                } else {
                    // For consumers and foodbanks: use simple text input (optional)
                    const simpleAddress = document.getElementById('simpleAddress')?.value || '';
                    formData.append('address', simpleAddress);
                }
                formData.append('fname', finalFormData.firstName);
                formData.append('lname', finalFormData.lastName);
                formData.append('mname', finalFormData.middleName);
                formData.append('business_name', finalFormData.businessName);
                formData.append('business_type', finalFormData.businessType);
                formData.append('owner_fname', finalFormData.firstName);
                formData.append('owner_lname', finalFormData.lastName);
                formData.append('organization_name', finalFormData.organizationName);
                // Use serviceArea as contact_person since form doesn't have a separate contact person field
                formData.append('contact_person', finalFormData.serviceArea || finalFormData.organizationName);
                // Registration number is optional, use empty string if not provided
                formData.append('registration_number', '');
                
                if (finalFormData.birCertificate) {
                    formData.append('birCertificate', finalFormData.birCertificate);
                }
                if (finalFormData.orgRegistration) {
                    formData.append('orgRegistration', finalFormData.orgRegistration);
                }
                
                requestBody = formData;
                headers = {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                };
                console.log('Sending FormData with files');
            } else {
                // Use JSON for non-file data
                const requestData = {
                    role: selectedAccountType === 'business' ? 'establishment' : selectedAccountType,
                    username: finalFormData.username,
                    password: finalFormData.password,
                    password_confirmation: finalFormData.password,
                    email: finalFormData.email,
                    phone_no: finalFormData.phone,
                };
                
                // Handle address based on account type
                if (selectedAccountType === 'business') {
                    // For establishments: use map-based location (required)
                    const location = document.getElementById('location')?.value || '';
                    const latitude = document.getElementById('latitude')?.value;
                    const longitude = document.getElementById('longitude')?.value;
                    
                    requestData.address = location;
                    if (latitude && longitude) {
                        requestData.latitude = latitude;
                        requestData.longitude = longitude;
                        requestData.formatted_address = location; // Store formatted address
                    }
                } else {
                    // For consumers and foodbanks: use simple text input (optional)
                    const simpleAddress = document.getElementById('simpleAddress')?.value || '';
                    requestData.address = simpleAddress;
                }
                
                // Add other fields
                requestData.fname = finalFormData.firstName;
                requestData.lname = finalFormData.lastName;
                requestData.mname = finalFormData.middleName;
                requestData.business_name = finalFormData.businessName;
                requestData.business_type = finalFormData.businessType;
                requestData.owner_fname = finalFormData.firstName;
                requestData.owner_lname = finalFormData.lastName;
                requestData.organization_name = finalFormData.organizationName;
                // Use serviceArea as contact_person since form doesn't have a separate contact person field
                requestData.contact_person = finalFormData.serviceArea || finalFormData.organizationName;
                // Registration number is optional, use empty string if not provided
                requestData.registration_number = '';
                console.log('Sending registration data:', requestData);
                
                requestBody = JSON.stringify(requestData);
                headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                };
            }

            // Send data to Laravel backend
            fetch('/register', {
                method: 'POST',
                headers: headers,
                body: requestBody
            })
            .then(response => {
                // Check if response is ok
                if (!response.ok) {
                    // Log the response for debugging
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    return response.json().then(data => {
                        console.log('Error response data:', data);
                        console.log('Validation errors:', data.errors);
                        
                        // Create a more detailed error message
                        let errorMessage = data.message || 'Validation failed';
                        if (data.errors) {
                            const errorMessages = [];
                            Object.keys(data.errors).forEach(field => {
                                errorMessages.push(`${field}: ${data.errors[field].join(', ')}`);
                            });
                            errorMessage += ' - ' + errorMessages.join('; ');
                        }
                        
                        throw new Error(`HTTP error! status: ${response.status}, message: ${errorMessage}`);
                    });
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
                } else if (error.message.includes('422')) {
                    errorMessage += 'Email or username already exists. Please use different credentials.';
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

    // Address Map Functionality
    let addressMap = null;
    let addressMarker = null;

    function initializeAddressMap() {
        const mapElement = document.getElementById('addressMap');
        if (!mapElement || addressMap) return; // Already initialized

        // Initialize map centered on Philippines
        addressMap = L.map('addressMap').setView([12.8797, 121.7740], 6);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(addressMap);

        // Add click event to map
        addressMap.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            // Update hidden inputs
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            // Remove existing marker
            if (addressMarker) {
                addressMap.removeLayer(addressMarker);
            }

            // Add new marker (using custom icon without shadow to avoid tracking prevention warnings)
            const customIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34]
            });
            
            addressMarker = L.marker([lat, lng], { icon: customIcon })
                .addTo(addressMap)
                .bindPopup('Selected Location')
                .openPopup();

            // Reverse geocode to get address
            reverseGeocode(lat, lng);
        });

        // Try to get user's current location (optional - not required)
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    addressMap.setView([lat, lng], 13);
                },
                function(error) {
                    // Silently handle geolocation errors - user can still select location manually
                    // Error codes: 1 = permission denied, 2 = position unavailable, 3 = timeout
                    // This is expected behavior and doesn't affect functionality
                },
                {
                    timeout: 5000,
                    enableHighAccuracy: false
                }
            );
        }
    }

    function reverseGeocode(lat, lng) {
        const locationInput = document.getElementById('location');
        const locationError = document.getElementById('location-error');
        
        // Show loading state
        locationInput.value = 'Loading address...';
        locationInput.disabled = true;

        // Use Nominatim reverse geocoding API
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;

        fetch(url, {
            headers: {
                'User-Agent': 'SavEats Application'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.address) {
                // Build formatted address
                const address = data.address;
                let formattedAddress = '';

                // Build address from components
                if (address.road || address.street) {
                    formattedAddress += (address.road || address.street) + ', ';
                }
                if (address.suburb || address.neighbourhood) {
                    formattedAddress += (address.suburb || address.neighbourhood) + ', ';
                }
                if (address.city || address.town || address.village) {
                    formattedAddress += (address.city || address.town || address.village) + ', ';
                }
                if (address.state) {
                    formattedAddress += address.state + ', ';
                }
                if (address.postcode) {
                    formattedAddress += address.postcode + ' ';
                }
                if (address.country) {
                    formattedAddress += address.country;
                }

                // Clean up trailing commas and spaces
                formattedAddress = formattedAddress.replace(/,\s*$/, '').trim();

                // If no formatted address, use display_name
                if (!formattedAddress && data.display_name) {
                    formattedAddress = data.display_name;
                }

                // Update input field
                locationInput.value = formattedAddress;
                locationInput.disabled = false;

                // Clear any errors
                locationError.textContent = '';
                locationInput.closest('.form-group')?.classList.remove('has-error');
            } else {
                // Fallback if reverse geocoding fails
                locationInput.value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
                locationInput.disabled = false;
            }
        })
        .catch(error => {
            console.error('Reverse geocoding error:', error);
            // Fallback to coordinates
            locationInput.value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
            locationInput.disabled = false;
        });
    }

    // Initialize form
    showStep(1);
});
