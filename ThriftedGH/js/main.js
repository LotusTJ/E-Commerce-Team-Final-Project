/**
 * THRIFTEDGH - MAIN JAVASCRIPT
 * University E-Commerce Project (Vanilla JS)
 * 
 * Features:
 * 1. Mobile navigation menu toggle with keyboard accessibility.
 * 2. Client-side catalog search and filtering (Shop page).
 * 3. Transparent placeholder alerts for future tasks (Cart, Checkout, Auth).
 */

document.addEventListener('DOMContentLoaded', function () {
    initMobileNav();
    initShopFilters();
    initPlaceholderButtons();
});

/* ==========================================================================
   1. MOBILE NAVIGATION TOGGLE
   ========================================================================== */
function initMobileNav() {
    const menuBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNav');

    if (!menuBtn || !mainNav) return;

    menuBtn.addEventListener('click', function () {
        const isOpen = menuBtn.classList.toggle('open');
        mainNav.classList.toggle('active');
        menuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    // Close mobile nav when clicking outside
    document.addEventListener('click', function (event) {
        if (!mainNav.contains(event.target) && !menuBtn.contains(event.target)) {
            if (mainNav.classList.contains('active')) {
                mainNav.classList.remove('active');
                menuBtn.classList.remove('open');
                menuBtn.setAttribute('aria-expanded', 'false');
            }
        }
    });

    // Close on Escape key press
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && mainNav.classList.contains('active')) {
            mainNav.classList.remove('active');
            menuBtn.classList.remove('open');
            menuBtn.setAttribute('aria-expanded', 'false');
        }
    });
}

/* ==========================================================================
   2. SHOP CATALOG SEARCH & REAL-TIME FILTERS
   ========================================================================== */
function initShopFilters() {
    const searchInput = document.getElementById('shopSearchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const categoryFilter = document.getElementById('categoryFilter');
    const sizeFilter = document.getElementById('sizeFilter');
    const sortOrder = document.getElementById('sortOrder');
    const resetBtn = document.getElementById('resetFiltersBtn');
    const grid = document.getElementById('catalogProductGrid');
    const resultsCount = document.getElementById('resultsCount');
    const noResultsMsg = document.getElementById('noResultsMsg');

    if (!grid) return; // Not on shop page

    const productCards = Array.from(grid.querySelectorAll('.product-card'));

    function applyFilters() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const selectedCat = categoryFilter ? categoryFilter.value : 'all';
        const selectedSize = sizeFilter ? sizeFilter.value : 'all';

        // Toggle clear button
        if (clearSearchBtn) {
            clearSearchBtn.style.display = query.length > 0 ? 'block' : 'none';
        }

        let visibleCount = 0;

        productCards.forEach(function (card) {
            const titleElem = card.querySelector('.product-title');
            const titleText = titleElem ? titleElem.textContent.toLowerCase() : '';
            const cardCat = card.getAttribute('data-category') || '';
            const cardSize = card.getAttribute('data-size') || '';

            const matchesQuery = query === '' || titleText.includes(query);
            const matchesCat = selectedCat === 'all' || cardCat.toLowerCase() === selectedCat.toLowerCase();
            const matchesSize = selectedSize === 'all' || cardSize.toLowerCase() === selectedSize.toLowerCase();

            if (matchesQuery && matchesCat && matchesSize) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Update results counter
        if (resultsCount) {
            resultsCount.innerHTML = `Showing <strong>${visibleCount}</strong> unique items`;
        }

        // Show/hide empty state
        if (noResultsMsg) {
            noResultsMsg.style.display = (visibleCount === 0) ? 'block' : 'none';
        }
    }

    // Sort Handler
    function applySort() {
        if (!sortOrder) return;
        const sortVal = sortOrder.value;

        const sortedCards = productCards.slice().sort(function (a, b) {
            const priceA = parseFloat(a.querySelector('.amount').textContent.replace(/,/g, '')) || 0;
            const priceB = parseFloat(b.querySelector('.amount').textContent.replace(/,/g, '')) || 0;

            if (sortVal === 'price-asc') {
                return priceA - priceB;
            } else if (sortVal === 'price-desc') {
                return priceB - priceA;
            }
            return 0; // default order
        });

        sortedCards.forEach(function (card) {
            grid.appendChild(card);
        });
    }

    // Attach Event Listeners
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function () {
            searchInput.value = '';
            applyFilters();
            searchInput.focus();
        });
    }

    if (categoryFilter) {
        categoryFilter.addEventListener('change', applyFilters);
    }

    if (sizeFilter) {
        sizeFilter.addEventListener('change', applyFilters);
    }

    if (sortOrder) {
        sortOrder.addEventListener('change', applySort);
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', window.resetAllFilters);
    }
}

