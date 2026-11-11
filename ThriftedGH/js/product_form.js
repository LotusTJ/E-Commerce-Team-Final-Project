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
