/* JavaScript پنل مدیریت قالب فرتاک */
(function($) {
    'use strict';
    $(document).ready(function() {
        // Color picker
        if ($.fn.wpColorPicker) {
            $('.fartak-color-field').wpColorPicker();
        }
        // Tab key support in code editors
        $('textarea').on('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                var start = this.selectionStart;
                var end = this.selectionEnd;
                this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                this.selectionStart = this.selectionEnd = start + 4;
            }
        });

        initProductPickers();
    });

    /* انتخابگر ترتیبی محصولات صفحه اصلی */
    function initProductPickers() {
        document.querySelectorAll('.ft-pick').forEach(function(zone) {
            var csv    = zone.querySelector('.ft-pk-csv');
            var list   = zone.querySelector('.ft-pick-list');
            var hint   = zone.querySelector('.ft-pk-hint');
            var search = zone.querySelector('.ft-pk-search');
            var box    = zone.querySelector('.ft-pick-box');
            var max    = parseInt(zone.getAttribute('data-max'), 10) || 20;
            var items  = Array.prototype.slice.call(zone.querySelectorAll('.ft-pk-item'));

            function ids() {
                return (csv.value || '').split(',').map(function(s) { return s.trim(); }).filter(Boolean);
            }
            function setIds(arr) { csv.value = arr.join(','); }
            function esc(s) {
                return String(s == null ? '' : s).replace(/[&<>"]/g, function(c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
                });
            }
            function infoFor(id) {
                for (var i = 0; i < items.length; i++) {
                    if (items[i].getAttribute('data-id') === String(id)) {
                        return {
                            id: id,
                            name: items[i].getAttribute('data-name'),
                            price: items[i].getAttribute('data-price') || '',
                            img: items[i].getAttribute('data-img') || ''
                        };
                    }
                }
                return { id: id, name: 'محصول #' + id, price: '', img: '' };
            }
            function chips() { return Array.prototype.slice.call(list.querySelectorAll('.ft-pk-chip')); }
            function renumber() {
                chips().forEach(function(c, x) {
                    var idx = c.querySelector('.ft-pk-idx');
                    if (idx) idx.textContent = (x + 1);
                });
            }
            function sync() {
                var current = ids();
                items.forEach(function(b) {
                    b.classList.toggle('picked', current.indexOf(b.getAttribute('data-id')) > -1);
                });
                if (hint) hint.style.display = current.length ? 'none' : '';
            }
            function render() {
                list.innerHTML = '';
                ids().forEach(function(id, i) {
                    var inf = infoFor(id);
                    var li = document.createElement('div');
                    li.className = 'ft-pk-chip';
                    li.setAttribute('data-id', inf.id);
                    li.innerHTML =
                        '<span class="ft-pk-idx">' + (i + 1) + '</span>' +
                        (inf.img ? '<img class="ft-pk-thumb" src="' + esc(inf.img) + '" alt="">' : '') +
                        '<span class="ft-pk-txt"><span class="ft-pk-name">' + esc(inf.name) + '</span>' +
                        (inf.price ? '<span class="ft-pk-meta">' + esc(inf.price) + '</span>' : '') + '</span>' +
                        '<span class="ft-pk-acts">' +
                        '<button type="button" class="button button-small" data-up title="بالا">▲</button>' +
                        '<button type="button" class="button button-small" data-down title="پایین">▼</button>' +
                        '<button type="button" class="button button-small ft-pk-rm" title="حذف">✕</button>' +
                        '</span>';
                    list.appendChild(li);
                });
                sync();
            }

            box.addEventListener('click', function(e) {
                var item = e.target.closest('.ft-pk-item');
                if (!item) return;
                var id = item.getAttribute('data-id');
                var arr = ids();
                var at = arr.indexOf(id);
                if (at > -1) {
                    arr.splice(at, 1);
                } else {
                    if (arr.length >= max) {
                        window.alert('حداکثر ' + max + ' محصول برای هر بخش قابل انتخاب است.');
                        return;
                    }
                    arr.push(id);
                }
                setIds(arr);
                render();
            });

            list.addEventListener('click', function(e) {
                var chip = e.target.closest('.ft-pk-chip');
                if (!chip) return;
                var arr = chips();
                var i = arr.indexOf(chip);
                if (e.target.closest('.ft-pk-rm')) {
                    chip.remove();
                } else if (e.target.closest('[data-up]') && i > 0) {
                    list.insertBefore(chip, arr[i - 1]);
                } else if (e.target.closest('[data-down]') && i < arr.length - 1) {
                    list.insertBefore(arr[i + 1], chip);
                } else {
                    return;
                }
                setIds(chips().map(function(c) { return c.getAttribute('data-id'); }));
                renumber();
                sync();
            });

            search.addEventListener('input', function() {
                var q = this.value.trim().toLowerCase();
                items.forEach(function(b) {
                    var hay = (b.getAttribute('data-name') + ' ' + b.getAttribute('data-id')).toLowerCase();
                    b.style.display = (!q || hay.indexOf(q) > -1) ? '' : 'none';
                });
            });

            render();
        });
    }
})(jQuery);
