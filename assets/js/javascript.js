document.addEventListener("DOMContentLoaded", function () {

    const toggleButton = document.getElementById('nav-toggle');
    const navlinks = document.getElementById('navbar-content');
    const dropToggle = document.querySelector(".drop-toggle");
    const dropdown = document.querySelector(".dropdown");

    // ==========================
    // Mobile Nav Toggle
    // ==========================
    if (toggleButton && navlinks) {
        toggleButton.addEventListener('click', () => {
            navlinks.classList.toggle('active');
        });
    }

    // ==========================
    // Jobs Dropdown
    // ==========================
    if (dropToggle && dropdown) {
        // Desktop Hover
        dropToggle.addEventListener("mouseenter", () => {
            if (window.innerWidth > 768) {
                dropdown.classList.add("hover-active");
            }
        });

        dropdown.addEventListener("mouseleave", () => {
            if (window.innerWidth > 768) {
                dropdown.classList.remove("hover-active");
            }
        });

        // Click for mobile
        dropToggle.addEventListener("click", function (e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                e.stopPropagation();
                dropdown.classList.toggle("active");
            }
        });
    }

    // ==========================
    // Close Menu When Clicking Outside
    // ==========================
    document.addEventListener("click", (e) => {
        if (
            navlinks &&
            toggleButton &&
            !navlinks.contains(e.target) &&
            !toggleButton.contains(e.target)
        ) {
            navlinks.classList.remove("active");
        }

        if (dropdown && dropdown.contains && !dropdown.contains(e.target)) {
            dropdown.classList.remove("active");
        }
    });

});