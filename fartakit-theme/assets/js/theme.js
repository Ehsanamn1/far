/**
 * Fartak Theme — همه رفتارهای تعاملی قالب
 */
(function () {
        "use strict";

        var F = window.FARTAK || {};
        var $ = window.jQuery;

        /* ------------------------------------------------- ابزارهای پایه */
        function fa(n) {
                try {
                        return Number(n || 0).toLocaleString("fa-IR");
                } catch (e) {
                        return String(n);
                }
        }
        function el(sel, root) { return (root || document).querySelector(sel); }
        function els(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
        function debounce(fn, wait) {
                var t;
                return function () {
                        var args = arguments, ctx = this;
                        clearTimeout(t);
                        t = setTimeout(function () { fn.apply(ctx, args); }, wait);
                };
        }
        var ICONS = {
                check: '<svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>',
                x: '<svg class="icon" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>',
                loader: '<svg class="icon spin" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-6.22-8.56"/></svg>',
                cart: '<svg class="icon" viewBox="0 0 24 24"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>',
                phone: '<svg class="icon" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
                clip: '<svg class="icon" viewBox="0 0 24 24"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>',
                truck: '<svg class="icon" viewBox="0 0 24 24"><path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11M14 9h4l4 4v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/><path d="M9 18h6"/></svg>',
                search: '<svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>',
                wrench: '<svg class="icon" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
                package: '<svg class="icon" viewBox="0 0 24 24"><path d="m16 16 2 2 4-4"/><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 .96.25"/><path d="M3.3 7l8.7 5 8.7-5M12 22V12"/></svg>',
                alert: '<svg class="icon" viewBox="0 0 24 24"><path d="M21.73 18l-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4M12 17h.01"/></svg>',
                copy: '<svg class="icon" viewBox="0 0 24 24"><rect x="8" y="8" width="14" height="14" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>',
                party: '<svg class="icon" viewBox="0 0 24 24"><path d="M5.8 11.3 2 22l10.7-3.79M4 3h.01M22 8h.01M15 2h.01M22 20h.01"/><path d="M22 2l-2.24.75a2.9 2.9 0 0 0-1.96 3.12c.07.86-.11 1.74-.56 2.55l-.27.56a2.11 2.11 0 0 1-1.7 1.03"/></svg>'
        };
        function icon(name) { return ICONS[name] || ICONS.check; }
        function esc(s) {
                return String(s == null ? "" : s).replace(/[&<>"]/g, function (c) {
                        return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c];
                });
        }
        function post(action, data) {
                data = data || {};
                data.action = action;
                data.nonce = F.nonce;
                var body = new URLSearchParams();
                Object.keys(data).forEach(function (k) { body.append(k, data[k]); });
                return fetch(F.ajax, { method: "POST", credentials: "same-origin", body: body }).then(function (r) { return r.json(); });
        }


        /* drawer accordion: expand/collapse subcategories in mobile drawer */
        document.addEventListener("click", function (e) {
                var arr = e.target.closest("[data-d-toggle]");
                if (arr) {
                        var group = arr.closest(".d-group");
                        if (group) {
                                var open = group.classList.toggle("open");
                                arr.setAttribute("aria-expanded", open ? "true" : "false");
                        }
                }
        });
        /* desktop: hover subcategory dropdown - JS fallback for browsers without CSS :has */
        document.addEventListener("mouseover", function (e) {
                var bar = document.querySelector(".catbar");
                if (!bar || !e.target || !e.target.closest) return;
                if (e.target.closest(".cat-item, .mega")) { bar.classList.add("mega-open"); }
                else if (!e.relatedTarget || !e.relatedTarget.closest || !e.relatedTarget.closest(".cat-item, .mega, .catbar")) { bar.classList.remove("mega-open"); }
        });
        /* mobile dock: دسته‌ها bottom sheet */
        document.addEventListener("click", function (e) {
                if (e.target.closest("[data-cats-open]")) { document.body.classList.add("cats-open"); }
                if (e.target.closest("[data-cats-close]")) { document.body.classList.remove("cats-open"); }
        });

        /* mega close button: force-close panel until mouse leaves */
        document.addEventListener("click", function (e) {
                var mc = e.target.closest("[data-mega-close]");
                if (mc) {
                        var barm = document.querySelector(".catbar");
                        if (barm) {
                                barm.classList.add("mega-locked");
                                barm.classList.remove("mega-open");
                                if (document.activeElement && document.activeElement.blur) document.activeElement.blur();
                                setTimeout(function () { barm.classList.remove("mega-locked"); }, 600);
                        }
                }
        });

        /* data:fartak-gallery-rescue */
        /* gallery rescue: اگر JS ووکامرس گالری را مخفی نگه داشت، بعد از لود صفحه نمایان کن */
        window.addEventListener("load", function () {
                document.querySelectorAll(".woocommerce-product-gallery").forEach(function (g) { g.style.opacity = "1"; });
        });
        /* image error fallback: preserve the <img> element and use the real WooCommerce placeholder */
        document.addEventListener("error", function (e) {
                var img = e.target;
                if (!img || img.tagName !== "IMG" || img.dataset.fartakFallbackApplied === "1") return;
                var placeholder = (window.FARTAK && FARTAK.placeholder) ? FARTAK.placeholder : "";
                if (!placeholder) return;
                img.dataset.fartakFallbackApplied = "1";
                img.removeAttribute("srcset");
                img.removeAttribute("sizes");
                img.src = placeholder;
        }, true);
        /* ------------------------------------------------- توست (خطا با آیکون متفاوت + سقف ۴ عدد) */
        function toast(msg, isError) {
                var zone = el("#fartak-toasts");
                if (!zone) return;
                while (zone.children.length >= 4) zone.removeChild(zone.firstChild);
                var t = document.createElement("div");
                t.className = "toast" + (isError ? " toast-err" : "");
                t.innerHTML = icon(isError ? "alert" : "check") + "<span>" + esc(msg) + "</span><button type='button' class='tx'>" + icon("x") + "</button>";
                zone.appendChild(t);
                setTimeout(function () { t.remove(); }, 2600);
                t.querySelector(".tx").addEventListener("click", function () { t.remove(); });
        }

        /* ------------------------------------------------- هدر چسبان */
        var header = el("#site-header");
        function onScroll() { header && header.classList.toggle("is-scrolled", window.scrollY > 40); }
        window.addEventListener("scroll", onScroll, { passive: true });
        onScroll();

        /* ------------------------------------------------- کشوی موبایل + قفل اسکرول قوی (سازگار با iOS) */
        var ftScrollY = 0;
        function lockScroll() {
                if (document.body.classList.contains("ft-locked")) return;
                ftScrollY = window.scrollY || window.pageYOffset || 0;
                document.body.classList.add("ft-locked");
                document.body.style.top = -ftScrollY + "px";
        }
        function unlockScroll() {
                if (!document.body.classList.contains("ft-locked")) return;
                document.body.classList.remove("ft-locked");
                document.body.style.top = "";
                window.scrollTo(0, ftScrollY);
        }
        document.addEventListener("click", function (e) {
                if (e.target.closest("[data-drawer-open]")) { document.body.classList.add("drawer-open"); lockScroll(); }
                if (e.target.closest("[data-drawer-close]")) { document.body.classList.remove("drawer-open"); unlockScroll(); }
        });

        /* ------------------------------------------------- مگامنو: fallback برای مرورگرهای بدون :has */
        var catbar = el(".catbar");
        if (catbar) {
                els(".mega", catbar).forEach(function (mega) {
                        mega.addEventListener("mouseenter", function () { catbar.classList.add("mega-open"); });
                        mega.addEventListener("mouseleave", function () { catbar.classList.remove("mega-open"); });
                });
        }

        /* ------------------------------------------------- پارالاکس 3D هیرو (data-hero-parallax) */
        var heroVisual = el("[data-hero-visual]");
        var heroParallax = el("[data-hero-parallax]");
        if (heroVisual && heroParallax && window.matchMedia("(hover:hover)").matches) {
                heroVisual.addEventListener("mousemove", function (e) {
                        var r = heroVisual.getBoundingClientRect();
                        var rx = ((e.clientY - r.top) / r.height - 0.5) * -8;
                        var ry = ((e.clientX - r.left) / r.width - 0.5) * 10;
                        heroParallax.style.transform = "rotateX(" + rx.toFixed(2) + "deg) rotateY(" + ry.toFixed(2) + "deg)";
                });
                heroVisual.addEventListener("mouseleave", function () { heroParallax.style.transform = "none"; });
        }

        /* ------------------------------------------------- ریل دسته‌بندی: اسکرول افقی با چرخ ماوس */
        els(".cat-rail").forEach(function (rail) {
                rail.addEventListener("wheel", function (e) {
                        if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return;
                        var max = rail.scrollWidth - rail.clientWidth;
                        if (max <= 0) return;
                        e.preventDefault();
                        rail.scrollLeft += e.deltaY;
                }, { passive: false });
        });

        /* ------------------------------------------------- جستجوی زنده */
        var searchInput = el("#fartak-live-search");
        var searchBox = el("#fartak-search-results");
        var searchToken = 0;
        if (searchInput && searchBox) {
                var doSearch = debounce(function () {
                        var q = searchInput.value.trim();
                        if (q.length < 2) { searchBox.classList.remove("open"); return; }
                        el("[data-search-icon]").classList.add("ft-hidden");
                        el("[data-search-spinner]").classList.remove("ft-hidden");
                        // توکن ضد race: پاسخ قدیمیِ کند نباید نتیجه عبارت جدید را دور بزند
                        var myToken = ++searchToken;
                        fetch(F.ajax + "?action=fartak_smart_search&nonce=" + encodeURIComponent(F.nonce) + "&q=" + encodeURIComponent(q), { credentials: "same-origin" })
                                .then(function (r) { return r.json(); })
                                .then(function (list) {
                                        if (myToken !== searchToken) return;
                                        el("[data-search-icon]").classList.remove("ft-hidden");
                                        el("[data-search-spinner]").classList.add("ft-hidden");
                                        var html = "";
                                        if (!list.length) {
                                                html = '<div class="sr-empty">موردی یافت نشد؛ عبارت دیگری را امتحان کنید.</div>';
                                        } else {
                                                list = Array.isArray(list) ? list : [];
                                                html = '<div class="sr-list">' + list.map(function (p) {
                                                        return '<a class="sr-item" href="' + esc(p.url) + '"><img src="' + esc(p.img) + '" alt=""><span class="t"><b>' + esc(p.name) + "</b><small>" + esc(p.cat) + '</small></span><span class="p">' + (p.raw > 0 ? p.price : "تماس بگیرید") + "</span></a>";
                                                }).join("") + "</div>";
                                        }
                                        html += '<a class="sr-all" href="' + F.homeUrl + "?s=" + encodeURIComponent(q) + '&post_type=product">مشاهده همه نتایج <span>←</span></a>';
                                        searchBox.innerHTML = html;
                                        searchBox.classList.add("open");
                                })
                                .catch(function () {
                                        el("[data-search-icon]").classList.remove("ft-hidden");
                                        el("[data-search-spinner]").classList.add("ft-hidden");
                                });
                }, 240);
                searchInput.addEventListener("input", doSearch);
                searchInput.addEventListener("keydown", function (e) {
                        if (e.key === "Enter") {
                                window.location.assign(F.homeUrl + "?s=" + encodeURIComponent(searchInput.value.trim()) + "&post_type=product");
                        }
                });
                document.addEventListener("mousedown", function (e) {
                        if (!e.target.closest(".search-wrap")) searchBox.classList.remove("open");
                });
        }

        /* ------------------------------------------------- مرتب‌سازی فروشگاه: سلکت خودکار submit شود
           (به‌جای اتکا به JS هسته ووکامرس که ممکن است لود نشده باشد) */
        document.addEventListener("change", function (e) {
                var sel = e.target.closest(".woocommerce-ordering select");
                if (sel && sel.form) sel.form.submit();
        });

        /* ------------------------------------------------- کاروسل‌های Swiper */
        if (window.Swiper) {
                els("[data-carousel]").forEach(function (zone) {
                        new Swiper(zone.querySelector(".ft-swiper"), {
                                slidesPerView: "auto",
                                spaceBetween: 12,
                                freeMode: { enabled: true, momentumRatio: 0.7 },
                                navigation: {
                                        prevEl: zone.querySelector("[data-prev]"),
                                        nextEl: zone.querySelector("[data-next]")
                                }
                        });
                });
        }

        /* ------------------------------------------------- هیرو اسلایدر */
        var hero = el("#fartak-hero");
        if (hero) {
                var hSlides = els("[data-hero-slide]", hero);
                var hImgs = els("[data-hero-img]", hero);
                var hDots = els("[data-hero-goto]", hero);
                var hIndex = 0, hTimer = null;
                if (hSlides.length > 1) {
                        function hGo(i) {
                                hIndex = (i + hSlides.length) % hSlides.length;
                                hSlides.forEach(function (s, x) { s.classList.toggle("active", x === hIndex); });
                                hImgs.forEach(function (s, x) { s.classList.toggle("active", x === hIndex); });
                                hDots.forEach(function (s, x) { s.classList.toggle("active", x === hIndex); });
                                clearInterval(hTimer);
                                hTimer = setInterval(function () { hGo(hIndex + 1); }, 6500);
                        }
                        hTimer = setInterval(function () { hGo((hIndex + 1) % hSlides.length); }, 6500);
                        hDots.forEach(function (d) {
                                d.addEventListener("click", function () { hGo(parseInt(d.getAttribute("data-hero-goto"), 10)); });
                        });
                        var pv = hero.querySelector("[data-hero-prev]"), nx = hero.querySelector("[data-hero-next]");
                        pv && pv.addEventListener("click", function () { hGo(hIndex - 1); });
                        nx && nx.addEventListener("click", function () { hGo(hIndex + 1); });
                        // در تب غیرفعال توقف — برگشت به تب بدون پرش اسلاید
                        document.addEventListener("visibilitychange", function () {
                                clearInterval(hTimer);
                                if (!document.hidden) {
                                        hTimer = setInterval(function () { hGo((hIndex + 1) % hSlides.length); }, 6500);
                                }
                        });
                }
        }

        /* ------------------------------------------------- استوری‌ها */
        var storiesData = el("#fartak-stories-data");
        var viewer = el("#story-viewer");
        if (storiesData && viewer) {
                var sList = [];
                try { sList = JSON.parse(storiesData.textContent); } catch (e) { sList = []; }
                var sIdx = 0, sTimer = null, sDur = 5000, sPaused = false, sStart = 0, sRemain = sDur;
                var frame = el("#story-frame");
                var progress = el("#story-progress");
                progress.innerHTML = sList.map(function () { return '<span class="story-seg"><i></i></span>'; }).join("");
                var segs = els(".story-seg", progress);
                function sShow(i) {
                        sIdx = i;
                        var s = sList[sIdx];
                        var storyImg = el("#story-img");
                        storyImg.src = s.img;
                        storyImg.onerror = function () { this.onerror = null; if (F.placeholder) this.src = F.placeholder; };
                        var bgEl = el("#story-bg");
                        if (bgEl) { bgEl.src = s.img; bgEl.onerror = function () { this.onerror = null; if (F.placeholder) this.src = F.placeholder; }; }
                        el("#story-name").textContent = s.name;
                        el("#story-price").innerHTML = s.raw > 0 ? s.price : "تماس بگیرید";
                        var oldEl = el("#story-old");
                        if (s.old) { oldEl.innerHTML = s.old; oldEl.style.display = "block"; } else { oldEl.style.display = "none"; }
                        el("#story-off").innerHTML = s.off > 0 ? '<span class="badge-sale" style="position:static">٪' + fa(s.off) + " تخفیف</span>" : "";
                        el("#story-count").textContent = fa(sIdx + 1) + " از " + fa(sList.length); var sw=el("#story-warranty"); if(sw) sw.textContent=s.warranty||"";
                        el("#story-link").href = s.url;
                        segs.forEach(function (seg, x) {
                                seg.className = "story-seg";
                                var bar = seg.querySelector("i");
                                bar.style.removeProperty("--dur");
                                if (x < sIdx) seg.classList.add("done");
                                if (x === sIdx) {
                                        void bar.offsetWidth; // ری‌استارت انیمیشن
                                        bar.style.setProperty("--dur", sDur + "ms");
                                        seg.classList.add("playing");
                                }
                        });
                        clearTimeout(sTimer);
                        sStart = Date.now();
                        sRemain = sDur;
                        sTimer = setTimeout(function () {
                                if (sIdx >= sList.length - 1) sClose();
                                else sShow(sIdx + 1);
                        }, sDur);
                }
                function sOpen(i) {
                        viewer.classList.add("open");
                        lockScroll();
                        sShow(i);
                }
                function sClose() {
                        viewer.classList.remove("open");
                        unlockScroll();
                        clearTimeout(sTimer);
                }
                document.addEventListener("click", function (e) {
                        var opener = e.target.closest("[data-story-open]");
                        if (opener) sOpen(parseInt(opener.getAttribute("data-story-open"), 10));
                        if (e.target.closest("[data-story-close]")) sClose();
                        if (e.target.closest("[data-story-next]")) { clearTimeout(sTimer); sIdx >= sList.length - 1 ? sClose() : sShow(sIdx + 1); }
                        if (e.target.closest("[data-story-prev]")) { clearTimeout(sTimer); sShow(Math.max(0, sIdx - 1)); }
                });
                frame.addEventListener("pointerdown", function () {
                        if (sPaused) return;
                        sPaused = true;
                        sRemain = Math.max(400, sDur - (Date.now() - sStart)); // باقی‌مانده واقعی — قبلاً resume از صفر شروع می‌شد
                        viewer.classList.add("paused");
                        clearTimeout(sTimer);
                });
                ["pointerup", "pointerleave"].forEach(function (evName) {
                        frame.addEventListener(evName, function () {
                                if (!sPaused) return;
                                sPaused = false;
                                viewer.classList.remove("paused");
                                clearTimeout(sTimer);
                                sTimer = setTimeout(function () {
                                        if (sIdx >= sList.length - 1) sClose(); else sShow(sIdx + 1);
                                }, sRemain);
                        });
                });
                document.addEventListener("keydown", function (e) {
                        if (!viewer.classList.contains("open")) return;
                        if (e.key === "Escape") sClose();
                        if (e.key === "ArrowLeft") { clearTimeout(sTimer); sShow(Math.max(0, sIdx - 1)); }
                        if (e.key === "ArrowRight") { clearTimeout(sTimer); sIdx >= sList.length - 1 ? sClose() : sShow(sIdx + 1); }
                });
        }

        /* ------------------------------------------------- ذخیره‌سازی محلی */
        function store(key, val) {
                try { window.localStorage.setItem(key, JSON.stringify(val)); } catch (e) {}
        }
        function read(key, fb) {
                try {
                        var v = window.localStorage.getItem(key);
                        return v ? JSON.parse(v) : fb;
                } catch (e) { return fb; }
        }

        /* ------------------------------------------------- تعداد محصول + افزودن به سبد */
        document.addEventListener("click", function(e){
                var inc=e.target.closest("[data-qty-inc]"), dec=e.target.closest("[data-qty-dec]");
                if(!inc && !dec) return;
                var wrap=e.target.closest("[data-qty-control]"), input=wrap?wrap.querySelector("[data-qty-input]"):null;
                if(!input) return; e.preventDefault();
                var value=parseInt(input.value||"1",10)||1; var min=Math.max(1,parseInt(input.min||"1",10)); var max=parseInt(input.max||"0",10);
                value += inc ? 1 : -1; if(max>0) value=Math.min(max,value); input.value=Math.max(min,value);
        });
        document.addEventListener("change", function(e){ var input=e.target.closest("[data-qty-input]"); if(input){ var v=parseInt(input.value||"1",10)||1; input.value=Math.max(1,v); }});
        document.addEventListener("click", function(e){
                var btn=e.target.closest("[data-fartak-add]"); if(!btn) return; e.preventDefault();
                var row=btn.closest(".ft-buy-row"), input=row?row.querySelector("[data-qty-input]"):null;
                var qty=input?Math.max(1,parseInt(input.value||"1",10)||1):1;
                btn.disabled=true;
                addToCart(parseInt(btn.getAttribute("data-fartak-add"),10),qty).finally(function(){btn.disabled=false;});
        });

        /* ------------------------------------------------- افزودن به سبد AJAX */
        function setCartCount(n) {
                els("[data-cart-count]").forEach(function (b) {
                        b.textContent = fa(n);
                        b.classList.toggle("ft-hidden", !(n > 0));
                });
        }
        function addToCart(id, qty) {
                return post("fartak_add_to_cart", { product_id: id, qty: qty || 1 }).then(function (res) {
                        if (res && res.success) {
                                setCartCount(res.data.count);
                                toast(F.i18n && F.i18n.added ? F.i18n.added : "به سبد خرید اضافه شد");
                        } else {
                                toast(res && res.data && res.data.message ? res.data.message : "خطا", true);
                        }
                        return res;
                });
        }

        /* ------------------------------------------------- مقایسه */
        function compareIds() { return read("fartak-compare", []); }
        function paintCompare() {
                var ids = compareIds();
                els("[data-compare-toggle]").forEach(function (b) {
                        b.classList.toggle("active", ids.indexOf(parseInt(b.getAttribute("data-compare-toggle"), 10)) > -1);
                });
                var badge = el("[data-compare-count]");
                if (badge) {
                        badge.textContent = fa(ids.length);
                        badge.classList.toggle("ft-hidden", ids.length === 0);
                }
        }
        function renderComparePage() {
                var zone = el("#compare-zone");
                if (!zone) return;
                var ids = compareIds();
                var empty = el("#compare-empty");
                var tableZone = el("#compare-table");
                if (!ids.length) {
                        empty.classList.remove("ft-hidden");
                        tableZone.classList.add("ft-hidden");
                        return;
                }
                fetch(F.ajax + "?action=fartak_compare&nonce=" + encodeURIComponent(F.nonce) + "&ids=" + ids.join(","), { credentials: "same-origin" })
                        .then(function (r) { return r.json(); })
                        .then(function (res) {
                                if (res && res.success) {
                                        tableZone.innerHTML = res.data.html;
                                        empty.classList.add("ft-hidden");
                                        tableZone.classList.remove("ft-hidden");
                                } else {
                                        store("fartak-compare", []);
                                        empty.classList.remove("ft-hidden");
                                        tableZone.classList.add("ft-hidden");
                                }
                        });
        }
        var zoneInit = el("#compare-zone") ? true : false;

        if ("serviceWorker" in navigator) { /* در صورت نیاز آفلاین بعداً فعال می‌شود */ }

        /* ------------------------------------------------- هندلر سراسری کلیک */
        document.addEventListener("click", function (e) {
                var cmp = e.target.closest("[data-compare-toggle]");
                if (cmp) {
                        var id = parseInt(cmp.getAttribute("data-compare-toggle"), 10);
                        var ids = compareIds();
                        if (ids.indexOf(id) > -1) {
                                ids = ids.filter(function (x) { return x !== id; });
                                toast(F.i18n && F.i18n.compareOff ? F.i18n.compareOff : "از مقایسه حذف شد");
                        } else {
                                if (ids.length >= 3) {
                                        toast(F.i18n && F.i18n.compareMax ? F.i18n.compareMax : "حداکثر ۳ کالا");
                                        return;
                                }
                                ids.push(id);
                                toast(F.i18n && F.i18n.compareOn ? F.i18n.compareOn : "به مقایسه اضافه شد");
                        }
                        store("fartak-compare", ids);
                        paintCompare();
                        if (zoneInit) renderComparePage();
                }
        });

        paintCompare();
        renderComparePage();

        /* ------------------------------------------------- مودال استعلام هوش مصنوعی */
        var inqModal = el("#inquiry-modal");
        if (inqModal) {
                var inqName = "";
                function inqPhase(phase) {
                        ["scan", "form", "done"].forEach(function (p) {
                                el("#inq-phase-" + p).classList.toggle("ft-hidden", p !== phase);
                        });
                }
                function inqOpen(name, hint) {
                        inqName = name || "کالا";
                        el("#inq-product-name").textContent = inqName;
                        el("#inq-name").value = "";
                        el("#inq-phone").value = "";
                        inqModal.classList.add("open");
                        lockScroll();
                        inqPhase("scan");
                        setTimeout(function () {
                                el("#inq-estimate").textContent = "استعلام قیمت روز";
                                inqPhase("form");
                        }, 1600);
                }
                document.addEventListener("click", function (e) {
                        var trigger = e.target.closest("[data-fartak-inquire]");
                        if (trigger) {
                                inqOpen(trigger.getAttribute("data-inq-name"), parseFloat(trigger.getAttribute("data-inq-hint") || "0"));
                        }
                        if (e.target.closest("[data-inquiry-close]") || e.target === inqModal) {
                                inqModal.classList.remove("open");
                                unlockScroll();
                        }
                });
                el("#inq-submit").addEventListener("click", function () {
                        var name = el("#inq-name").value.trim();
                        var phone = el("#inq-phone").value.trim();
                        if (!name || phone.length < 10) {
                                toast(F.i18n && F.i18n.formErr ? F.i18n.formErr : "فرم ناقص است");
                                return;
                        }
                        this.disabled = true;
                        var btn = this;
                        var orig = btn.innerHTML;
                        btn.innerHTML = icon("loader");
                        post("fartak_inquiry", {
                                name: name,
                                phone: phone,
                                product_name: inqName,
                                note: "برآورد هوش مصنوعی: " + el("#inq-estimate").textContent
                        }).then(function (res) {
                                btn.disabled = false;
                                btn.innerHTML = orig;
                                if (res && res.success) inqPhase("done");
                                else toast(F.i18n && F.i18n.sendErr ? F.i18n.sendErr : "خطا");
                        }).catch(function () {
                                btn.disabled = false;
                                btn.innerHTML = orig;
                                toast(F.i18n && F.i18n.sendErr ? F.i18n.sendErr : "خطا");
                        });
                });
        }

        /* ------------------------------------------------- چت منشی */
        var chatPanel = el("#chat-panel");
        var chatFab = el("[data-chat-toggle]");
        if (chatPanel && chatFab) {
                var faq = [];
                try { faq = JSON.parse(el("#fartak-chat-faq").textContent); } catch (e) { faq = []; }
                var chatPhone = "";
                try { chatPhone = JSON.parse(el("#fartak-chat-phone").textContent); } catch (e) { chatPhone = ""; }
                var quick = el("#chat-quick");
                quick.innerHTML = faq.map(function (q) { return '<button type="button" class="chip">' + esc(q[0]) + "</button>"; }).join("");
                function chatMsg(text, who, html) {
                        var body = el("#chat-body");
                        var div = document.createElement("div");
                        div.className = "chat-msg " + who;
                        if (html) { div.innerHTML = text; } else { div.textContent = text; }
                        body.appendChild(div);
                        body.scrollTop = body.scrollHeight;
                }
                function chatAnswer(q) {
                        var ql = (q || "").trim();
                        for (var i = 0; i < faq.length; i++) {
                                if (faq[i][0] === ql || ql.indexOf(faq[i][0].replace("؟","").replace("?","")) !== -1) return faq[i][1];
                        }
                        for (var j = 0; j < faq.length; j++) {
                                if (faq[j][0].indexOf(ql) !== -1 && ql.length > 2) return faq[j][1];
                        }
                        return chatPhone
                                ? "پاسخ دقیق این سوال را کارشناسان ما می‌دانند. لطفاً با پشتیبانی فرتاک تماس بگیرید: <a href=\"tel:" + esc(chatPhone) + "\" dir=\"ltr\" style=\"color:var(--copper2);font-weight:900\">" + esc(chatPhone) + "</a>"
                                : "برای پاسخ دقیق با پشتیبانی فرتاک تماس بگیرید.";
                }
                function chatSend(text) {
                        text = (text || "").trim();
                        if (!text) return;
                        chatMsg(text, "user");
                        setTimeout(function () { chatMsg(chatAnswer(text), "bot", true); }, 700);
                }
                chatFab.addEventListener("click", function () {
                        var open = chatPanel.classList.toggle("open");
                        el("[data-chat-open-ic]").classList.toggle("ft-hidden", open);
                        el("[data-chat-close-ic]").classList.toggle("ft-hidden", !open);
                });
                var chatCloseBtn = el("[data-chat-close]");
                if (chatCloseBtn) {
                        chatCloseBtn.addEventListener("click", function () {
                                chatPanel.classList.remove("open");
                                el("[data-chat-open-ic]").classList.remove("ft-hidden");
                                el("[data-chat-close-ic]").classList.add("ft-hidden");
                        });
                }
                quick.addEventListener("click", function (e) {
                        var btn = e.target.closest(".chip");
                        if (btn) chatSend(btn.textContent.trim());
                });
                el("[data-chat-send]").addEventListener("click", function () {
                        chatSend(el("#chat-text").value);
                        el("#chat-text").value = "";
                });
                el("#chat-text").addEventListener("keydown", function (e) {
                        if (e.key === "Enter") {
                                chatSend(this.value);
                                this.value = "";
                        }
                });
        }

        /* ------------------------------------------------- گردونه شانس */
        var wheelModal = el("#wheel-modal");
        var wheelFab = el("[data-wheel-open]");
        if (wheelModal && wheelFab) {
                var segs2 = [];
                try { segs2 = JSON.parse(el("#fartak-wheel-data").textContent); } catch (e) { segs2 = []; }
                var canvas = el("#wheel-canvas");
                var ctx = canvas.getContext("2d");
                var spinning = false;
                var rotation = 0;
                function drawWheel() {
                        var size = 280, r = size / 2;
                        ctx.setTransform(2, 0, 0, 2, 0, 0);
                        ctx.clearRect(0, 0, size, size);
                        segs2.forEach(function (seg, i) {
                                var start = (i * 45 - 90) * Math.PI / 180;
                                var end = ((i + 1) * 45 - 90) * Math.PI / 180;
                                ctx.beginPath();
                                ctx.moveTo(r, r);
                                ctx.arc(r, r, r - 4, start, end);
                                ctx.closePath();
                                ctx.fillStyle = seg.code === "FRT20" ? "#7a4a22" : (i % 2 === 0 ? "#14203a" : "#0e1626");
                                ctx.fill();
                                ctx.strokeStyle = "rgba(232,152,94,.35)";
                                ctx.lineWidth = 1;
                                ctx.stroke();
                                ctx.save();
                                ctx.translate(r, r);
                                ctx.rotate((i * 45 + 22.5 - 90) * Math.PI / 180);
                                ctx.textAlign = "left";
                                ctx.fillStyle = seg.code ? "#f2b47e" : "#8b95ab";
                                ctx.font = "bold 11px Vazirmatn, sans-serif";
                                ctx.fillText(seg.label, r * 0.4, 4);
                                ctx.restore();
                        });
                        ctx.beginPath();
                        ctx.arc(r, r, 26, 0, Math.PI * 2);
                        ctx.fillStyle = "#0a101e";
                        ctx.fill();
                        ctx.strokeStyle = "#e8985e";
                        ctx.lineWidth = 2;
                        ctx.stroke();
                        ctx.fillStyle = "#f2b47e";
                        ctx.font = "900 12px Vazirmatn, sans-serif";
                        ctx.textAlign = "center";
                        ctx.fillText("فرتاک", r, r + 4);
                }
                function wheelOpen() {
                        wheelModal.classList.add("open");
                        lockScroll();
                        drawWheel();
                }
                function wheelClose() {
                        if (!spinning) {
                                wheelModal.classList.remove("open");
                                unlockScroll();
                        }
                }
                wheelFab.addEventListener("click", wheelOpen);
                el("[data-wheel-close]").addEventListener("click", wheelClose);
                wheelModal.addEventListener("click", function (e) { if (e.target === wheelModal) wheelClose(); });
                // Exit intent — یک بار در هر نشست
                document.addEventListener("mouseleave", function (e) {
                        if (e.clientY <= 0 && !sessionStorage.getItem("fartak-wheel-seen")) {
                                try { sessionStorage.setItem("fartak-wheel-seen", "1"); } catch (err) {}
                                wheelOpen();
                        }
                });
                el("#wheel-spin").addEventListener("click", function () {
                        if (spinning) return;
                        spinning = true;
                        el("#wheel-result").innerHTML = "";
                        this.textContent = "در حال چرخش…";
                        var idx = Math.floor(Math.random() * segs2.length);
                        rotation = Math.ceil(rotation / 360) * 360 + 360 * 6 + (360 - idx * 45 - 22.5);
                        canvas.style.transform = "rotate(" + rotation + "deg)";
                        var btn = this;
                        setTimeout(function () {
                                spinning = false;
                                btn.textContent = "بچرخون!";
                                var seg = segs2[idx];
                                if (seg.code) {
                                        el("#wheel-result").innerHTML =
                                                '<div class="wheel-prize">' +
                                                '<div style="display:flex;align-items:center;justify-content:center;gap:8px;font-size:15px;font-weight:900;color:var(--copper2)">' + icon("party") + " تبریک! " + esc(seg.label) + " برنده شدی</div>" +
                                                '<button type="button" class="coupon-code" data-copy="' + esc(seg.code) + '">' + esc(seg.code) + " " + icon("copy") + "</button>" +
                                                '<a class="btn-ghost" style="margin-inline:auto" href="' + F.cartUrl + "?fartak_coupon=" + encodeURIComponent(seg.code) + '">اعمال خودکار در سبد خرید</a>' +
                                                '<p style="margin:0;font-size:11px;color:var(--mist)">کد در صفحه سبد خرید قابل‌استفاده است.</p></div>';
                                } else {
                                        el("#wheel-result").innerHTML =
                                                '<div class="wheel-prize"><p style="margin:0;font-size:14px;font-weight:700;color:var(--mist)">این دور نشد… ولی یک شانس دیگه داری!</p>' +
                                                '<button type="button" class="btn-copper" style="margin-inline:auto" id="wheel-respin">چرخش دوباره</button></div>';
                                }
                        }, 4600);
                });
                document.addEventListener("click", function (e) {
                        if (e.target.closest("#wheel-respin")) {
                                el("#wheel-spin").click();
                        }
                        var cp = e.target.closest("[data-copy]");
                        if (cp && navigator.clipboard) {
                                navigator.clipboard.writeText(cp.getAttribute("data-copy"));
                                toast(F.i18n && F.i18n.copied ? F.i18n.copied : "کپی شد");
                        }
                });
        }

        /* اعمال خودکار کوپن در صفحه سبد */
        (function () {
                var params = new URLSearchParams(window.location.search);
                var coupon = params.get("fartak_coupon");
                if (!coupon) return;
                var input = el("#coupon_code");
                var form = input ? input.closest("form") : null;
                if (input && form) {
                        input.value = coupon;
                        setTimeout(function () {
                                var btn = form.querySelector("[name='apply_coupon']");
                                btn && btn.click();
                        }, 600);
                }
        })();

        /* ------------------------------------------------- فرم تماس با ما */
        var contactForm = el("#fartak-contact-form");
        if (contactForm) {
                contactForm.addEventListener("submit", function (e) {
                        e.preventDefault();
                        var btn = el("#fartak-contact-btn");
                        var msg = el("#fartak-contact-msg");
                        msg.classList.add("ft-hidden");
                        btn.disabled = true;
                        btn.innerHTML = icon("loader") + " در حال ارسال…";
                        var payload = {};
                        new FormData(contactForm).forEach(function (v, k) { payload[k] = v; });
                        post("fartak_inquiry", payload)
                                .then(function (res) {
                                        btn.disabled = false;
                                        btn.innerHTML = icon("check") + " ارسال پیام";
                                        msg.classList.remove("ft-hidden");
                                        if (res && res.success) {
                                                msg.className = "ct-msg ct-ok";
                                                msg.textContent = res.data && res.data.message ? res.data.message : "پیام شما ثبت شد. کارشناسان ما به‌زودی با شما تماس می‌گیرند.";
                                                contactForm.reset();
                                        } else {
                                                msg.className = "ct-msg ct-err";
                                                msg.textContent = res && res.data && res.data.message ? res.data.message : "خطا در ارسال. لطفاً دوباره تلاش کنید.";
                                        }
                                })
                                .catch(function () {
                                        btn.disabled = false;
                                        btn.innerHTML = icon("check") + " ارسال پیام";
                                        msg.className = "ct-msg ct-err";
                                                msg.textContent = "خطا در اتصال. لطفاً دوباره تلاش کنید.";
                                        msg.classList.remove("ft-hidden");
                                });
                });
        }

        /* ------------------------------------------------- فرم خرید عمده */
        var wsForm = el("#fartak-wholesale-form");
        if (wsForm) {
                wsForm.addEventListener("submit", function (e) {
                        e.preventDefault();
                        var btn = el("#fartak-wholesale-btn");
                        var msg = el("#fartak-wholesale-msg");
                        msg.classList.add("ft-hidden");
                        btn.disabled = true;
                        btn.innerHTML = icon("loader") + " در حال ارسال…";
                        var payload = {};
                        new FormData(wsForm).forEach(function (v, k) { payload[k] = v; });
                        post("fartak_wholesale_submit", payload)
                                .then(function (res) {
                                        btn.disabled = false;
                                        btn.innerHTML = icon("send") + " ثبت درخواست";
                                        msg.classList.remove("ft-hidden");
                                        if (res && res.success) {
                                                msg.className = "ct-msg ct-ok";
                                                msg.textContent = res.data && res.data.message ? res.data.message : "درخواست شما ثبت شد. به‌زودی با شما تماس می‌گیریم.";
                                                wsForm.reset();
                                        } else {
                                                msg.className = "ct-msg ct-err";
                                                msg.textContent = res && res.data && res.data.message ? res.data.message : "خطا در ارسال. لطفاً دوباره تلاش کنید.";
                                        }
                                })
                                .catch(function () {
                                        btn.disabled = false;
                                        btn.innerHTML = icon("send") + " ثبت درخواست";
                                        msg.className = "ct-msg ct-err";
                                        msg.textContent = "خطا در اتصال. لطفاً دوباره تلاش کنید.";
                                        msg.classList.remove("ft-hidden");
                                });
                });
        }

        /* ------------------------------------------------- اسنبل آنلاین */
        var builderSteps = el("#builder-steps");
        if (builderSteps) {
                var stepBtns = els(".builder-step", builderSteps);
                var panels = els(".builder-panel");
                var sel = {}; // slug → {id,name,price,socket,ram}
                var xp = 0;
                var order = stepBtns.map(function (b) { return b.getAttribute("data-step"); });

                function catData(card) {
                        return {
                                id: parseInt(card.getAttribute("data-id"), 10),
                                name: card.getAttribute("data-name"),
                                price: parseFloat(card.getAttribute("data-price")),
                                socket: card.getAttribute("data-socket") || "",
                                ram: card.getAttribute("data-ram") || ""
                        };
                }

                function incompat(card, slug) {
                        var d = catData(card);
                        var cpu = sel.cpu, board = sel.motherboard;
                        if (slug === "motherboard" && cpu && board !== undefined && cpu.socket && d.socket && cpu.socket !== d.socket) {
                                return "سوکت «" + d.socket + "» با پردازنده سازگار نیست";
                        }
                        if (slug === "ram" && board && board.ram && d.ram && board.ram !== d.ram) {
                                return "مادربرد انتخابی فقط رم «" + board.ram + "» می‌پذیرد";
                        }
                        if (slug === "cpu" && board && board.socket && d.socket && board.socket !== d.socket) {
                                return "این پردازنده روی سوکت «" + board.socket + "» نصب نمی‌شود";
                        }
                        return null;
                }

                function repaint() {
                        els(".bo-card").forEach(function (card) {
                                var slug = card.getAttribute("data-build-pick");
                                var reason = incompat(card, slug);
                                var warn = card.querySelector("[data-warn]");
                                var pick = card.querySelector(".bo-pick");
                                var chosen = sel[slug] && sel[slug].id === catData(card).id;
                                card.classList.toggle("off", !!reason);
                                card.classList.toggle("sel", !!chosen);
                                if (reason) {
                                        warn.innerHTML = icon("alert") + "<span>" + esc(reason) + "</span>";
                                        warn.classList.remove("ft-hidden");
                                        pick.disabled = true;
                                        pick.textContent = "ناسازگار";
                                } else {
                                        warn.classList.add("ft-hidden");
                                        pick.disabled = false;
                                        pick.textContent = chosen ? "انتخاب شد" : "انتخاب";
                                }
                        });
                        stepBtns.forEach(function (b) {
                                var slug = b.getAttribute("data-step");
                                var doneMk = b.querySelector(".done-mark");
                                var isDone = !!sel[slug];
                                b.classList.toggle("done", isDone);
                                doneMk && doneMk.classList.toggle("ft-hidden", !isDone);
                        });
                        var chosenCount = order.filter(function (s) { return !!sel[s]; }).length;
                        el("#builder-progress-fill").style.width = (chosenCount / order.length) * 100 + "%";
                        el("#builder-progress-note").textContent = fa(chosenCount) + " از " + fa(order.length) + " مرحله تکمیل شد";
                        var total = 0, watt = 90;
                        order.forEach(function (s) {
                                var item = els('[data-sum="' + s + '"]')[0];
                                if (sel[s]) {
                                        item.classList.remove("empty");
                                        item.querySelector("[data-sum-name]").textContent = sel[s].name;
                                        if (!item.querySelector(".rm")) {
                                                var rm = document.createElement("button");
                                                rm.type = "button";
                                                rm.className = "rm";
                                                rm.setAttribute("data-remove-step", s);
                                                rm.innerHTML = icon("x");
                                                item.appendChild(rm);
                                        }
                                        total += sel[s].price;
                                } else {
                                        item.classList.add("empty");
                                        item.querySelector("[data-sum-name]").textContent = "— انتخاب نشده";
                                        var rm2 = item.querySelector(".rm");
                                        rm2 && rm2.remove();
                                }
                        });
                        if (sel.cpu) watt += 95;
                        if (sel.gpu) watt += sel.gpu.price > 40000000 ? 420 : sel.gpu.price > 25000000 ? 280 : 160;
                        el("#builder-watt").textContent = fa(watt) + " وات";
                        el("#builder-total").textContent = fa(total);
                        el("#builder-add-all").disabled = chosenCount === 0;
                        el("#builder-hint").textContent = chosenCount === order.length ? "کارت عالی بود — سیستم کامل شد!" : "برای برآیند نهایی، همه مراحل را کامل کن.";
                }

                builderSteps.addEventListener("click", function (e) {
                        var btn = e.target.closest(".builder-step");
                        if (!btn) return;
                        var slug = btn.getAttribute("data-step");
                        stepBtns.forEach(function (b) { b.classList.toggle("on", b === btn); });
                        panels.forEach(function (p) { p.classList.toggle("ft-hidden", p.getAttribute("data-panel") !== slug); });
                });

                document.addEventListener("click", function (e) {
                        var pick = e.target.closest(".bo-card .bo-pick");
                        if (pick && !pick.disabled) {
                                var card = pick.closest(".bo-card");
                                var slug = card.getAttribute("data-build-pick");
                                sel[slug] = catData(card);
                                xp += 100;
                                el("#builder-xp").textContent = fa(xp);
                                repaint();
                                // حرکت خودکار به مرحله بعد
                                var idx = order.indexOf(slug);
                                if (idx > -1 && idx < order.length - 1) {
                                        setTimeout(function () { stepBtns[idx + 1].click(); }, 350);
                                }
                        }
                        var rmBtn = e.target.closest("[data-remove-step]");
                        if (rmBtn) {
                                delete sel[rmBtn.getAttribute("data-remove-step")];
                                repaint();
                        }
                });

                el("#builder-add-all").addEventListener("click", function () {
                        var ids = order.filter(function (s) { return !!sel[s]; }).map(function (s) { return sel[s].id; });
                        if (!ids.length) return;
                        var btn = this;
                        btn.disabled = true;
                        var origHtml = btn.innerHTML;
                        btn.innerHTML = icon("loader") + " در حال افزودن…";
                        var failed = 0;
                        var chain = Promise.resolve();
                        ids.forEach(function (id) {
                                chain = chain.then(function () {
                                        return addToCart(id, 1).then(function (res) {
                                                if (!res || !res.success) failed++;
                                        });
                                });
                        });
                        chain.then(function () {
                                if (failed === 0) {
                                        btn.innerHTML = icon("cart") + " همه در سبد است";
                                        toast("همه قطعات اسمبل به سبد اضافه شد");
                                        setTimeout(function () { window.location.assign(F.cartUrl); }, 1200);
                                } else {
                                        btn.disabled = false;
                                        btn.innerHTML = origHtml;
                                        toast(fa(failed) + " قطعه به سبد اضافه نشد — دوباره تلاش کنید", true);
                                }
                        }).catch(function () {
                                // بدون این catch دکمه برای همیشه روی لودر گیر می‌کرد
                                btn.disabled = false;
                                btn.innerHTML = origHtml;
                                toast("خطا در افزودن قطعات — اتصال را چک کنید", true);
                        });
                });

                repaint();
        }
})();

