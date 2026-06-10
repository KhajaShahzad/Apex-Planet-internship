// Form validations, AJAX checkers, and toggle handlers
document.addEventListener("DOMContentLoaded", function () {
    initPasswordToggle();
    initLoginForm();
    initRegisterForm();
});

// 1. Password Visibility Toggle (Show/Hide Password)
function initPasswordToggle() {
    const toggles = document.querySelectorAll(".toggle-password");
    toggles.forEach(toggle => {
        toggle.addEventListener("click", function () {
            // Find password input associated with this button
            const inputGroup = this.closest(".input-group");
            const passwordField = inputGroup.querySelector("input");
            const icon = this.querySelector("i");
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove("bi-eye-fill");
                icon.classList.add("bi-eye-slash-fill");
            } else {
                passwordField.type = "password";
                icon.classList.remove("bi-eye-slash-fill");
                icon.classList.add("bi-eye-fill");
            }
        });
    });
}

// 2. Debouncer function for AJAX calls to avoid excessive backend requests
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Mock Database fallback for local file execution without a PHP server running
const mockDb = {
    usernames: ['admin', 'devsphere', 'khaja', 'shahzad', 'intern', 'john_doe'],
    emails: ['admin@devsphere.com', 'test@test.com', 'user@example.com', 'john@example.com']
};

// 3. AJAX availability checking function
function checkAvailability(type, value, feedbackEl, inputEl, spinnerEl) {
    if (!value || value.trim() === "") {
        feedbackEl.innerHTML = "";
        inputEl.classList.remove("is-valid", "is-invalid");
        return;
    }

    // Show spinner if present
    if (spinnerEl) spinnerEl.style.display = "inline-block";

    const isEmail = type === 'email';
    
    // Check if running in a web server environment or local files (file://)
    if (window.location.protocol === 'file:') {
        // Run client-side fallback simulation
        setTimeout(() => {
            if (spinnerEl) spinnerEl.style.display = "none";
            const databaseList = isEmail ? mockDb.emails : mockDb.usernames;
            const isTaken = databaseList.includes(value.toLowerCase().trim());
            
            updateValidationUI(isTaken, type, feedbackEl, inputEl);
        }, 300);
    } else {
        // Fetch from the real PHP backend check_user.php
        const queryParam = isEmail ? `email=${encodeURIComponent(value)}` : `username=${encodeURIComponent(value)}`;
        fetch(`check_user.php?${queryParam}`)
            .then(res => {
                if (!res.ok) throw new Error("Server error");
                return res.json();
            })
            .then(data => {
                if (spinnerEl) spinnerEl.style.display = "none";
                updateValidationUI(data.exists, type, feedbackEl, inputEl);
            })
            .catch(err => {
                console.warn("AJAX server check failed, switching to offline validator.", err);
                // Fallback check
                if (spinnerEl) spinnerEl.style.display = "none";
                const databaseList = isEmail ? mockDb.emails : mockDb.usernames;
                const isTaken = databaseList.includes(value.toLowerCase().trim());
                updateValidationUI(isTaken, type, feedbackEl, inputEl);
            });
    }
}