// Global reset helper for empty state button
window.resetAllFilters = function () {
    const searchInput = document.getElementById('shopSearchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const sizeFilter = document.getElementById('sizeFilter');
    const sortOrder = document.getElementById('sortOrder');
    const clearSearchBtn = document.getElementById('clearSearchBtn');

    if (searchInput) searchInput.value = '';
    if (clearSearchBtn) clearSearchBtn.style.display = 'none';
    if (categoryFilter) categoryFilter.value = 'all';
    if (sizeFilter) sizeFilter.value = 'all';
    if (sortOrder) sortOrder.value = 'default';

    const grid = document.getElementById('catalogProductGrid');
    if (grid) {
        const cards = grid.querySelectorAll('.product-card');
        cards.forEach(card => card.style.display = 'flex');
        const count = document.getElementById('resultsCount');
        if (count) count.innerHTML = `Showing <strong>${cards.length}</strong> unique items`;
        const noResults = document.getElementById('noResultsMsg');
        if (noResults) noResults.style.display = 'none';
    }
};

/* ==========================================================================
   3. PLACEHOLDER MODAL / NOTIFICATION (Academic Requirement)
   ========================================================================== */
function initPlaceholderButtons() {
    const placeholderBtns = document.querySelectorAll('.placeholder-action-btn');

    placeholderBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const actionName = btn.getAttribute('data-action-name') || 'This action';
            const taskInfo = btn.getAttribute('data-task-info') || 'Subsequent tasks';
            showPlaceholderAlert(actionName, taskInfo);
        });
    });
}

window.showPlaceholderAlert = function (featureName, taskAssignment) {
    // Remove existing modal if any
    const existing = document.getElementById('placeholderModalBackdrop');
    if (existing) existing.remove();

    const backdrop = document.createElement('div');
    backdrop.id = 'placeholderModalBackdrop';
    backdrop.className = 'placeholder-modal-backdrop';

    backdrop.innerHTML = `
        <div class="placeholder-modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <div class="placeholder-modal-icon">🛠️</div>
            <h3 id="modalTitle" class="placeholder-modal-title">${featureName}</h3>
            <p class="placeholder-modal-text">
                This is a <strong>Task 2 frontend prototype</strong>. 
                Full backend logic and MySQL database connection for this feature will be implemented in <strong>${taskAssignment}</strong>.
            </p>
            <button type="button" class="btn btn-primary btn-block" id="closePlaceholderModalBtn">
                Understood, Return to Demo
            </button>
        </div>
    `;

    document.body.appendChild(backdrop);

    // Focus close button
    const closeBtn = document.getElementById('closePlaceholderModalBtn');
    if (closeBtn) {
        closeBtn.focus();
        closeBtn.addEventListener('click', function () {
            backdrop.remove();
        });
    }

    // Close on backdrop click
    backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) backdrop.remove();
    });

    // Close on Escape
    const escListener = function (e) {
        if (e.key === 'Escape') {
            backdrop.remove();
            document.removeEventListener('keydown', escListener);
        }
    };
    document.addEventListener('keydown', escListener);
};