/* ========== Mobile Search Toggle ========== */
(function() {
    var toggle = document.getElementById('mobile-search-toggle');
    var search = document.querySelector('.search-wrap');
    if (!toggle || !search) return;

    var ftScrollY = 0;
    function lockScroll() {
        if (document.body.classList.contains('ft-locked')) return;
        ftScrollY = window.scrollY || window.pageYOffset || 0;
        document.body.classList.add('ft-locked');
        document.body.style.top = -ftScrollY + 'px';
    }
    function unlockScroll() {
        if (!document.body.classList.contains('ft-locked')) return;
        document.body.classList.remove('ft-locked');
        document.body.style.top = '';
        window.scrollTo(0, ftScrollY);
    }

    function closeMobileSearch() {
        search.classList.remove('mobile-open');
        unlockScroll();
        var results = document.getElementById('fartak-search-results');
        if (results) results.classList.remove('open');
        var input = search.querySelector('input');
        if (input) input.value = '';
    }

    toggle.addEventListener('click', function() {
        search.classList.toggle('mobile-open');
        if (search.classList.contains('mobile-open')) {
            lockScroll();
            var input = search.querySelector('input');
            if (input) setTimeout(function() { input.focus(); }, 100);
        } else {
            unlockScroll();
        }
    });

    // بستن با دکمه ضربدر
    var closeBtn = search.querySelector('[data-search-close]');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeMobileSearch();
        });
    }

    // بستن با لمس پس‌زمینه (زیر باکس جستجو)
    search.addEventListener('click', function(e) {
        if (search.classList.contains('mobile-open') && e.target === search) {
            closeMobileSearch();
        }
    });

    // بستن با Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && search.classList.contains('mobile-open')) {
            closeMobileSearch();
        }
    });
})();

