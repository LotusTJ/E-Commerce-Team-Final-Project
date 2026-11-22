(function () {
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        var input = document.querySelector(button.getAttribute('data-toggle-password'));
        if (!input) return;
        button.addEventListener('click', function () {
            var reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            button.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
            button.classList.toggle('is-visible', reveal);
        });
    });

    var form = document.getElementById('registerForm');
    if (!form) return;

    var rules = {
        full_name: function (v) { return v.trim().length >= 2 ? '' : 'Enter your full name.'; },
        email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? '' : 'Enter a valid email address.'; },
        phone: function (v) { return v === '' || /^\+?[0-9][0-9 ()-]{6,18}$/.test(v) ? '' : 'Enter a valid phone number or leave it blank.'; },
        password: function (v) { return v.length >= 8 && /[A-Za-z]/.test(v) && /[0-9]/.test(v) ? '' : 'Use at least 8 characters with a letter and a number.'; },
        confirm_password: function (v) { return v === form.elements.password.value ? '' : 'The two passwords do not match.'; }
    };

    function show(input, message) {
        var group = input.closest('.form-group');
        var note = group.querySelector('.field-error');
        if (!note) {
            note = document.createElement('p');
            note.className = 'field-error';
            note.setAttribute('role', 'alert');
            group.appendChild(note);
        }
        note.textContent = message;
        input.classList.toggle('is-invalid', message !== '');
        input.setAttribute('aria-invalid', message !== '' ? 'true' : 'false');
        return message === '';
    }

    function check(name) {
        var input = form.elements[name];
        return show(input, rules[name](input.value));
    }

    Object.keys(rules).forEach(function (name) {
        var input = form.elements[name];
        input.addEventListener('blur', function () { check(name); });
        input.addEventListener('input', function () {
            if (input.classList.contains('is-invalid')) check(name);
            if (name === 'password' && form.elements.confirm_password.value !== '') check('confirm_password');
        });
    });

    form.addEventListener('submit', function (event) {
        var valid = true;
        Object.keys(rules).forEach(function (name) { if (!check(name)) valid = false; });
        if (!valid) {
            event.preventDefault();
            var first = form.querySelector('.is-invalid');
            if (first) first.focus();
        }
    });
})();
