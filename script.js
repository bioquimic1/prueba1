document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('header nav ul li a[href^="#"]');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Optional: Basic mobile navigation toggle (if we add a burger menu icon later)
    // For now, this part can be minimal or a placeholder comment,
    // as the current HTML/CSS doesn't include a burger icon for mobile.
    // We can expand this if we enhance the mobile menu design.
    const mobileNavToggle = document.querySelector('.mobile-nav-toggle'); // Assuming a class for a toggle button
    const primaryNav = document.querySelector('header nav ul');

    if (mobileNavToggle && primaryNav) {
        mobileNavToggle.addEventListener('click', () => {
            primaryNav.classList.toggle('nav-active');
            // Add ARIA attributes for accessibility if implementing a toggle
        });
    }

    // Highlight active navigation link based on scroll position (optional advanced feature)
    // This is more complex and can be added later if desired.
    // For now, we can keep it simple.

});