/* ========== سبد خرید: انیمیشن نرم حذف و به‌روزرسانی ========== */
(function() {
    function cartRow(trigger) {
        return trigger ? trigger.closest('tr.cart_item') : null;
    }

    // حذف آیتم: اول انیمیشن، بعد ارسال واقعی
    document.addEventListener('click', function(e) {
        var remove = e.target.closest('td.product-remove a.remove, td.product-remove .remove');
        if (!remove) return;
        var row = cartRow(remove);
        if (!row) return;
        e.preventDefault();
        row.classList.add('ft-removing');
        var href = remove.getAttribute('href');
        setTimeout(function() { window.location.assign(href); }, 430);
    }, true);

    // تغییر تعداد — delegation روی document (بعد از آپدیت AJAX هم کار می‌کند) + debounce ۵۰۰ms
    // قبلاً لیسنر فقط یک‌بار بایند می‌شد و بعد از جایگزینی HTML ووکامرس بی‌اثر می‌شد
    var qtyTimer = null;
    document.addEventListener('change', function(e) {
        var q = e.target.closest('.woocommerce-cart-form .qty');
        if (!q) return;
        var row = cartRow(q);
        if (row) row.classList.add('ft-updating');
        clearTimeout(qtyTimer);
        qtyTimer = setTimeout(function() {
            var form2 = document.querySelector('.woocommerce-cart-form');
            if (!form2) return;
            var btn = form2.querySelector('[name=update_cart]');
            if (btn) {
                btn.click();
            } else {
                form2.submit();
            }
        }, 500);
    });
    // بعد از آپدیت AJAX: پاک کردن هایلایت سطرها و فلش جمع کل
    if (window.jQuery) {
        jQuery(document.body).on('updated_wc_div updated_cart_totals wc_fragments_refreshed', function() {
            var totals = document.querySelector('.cart_totals');
            if (totals) {
                totals.classList.remove('ft-busy');
                totals.classList.add('ft-flash');
                setTimeout(function(){ totals.classList.remove('ft-flash'); }, 600);
            }
            document.querySelectorAll('tr.cart_item.ft-updating').forEach(function(r) {
                r.classList.remove('ft-updating');
            });
        });
    }
})();
