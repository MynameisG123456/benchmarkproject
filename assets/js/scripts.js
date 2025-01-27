document.addEventListener('DOMContentLoaded', function() {
    // Example function to toggle a class on an element
    function toggleClass(element, className) {
        if (element.classList.contains(className)) {
            element.classList.remove(className);
        } else {
            element.classList.add(className);
        }
    }

    // Example usage: Toggle the 'active' class on a navbar item when clicked
    const navLinks = document.querySelectorAll('.nav-links a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navLinks.forEach(link => link.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Show dropdown menu on hover
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('mouseenter', function() {
            const dropdownMenu = this.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.style.display = 'block';
            }
        });

        dropdown.addEventListener('mouseleave', function() {
            const dropdownMenu = this.querySelector('.dropdown-menu');
            if (dropdownMenu) {
                dropdownMenu.style.display = 'none';
            }
        });
    });
});