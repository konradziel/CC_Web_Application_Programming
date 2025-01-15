document.addEventListener('DOMContentLoaded', function() {
    // Handle menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.store-sidebar');
    const sidebarClose = document.querySelector('.sidebar-toggle');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.add('active');
        });
    }

    if (sidebarClose && sidebar) {
        sidebarClose.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });
    }

    // Handle category tree toggling
    document.querySelectorAll('.category-header').forEach(header => {
        header.addEventListener('click', function(e) {
            // Don't handle clicks on links
            if (e.target.tagName.toLowerCase() === 'a') {
                return;
            }

            const listItem = this.closest('.category-item');
            
            // Only toggle if category has children
            if (listItem.classList.contains('has-children')) {
                e.preventDefault();
                e.stopPropagation();

                // Close siblings at the same level
                const siblings = listItem.parentElement.children;
                Array.from(siblings).forEach(sibling => {
                    if (sibling !== listItem && sibling.classList.contains('has-children') && sibling.classList.contains('active')) {
                        const siblingUl = sibling.querySelector('ul');
                        if (siblingUl) {
                            // Set explicit height before closing
                            siblingUl.style.height = siblingUl.scrollHeight + 'px';
                            // Force reflow
                            siblingUl.offsetHeight;
                            // Start transition to 0
                            siblingUl.style.height = '0px';
                            sibling.classList.remove('active');
                        }
                    }
                });

                // Toggle current category
                const ul = listItem.querySelector('ul');
                if (ul) {
                    if (!listItem.classList.contains('active')) {
                        // Opening
                        listItem.classList.add('active');
                        const height = ul.scrollHeight;
                        ul.style.height = '0px';
                        // Force reflow
                        ul.offsetHeight;
                        ul.style.height = height + 'px';
                        // Remove fixed height after transition
                        ul.addEventListener('transitionend', function handler() {
                            if (listItem.classList.contains('active')) {
                                ul.style.height = 'auto';
                            }
                            ul.removeEventListener('transitionend', handler);
                        });
                    } else {
                        // Closing
                        ul.style.height = ul.scrollHeight + 'px';
                        // Force reflow
                        ul.offsetHeight;
                        ul.style.height = '0px';
                        listItem.classList.remove('active');
                    }
                }
            }
        });
    });

    // Initialize the category tree based on URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const categoryId = urlParams.get('category');
    if (categoryId) {
        const categoryLink = document.querySelector(`a[href*="category=${categoryId}"]`);
        if (categoryLink) {
            let parent = categoryLink.closest('.category-item');
            while (parent) {
                if (parent.classList.contains('has-children')) {
                    parent.classList.add('active');
                }
                parent = parent.parentElement.closest('.category-item');
            }
        }
    }

    // Modal functionality
    const productCards = document.querySelectorAll('.product-clickable');
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.close-modal');

    // Open modal when clicking on product card
    productCards.forEach(card => {
        card.addEventListener('click', function(event) {
            // Don't open modal if clicking on form elements
            if (event.target.closest('.add-to-cart-form')) {
                return;
            }
            const productId = this.closest('.product-card').dataset.productId;
            const modal = document.getElementById(`product-modal-${productId}`);
            if (modal) {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            }
        });
    });

    // Prevent form submission from bubbling up to product card
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    });

    // Close modal when clicking close button
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = ''; // Restore scrolling
            }
        });
    });

    // Close modal when clicking outside
    modals.forEach(modal => {
        modal.addEventListener('click', function(event) {
            if (event.target === this) {
                this.classList.remove('show');
                document.body.style.overflow = ''; // Restore scrolling
            }
        });
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                openModal.classList.remove('show');
                document.body.style.overflow = ''; // Restore scrolling
            }
        }
    });
});