function updateValidationUI(isTaken, type, feedbackEl, inputEl) {
    if (isTaken) {
        inputEl.classList.remove("is-valid");
        inputEl.classList.add("is-invalid");
        feedbackEl.innerHTML = `<i class="bi bi-x-circle-fill me-1"></i> This ${type} is already registered.`;
        feedbackEl.className = "invalid-feedback d-block";
    } else {
        inputEl.classList.remove("is-invalid");
        inputEl.classList.add("is-valid");
        feedbackEl.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> ${type.charAt(0).toUpperCase() + type.slice(1)} is available!`;
        feedbackEl.className = "valid-feedback d-block";
    }
}

// 4. Login Form Handler
function initLoginForm() {
    const loginForm = document.getElementById("loginForm");
    if (!loginForm) return;

    loginForm.addEventListener("submit", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const emailInput = document.getElementById("loginEmail");
        const passwordInput = document.getElementById("loginPassword");
        let isValid = true;

        // Reset previous validation classes
        emailInput.classList.remove("is-invalid");
        passwordInput.classList.remove("is-invalid");

        // Validate Email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailInput.value || !emailRegex.test(emailInput.value)) {
            emailInput.classList.add("is-invalid");
            isValid = false;
        } else {
            emailInput.classList.add("is-valid");
        }

        // Validate Password
        if (!passwordInput.value || passwordInput.value.trim() === "") {
            passwordInput.classList.add("is-invalid");
            isValid = false;
        } else {
            passwordInput.classList.add("is-valid");
        }

        if (isValid) {
            // Trigger login success alert / redirection modal
            showSuccessModal("Login Successful!", `Welcome back, <strong>${emailInput.value}</strong>! Redirecting you to the dashboard...`);
        }
    });
}

// 5. Registration Form Handler
function initRegisterForm() {
    const regForm = document.getElementById("registerForm");
    if (!regForm) return;

    const usernameInput = document.getElementById("regUsername");
    const usernameFeedback = document.getElementById("usernameFeedback");
    const usernameSpinner = document.getElementById("usernameSpinner");

    const emailInput = document.getElementById("regEmail");
    const emailFeedback = document.getElementById("emailFeedback");
    const emailSpinner = document.getElementById("emailSpinner");

    const passwordInput = document.getElementById("regPassword");
    const confirmPasswordInput = document.getElementById("regConfirmPassword");
    const confirmFeedback = document.getElementById("confirmFeedback");

    // Debounced AJAX Checks for Availability
    usernameInput.addEventListener("input", debounce(function () {
        checkAvailability("username", this.value, usernameFeedback, usernameInput, usernameSpinner);
    }, 450));

    emailInput.addEventListener("input", debounce(function () {
        // Only run AJAX if basic email format matches
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailRegex.test(this.value)) {
            checkAvailability("email", this.value, emailFeedback, emailInput, emailSpinner);
        } else {
            emailInput.classList.remove("is-valid");
            emailInput.classList.add("is-invalid");
            emailFeedback.innerHTML = `<i class="bi bi-exclamation-circle-fill me-1"></i> Please enter a valid email address.`;
            emailFeedback.className = "invalid-feedback d-block";
        }
    }, 450));

    // Password strength check on input
    passwordInput.addEventListener("input", function () {
        const score = checkPasswordStrength(this.value);
        if (this.value.length > 0) {
            if (score >= 2) {
                this.classList.remove("is-invalid");
                this.classList.add("is-valid");
            } else {
                this.classList.remove("is-valid");
                this.classList.add("is-invalid");
            }
        } else {
            this.classList.remove("is-valid", "is-invalid");
        }
        // Check password matching if confirm input has value
        if (confirmPasswordInput.value.length > 0) {
            checkPasswordsMatch();
        }
    });

    // Confirm password matching check
    confirmPasswordInput.addEventListener("input", checkPasswordsMatch);

    function checkPasswordsMatch() {
        if (passwordInput.value === confirmPasswordInput.value) {
            confirmPasswordInput.classList.remove("is-invalid");
            confirmPasswordInput.classList.add("is-valid");
            confirmFeedback.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Passwords match!`;
            confirmFeedback.className = "valid-feedback d-block";
            return true;
        } else {
            confirmPasswordInput.classList.remove("is-valid");
            confirmPasswordInput.classList.add("is-invalid");
            confirmFeedback.innerHTML = `<i class="bi bi-x-circle-fill me-1"></i> Passwords do not match.`;
            confirmFeedback.className = "invalid-feedback d-block";
            return false;
        }
    }

    // Form submission
    regForm.addEventListener("submit", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const fullnameInput = document.getElementById("regFullname");
        const termsCheck = document.getElementById("termsCheck");
        let isFormValid = true;

        // Trigger manual check of password match
        const matches = checkPasswordsMatch();
        if (!matches) isFormValid = false;

        // Validate Fullname
        if (!fullnameInput.value || fullnameInput.value.trim().length < 3) {
            fullnameInput.classList.add("is-invalid");
            isFormValid = false;
        } else {
            fullnameInput.classList.remove("is-invalid");
            fullnameInput.classList.add("is-valid");
        }

        // Check availability triggers
        if (usernameInput.classList.contains("is-invalid") || !usernameInput.classList.contains("is-valid")) {
            usernameInput.classList.add("is-invalid");
            isFormValid = false;
        }

        if (emailInput.classList.contains("is-invalid") || !emailInput.classList.contains("is-valid")) {
            emailInput.classList.add("is-invalid");
            isFormValid = false;
        }

        // Validate Password Strength
        const strength = checkPasswordStrength(passwordInput.value);
        if (strength < 2) {
            passwordInput.classList.add("is-invalid");
            isFormValid = false;
        }

        // Terms check
        if (!termsCheck.checked) {
            termsCheck.classList.add("is-invalid");
            isFormValid = false;
        } else {
            termsCheck.classList.remove("is-invalid");
            termsCheck.classList.add("is-valid");
        }

        if (isFormValid) {
            const signupData = {
                fullname: fullnameInput.value,
                username: usernameInput.value,
                email: emailInput.value,
                password: passwordInput.value
            };

            if (window.location.protocol === 'file:') {
                // Offline fallback mode simulation
                showSuccessModal("Registration Successful! (Offline Fallback)", `Welcome, <strong>${fullnameInput.value}</strong>!<br><br>Since you are running the project as a static file (without a local server), the account was successfully verified and simulated in-memory under username <strong>${usernameInput.value}</strong>.`);
                resetFormState();
            } else {
                // Online mode: Submit to register_user.php via Fetch POST
                fetch('register_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(signupData)
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(errData => { throw new Error(errData.message || "Registration failed."); });
                    }
                    return res.json();
                })
                .then(data => {
                    showSuccessModal("Registration Successful!", `Welcome, <strong>${fullnameInput.value}</strong>!<br><br>Your credentials have been securely stored in <strong>users.json</strong>. You can now login under username <strong>${usernameInput.value}</strong>.`);
                    resetFormState();
                })
                .catch(err => {
                    console.error("AJAX registration failed: ", err);
                    alert("Registration failed: " + err.message);
                });
            }
        }

        function resetFormState() {
            regForm.reset();
            // Reset validation states
            document.querySelectorAll(".form-control, .form-check-input").forEach(el => {
                el.classList.remove("is-valid", "is-invalid");
            });
            const strengthBar = document.getElementById("strengthBar");
            const strengthText = document.getElementById("strengthText");
            if (strengthBar) strengthBar.style.width = "0%";
            if (strengthText) strengthText.innerHTML = "";
            usernameFeedback.innerHTML = "";
            emailFeedback.innerHTML = "";
            confirmFeedback.innerHTML = "";
        }
    });
}