(function () {
    var REQUIRED = {
        top: ['chest'],
        outerwear: ['chest'],
        bottom: ['waist', 'hips'],
        dress: ['chest', 'waist', 'hips']
    };
    var OPTIONAL = { bottom: ['inseam'] };
    var MAX_BYTES = 2 * 1024 * 1024;
    var TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    document.querySelectorAll('form.js-confirm').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-message') || 'Are you sure?')) {
                event.preventDefault();
            }
        });
    });

    var form = document.getElementById('product-form');
    if (!form) {
        return;
    }

    var typeSelect = document.getElementById('garment_type');
    var fields = document.getElementById('measure-fields');
    var errorBox = document.getElementById('form-errors');
    var imageInput = document.getElementById('image');
    var preview = document.getElementById('image-preview');

    function syncMeasurements() {
        var type = typeSelect.value;
        var shown = (REQUIRED[type] || []).concat(OPTIONAL[type] || []);
        fields.hidden = !type;
        fields.querySelectorAll('[data-dim]').forEach(function (box) {
            box.hidden = shown.indexOf(box.getAttribute('data-dim')) === -1;
        });
    }

    function imageProblem() {
        var file = imageInput.files && imageInput.files[0];
        if (!file) {
            return null;
        }
        if (TYPES.indexOf(file.type) === -1) {
            return 'The photo must be a JPG, PNG or WEBP image.';
        }
        if (file.size > MAX_BYTES) {
            return 'The photo is too large. The limit is 2 MB.';
        }
        return null;
    }

    typeSelect.addEventListener('change', syncMeasurements);
    syncMeasurements();

    imageInput.addEventListener('change', function () {
        var problem = imageProblem();
        var file = imageInput.files && imageInput.files[0];
        if (problem || !file) {
            preview.hidden = true;
            if (problem) {
                errorBox.textContent = problem;
                errorBox.hidden = false;
                imageInput.value = '';
            }
            return;
        }
        errorBox.hidden = true;
        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
    });

    form.addEventListener('submit', function (event) {
        var errors = [];
        var title = form.elements['title'].value.trim();
        var description = form.elements['description'].value.trim();
        var price = form.elements['price'].value.trim();

        if (title.length < 3 || title.length > 150) {
            errors.push('Title must be between 3 and 150 characters.');
        }
        if (!form.elements['category_id'].value) {
            errors.push('Choose a category.');
        }
        if (!/^\d{1,6}(\.\d{1,2})?$/.test(price) || parseFloat(price) <= 0) {
            errors.push('Enter a valid price above 0.');
        }
        if (!form.elements['size'].value) {
            errors.push('Choose a size.');
        }
        if (!form.elements['item_condition'].value) {
            errors.push('Choose a condition.');
        }
        if (description.length < 10 || description.length > 2000) {
            errors.push('Description must be between 10 and 2000 characters.');
        }

        var hasExisting = !!document.getElementById('current-image');
        if (!hasExisting && !(imageInput.files && imageInput.files[0])) {
            errors.push('Please upload a photo of the item.');
        }
        var problem = imageProblem();
        if (problem) {
            errors.push(problem);
        }

        var type = typeSelect.value;
        if (type) {
            (REQUIRED[type] || []).forEach(function (dim) {
                var value = form.elements[dim].value.trim();
                if (!/^\d{1,3}(\.\d)?$/.test(value) || parseFloat(value) < 30) {
                    errors.push('Enter a valid ' + dim + ' measurement in cm.');
                }
            });
        }

        if (errors.length) {
            event.preventDefault();
            errorBox.innerHTML = '';
            var list = document.createElement('ul');
            errors.forEach(function (message) {
                var item = document.createElement('li');
                item.textContent = message;
                list.appendChild(item);
            });
            errorBox.appendChild(list);
            errorBox.hidden = false;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
}());
