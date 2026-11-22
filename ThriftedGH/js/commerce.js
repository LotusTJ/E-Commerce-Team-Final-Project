(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  function store(action, key, value) {
    try {
      if (action === 'set') window.localStorage.setItem(key, value);
      else return window.localStorage.getItem(key);
    } catch (e) {
      return null;
    }
    return null;
  }

  function pad(n) {
    return n < 10 ? '0' + n : String(n);
  }

  function setupCheckout() {
    var form = document.getElementById('checkoutForm');
    if (!form) return;
    var area = document.getElementById('delivery_address');
    var bar = document.getElementById('addrBar');
    var hint = document.getElementById('addrHint');
    var useLast = document.getElementById('useLastAddress');
    var pay = document.getElementById('payBtn');
    var last = store('get', 'tgh_last_address');

    function update() {
      var len = area.value.trim().length;
      var pct = Math.min(100, Math.round((len / 10) * 100));
      bar.style.width = pct + '%';
      bar.className = len >= 10 ? 'is-ok' : '';
      hint.textContent = len >= 10 ? 'Looks good. ' + area.value.length + ' / 2000' : 'At least 10 characters (' + len + '/10)';
    }

    if (last && useLast) {
      useLast.hidden = false;
      useLast.addEventListener('click', function () {
        area.value = last;
        update();
        area.focus();
      });
    }

    area.addEventListener('input', update);
    update();

    form.addEventListener('submit', function (event) {
      if (area.value.trim().length < 10) {
        event.preventDefault();
        area.focus();
        hint.textContent = 'Please enter a delivery address of at least 10 characters.';
        return;
      }
      store('set', 'tgh_last_address', area.value.trim());
      if (pay.classList.contains('is-loading')) {
        event.preventDefault();
        return;
      }
      pay.classList.add('is-loading');
      pay.textContent = 'Taking you to Paystack...';
    });

    window.addEventListener('pageshow', function (event) {
      if (event.persisted && pay) {
        pay.classList.remove('is-loading');
        pay.textContent = pay.getAttribute('data-label');
      }
    });
  }

  function setupCopy() {
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = document.querySelector(btn.getAttribute('data-copy'));
        if (!target) return;
        var text = target.textContent.trim();
        var done = function () {
          var old = btn.textContent;
          btn.textContent = 'Copied';
          btn.classList.add('is-active');
          setTimeout(function () {
            btn.textContent = old;
            btn.classList.remove('is-active');
          }, 1600);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(done, done);
        } else {
          var range = document.createRange();
          range.selectNodeContents(target);
          var sel = window.getSelection();
          sel.removeAllRanges();
          sel.addRange(range);
          try { document.execCommand('copy'); } catch (e) {}
          sel.removeAllRanges();
          done();
        }
      });
    });
  }

  function setupCountdown() {
    var nodes = document.querySelectorAll('[data-expires]');
    if (!nodes.length) return;
    function tick() {
      var now = Date.now();
      nodes.forEach(function (node) {
        var raw = node.getAttribute('data-expires');
        var end = Date.parse(raw.replace(' ', 'T') + 'Z');
        if (isNaN(end)) return;
        var left = Math.max(0, Math.floor((end - now) / 1000));
        if (left === 0) {
          node.textContent = 'but the hold has now expired. Refresh to see the latest status';
          node.classList.add('is-expired');
        } else {
          node.textContent = 'for ' + pad(Math.floor(left / 60)) + ':' + pad(left % 60) + ' more';
        }
      });
    }
    tick();
    setInterval(tick, 1000);
  }

  function setupFilters() {
    var chips = document.querySelectorAll('[data-filter]');
    if (!chips.length) return;
    var cards = document.querySelectorAll('.tg-order');
    var empty = document.getElementById('filterEmpty');
    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        var want = chip.getAttribute('data-filter');
        var shown = 0;
        chips.forEach(function (c) { c.classList.toggle('is-active', c === chip); });
        cards.forEach(function (card) {
          var ok = want === 'all' || card.getAttribute('data-status') === want;
          card.hidden = !ok;
          if (ok) shown++;
        });
        if (empty) empty.hidden = shown !== 0;
      });
    });
  }

  function setupButtons() {
    document.querySelectorAll('[data-print]').forEach(function (btn) {
      btn.addEventListener('click', function () { window.print(); });
    });

    document.querySelectorAll('button[data-confirm]').forEach(function (btn) {
      var armed = false;
      var timer = null;
      var label = btn.textContent;
      btn.addEventListener('click', function (event) {
        if (armed) return;
        event.preventDefault();
        armed = true;
        btn.textContent = btn.getAttribute('data-confirm');
        btn.classList.add('is-danger');
        timer = setTimeout(function () {
          armed = false;
          btn.textContent = label;
          btn.classList.remove('is-danger');
        }, 4000);
      });
      btn.form.addEventListener('submit', function () { clearTimeout(timer); });
    });

    document.querySelectorAll('button[data-busy]').forEach(function (btn) {
      if (!btn.form) return;
      btn.form.addEventListener('submit', function () {
        setTimeout(function () {
          btn.disabled = true;
          btn.classList.add('is-loading');
          btn.textContent = btn.getAttribute('data-busy');
        }, 0);
      });
    });
  }

  ready(function () {
    setupCheckout();
    setupCopy();
    setupCountdown();
    setupFilters();
    setupButtons();
  });
})();
