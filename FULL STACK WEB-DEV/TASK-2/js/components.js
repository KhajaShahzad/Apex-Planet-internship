document.addEventListener("DOMContentLoaded", function () {
    loadNavbar();
    loadFooter();
});

// Hardcoded HTML string fallbacks to handle local file:// protocol CORS issues
const navbarHTMLFallback = `
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.html">
            <i class="bi bi-terminal me-2 text-info"></i>
            Dev<span style="color:#9FC131;">Sphere</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item px-2">
                    <a class="nav-link" id="nav-home" href="index.html">Home</a>
                </li>
                <li class="nav-item px-2">
                    <a class="nav-link" id="nav-login" href="login.html">Login</a>
                </li>
                <li class="nav-item px-2">
                    <a class="nav-link" id="nav-register" href="register.html">Register</a>
                </li>
                <li class="nav-item px-2 ms-lg-3">
                    <a class="btn btn-apex-accent btn-sm" href="register.html">Get Started</a>
                </li>
            </ul>
        </div>
    </div>
</nav>`;

const footerHTMLFallback = `
<footer class="footer mt-auto py-5 bg-dark text-white-50">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h5 class="text-white mb-3">
                    <i class="bi bi-terminal me-2 text-info"></i>Dev<span class="text-success">Sphere</span>
                </h5>
                <p class="small">A premium interactive portal featuring responsive dashboards, custom JS validations, and asynchronous AJAX checking checks. Designed and developed by Khaja Shahzad.</p>
                <div class="d-flex gap-3 fs-5 mt-3">
                    <a href="#" class="text-white-50"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white-50"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-white-50"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="text-white-50"><i class="bi bi-github"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5 class="text-white mb-3">Quick Links</h5>
                <ul class="list-unstyled text-small">
                    <li><a href="index.html" class="link-secondary">Home</a></li>
                    <li><a href="login.html" class="link-secondary">Login</a></li>
                    <li><a href="register.html" class="link-secondary">Register</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white mb-3">Core Stack</h5>
                <ul class="list-unstyled text-small">
                    <li><a href="#" class="link-secondary">HTML5 & CSS3</a></li>
                    <li><a href="#" class="link-secondary">Bootstrap 5</a></li>
                    <li><a href="#" class="link-secondary">JavaScript (ES6)</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white mb-3">Developer</h5>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2"><i class="bi bi-person me-2 text-info"></i>Khaja Shahzad</li>
                    <li class="mb-2"><i class="bi bi-envelope me-2 text-info"></i>khaja.shahzad@example.com</li>
                    <li class="mb-2"><i class="bi bi-github me-2 text-info"></i>github.com/KhajaShahzad</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start small">
                &copy; 2026 DevSphere. Designed & Developed by <strong>Khaja Shahzad</strong>.
            </div>
            <div class="col-md-6 text-center text-md-end small mt-2 mt-md-0">
                <a href="#" class="link-secondary me-3">Privacy Policy</a>
                <a href="#" class="link-secondary">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>`;

function loadNavbar() {
    const placeholder = document.getElementById("navbar-placeholder");
    if (!placeholder) return;

    if (window.location.protocol === 'file:') {
        // Fallback for local files to avoid CORS error
        placeholder.innerHTML = navbarHTMLFallback;
        highlightActiveNavItem();
    } else {
        fetch("components/navbar.html")
            .then(response => {
                if (!response.ok) throw new Error("Navbar load failed");
                return response.text();
            })
            .then(data => {
                placeholder.innerHTML = data;
                highlightActiveNavItem();
            })
            .catch(err => {
                console.warn("AJAX navbar load failed, loading fallback: ", err);
                placeholder.innerHTML = navbarHTMLFallback;
                highlightActiveNavItem();
            });
    }
}

function loadFooter() {
    const placeholder = document.getElementById("footer-placeholder");
    if (!placeholder) return;

    if (window.location.protocol === 'file:') {
        // Fallback for local files to avoid CORS error
        placeholder.innerHTML = footerHTMLFallback;
    } else {
        fetch("components/footer.html")
            .then(response => {
                if (!response.ok) throw new Error("Footer load failed");
                return response.text();
            })
            .then(data => {
                placeholder.innerHTML = data;
            })
            .catch(err => {
                console.warn("AJAX footer load failed, loading fallback: ", err);
                placeholder.innerHTML = footerHTMLFallback;
            });
    }
}

function highlightActiveNavItem() {
    const path = window.location.pathname;
    const page = path.split("/").pop();
    
    // Default to home if page is empty (e.g. folder root '/')
    let activeId = "nav-home";
    if (page === "login.html") {
        activeId = "nav-login";
    } else if (page === "register.html") {
        activeId = "nav-register";
    }
    
    const activeEl = document.getElementById(activeId);
    if (activeEl) {
        activeEl.classList.add("active");
        activeEl.setAttribute("aria-current", "page");
    }
}
