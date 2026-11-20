(function () {
    'use strict';

    var box = document.getElementById('cat-of-the-day');
    if (box) {
        fetch(box.dataset.endpoint, { headers: { Accept: 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                var photo = box.querySelector('.cat-of-day-photo');
                var caption = box.querySelector('.cat-of-day-caption');
                if (data.ok) {
                    photo.innerHTML = '';
                    var img = new Image();
                    img.alt = 'Cat of the day';
                    img.src = data.url;
                    photo.appendChild(img);
                    caption.textContent = data.breed ? 'Today: a ' + data.breed : 'A new cat every day, via TheCatAPI';
                } else {
                    caption.textContent = 'Today\'s cat is taking a nap (' + (data.message || 'photo unavailable') + ').';
                }
            })
            .catch(function () {
                box.querySelector('.cat-of-day-caption').textContent = 'Today\'s cat is taking a nap.';
            });
    }

    var queue = Array.prototype.slice.call(document.querySelectorAll('[data-photo-endpoint]'));
    var active = 0;
    var MAX_PARALLEL = 2;

    function next() {
        while (active < MAX_PARALLEL && queue.length) {
            load(queue.shift());
        }
    }

    function load(el) {
        active++;
        fetch(el.dataset.photoEndpoint, { headers: { Accept: 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.ok) {
                    var img = new Image();
                    img.className = el.className.replace('pet-photo-placeholder', '').trim();
                    img.alt = el.dataset.alt || 'Cat';
                    img.onload = function () { el.replaceWith(img); };
                    img.src = data.url;
                } else {
                    queue.length = 0;
                }
            })
            .catch(function () { queue.length = 0; })
            .then(function () { active--; next(); });
    }
    next();

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
})();