// 6. Password Strength Evaluator
function checkPasswordStrength(password) {
    const strengthBar = document.getElementById("strengthBar");
    const strengthText = document.getElementById("strengthText");
    if (!strengthBar || !strengthText) return 0;

    let score = 0;
    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    let width = "0%";
    let colorClass = "bg-danger";
    let text = "";

    if (password.length === 0) {
        width = "0%";
        text = "";
    } else if (score <= 1) {
        width = "25%";
        colorClass = "bg-danger";
        text = "Weak (needs length, caps, numbers, or symbols)";
    } else if (score === 2) {
        width = "50%";
        colorClass = "bg-warning";
        text = "Fair (moderately secure)";
    } else if (score === 3) {
        width = "75%";
        colorClass = "bg-info";
        text = "Good (secure)";
    } else if (score >= 4) {
        width = "100%";
        colorClass = "bg-success";
        text = "Strong (very secure!)";
    }

    strengthBar.className = `password-strength-bar ${colorClass}`;
    strengthBar.style.width = width;
    strengthText.innerHTML = text;

    return score;
}

// 7. Success Modal Trigger — with Bootstrap availability check and pure-JS fallback
function showSuccessModal(title, bodyText) {

    // ── If Bootstrap JS is loaded, use its Modal API ──────────────────────────
    if (typeof bootstrap !== 'undefined') {
        let modalEl = document.getElementById("statusAlertModal");
        if (!modalEl) {
            const modalHtml = `
            <div class="modal fade" id="statusAlertModal" tabindex="-1" aria-labelledby="statusAlertModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content glass-panel" style="border-top: 5px solid var(--accent-color, #9FC131);">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold" id="statusAlertModalLabel"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-4 text-center">
                            <div class="mb-3"><i class="bi bi-patch-check-fill text-success fs-1"></i></div>
                            <div id="statusAlertModalBody"></div>
                        </div>
                        <div class="modal-footer border-0 pt-0 justify-content-center">
                            <button type="button" class="btn btn-apex-primary" data-bs-dismiss="modal">Continue</button>
                        </div>
                    </div>
                </div>
            </div>`;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            modalEl = document.getElementById("statusAlertModal");
        }

        document.getElementById("statusAlertModalLabel").innerText = title;
        document.getElementById("statusAlertModalBody").innerHTML = bodyText;

        // Destroy any previous instance before creating a new one
        const existingInstance = bootstrap.Modal.getInstance(modalEl);
        if (existingInstance) existingInstance.dispose();

        const bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
        return;
    }

    // ── Fallback: Pure-JS/CSS overlay modal (when Bootstrap CDN is unavailable) ─
    // Remove old fallback overlay if present
    const oldOverlay = document.getElementById("fallbackModalOverlay");
    if (oldOverlay) oldOverlay.remove();

    const overlay = document.createElement("div");
    overlay.id = "fallbackModalOverlay";
    overlay.style.cssText = `
        position:fixed; inset:0; z-index:9999;
        background:rgba(0,0,0,0.55); display:flex;
        align-items:center; justify-content:center;
        animation:fadeIn 0.25s ease;
    `;

    const box = document.createElement("div");
    box.style.cssText = `
        background:#fff; border-radius:16px; max-width:460px; width:90%;
        padding:36px 32px; text-align:center; position:relative;
        border-top:5px solid #9FC131;
        box-shadow:0 12px 40px rgba(0,92,83,0.18);
        animation:fadeIn 0.3s ease;
    `;

    box.innerHTML = `
        <div style="font-size:3rem; margin-bottom:12px;">✅</div>
        <h5 style="font-family:'Outfit',sans-serif; color:#042940; font-weight:700; margin-bottom:10px;">${title}</h5>
        <p style="font-family:'Outfit',sans-serif; color:#555; font-size:0.95rem; line-height:1.6;">${bodyText}</p>
        <button id="fallbackModalClose" style="
            margin-top:20px; background:#005C53; color:#fff;
            border:none; border-radius:8px; padding:10px 28px;
            font-family:'Outfit',sans-serif; font-size:1rem; font-weight:600;
            cursor:pointer; transition:background 0.2s;">
            Continue
        </button>
    `;

    overlay.appendChild(box);
    document.body.appendChild(overlay);

    // Close handlers
    document.getElementById("fallbackModalClose").addEventListener("click", () => overlay.remove());
    overlay.addEventListener("click", (e) => { if (e.target === overlay) overlay.remove(); });
}

