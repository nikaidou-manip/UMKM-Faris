// ============================================================
// ANOHOBBY - Main JS
// ============================================================
(function () {
    'use strict';

    // ---------- HERO CAROUSEL ----------
    const track = document.querySelector('.hero-track');
    if (track) {
        const prevBtn = document.querySelector('.hero-arrow.prev');
        const nextBtn = document.querySelector('.hero-arrow.next');
        const dotsContainer = document.querySelector('.hero-dots');

        const slides = Array.from(track.children);
        // Build dots
        if (dotsContainer) {
            slides.forEach((_, i) => {
                const dot = document.createElement('span');
                if (i === 0) dot.classList.add('active');
                dot.addEventListener('click', () => {
                    const slide = slides[i];
                    track.scrollTo({ left: slide.offsetLeft - track.offsetLeft, behavior: 'smooth' });
                });
                dotsContainer.appendChild(dot);
            });
        }

        const getSlideWidth = () => {
            const slide = slides[0];
            if (!slide) return 300;
            const style = getComputedStyle(track);
            const gap = parseFloat(style.gap) || 16;
            return slide.getBoundingClientRect().width + gap;
        };

        if (prevBtn) prevBtn.addEventListener('click', () => {
            track.scrollBy({ left: -getSlideWidth(), behavior: 'smooth' });
        });
        if (nextBtn) nextBtn.addEventListener('click', () => {
            track.scrollBy({ left: getSlideWidth(), behavior: 'smooth' });
        });

        // Auto-advance
        let autoTimer = setInterval(() => {
            if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 10) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                track.scrollBy({ left: getSlideWidth(), behavior: 'smooth' });
            }
        }, 5000);

        track.addEventListener('mouseenter', () => clearInterval(autoTimer));

        // Update active dot on scroll
        let scrollTimer;
        track.addEventListener('scroll', () => {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(() => {
                if (!dotsContainer) return;
                const dots = dotsContainer.querySelectorAll('span');
                const slide = slides[0];
                if (!slide) return;
                const style = getComputedStyle(track);
                const gap = parseFloat(style.gap) || 16;
                const slideWidth = slide.getBoundingClientRect().width + gap;
                const idx = Math.round(track.scrollLeft / slideWidth);
                dots.forEach((d, i) => d.classList.toggle('active', i === idx));
            }, 80);
        });
    }

    // ---------- CATEGORY SLIDER (homepage) ----------
    // 2 cols x 4 rows = 8 cards per page. Arrow + dot navigation.
    const catSlider = document.getElementById('catSlider');
    if (catSlider) {
        const track   = catSlider.querySelector('.cat-slider-track');
        const pages   = catSlider.querySelectorAll('.cat-slider-page');
        const dots    = catSlider.querySelectorAll('.cat-slider-dots .dot');
        const prevBtn = catSlider.querySelector('.cat-slider-nav.prev');
        const nextBtn = catSlider.querySelector('.cat-slider-nav.next');
        const total   = pages.length;
        let current = 0;

        const update = (idx) => {
            if (idx < 0) idx = 0;
            if (idx > total - 1) idx = total - 1;
            current = idx;
            track.style.transform = `translateX(-${idx * 100}%)`;
            dots.forEach((d, i) => d.classList.toggle('active', i === idx));
            if (prevBtn) prevBtn.disabled = (idx === 0);
            if (nextBtn) nextBtn.disabled = (idx === total - 1);
        };

        if (prevBtn) prevBtn.addEventListener('click', () => update(current - 1));
        if (nextBtn) nextBtn.addEventListener('click', () => update(current + 1));
        dots.forEach((d, i) => d.addEventListener('click', () => update(i)));

        // Keyboard arrow navigation when slider is in view
        let sliderInView = false;
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver(entries => {
                sliderInView = entries[0].isIntersecting;
            }, { threshold: 0.4 });
            io.observe(catSlider);
        }
        document.addEventListener('keydown', e => {
            if (!sliderInView) return;
            if (document.activeElement && ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) return;
            if (e.key === 'ArrowLeft' && current > 0) { e.preventDefault(); update(current - 1); }
            if (e.key === 'ArrowRight' && current < total - 1) { e.preventDefault(); update(current + 1); }
        });

        // Touch swipe support (mobile)
        let startX = null, deltaX = 0;
        track.addEventListener('touchstart', e => { startX = e.touches[0].clientX; deltaX = 0; }, { passive: true });
        track.addEventListener('touchmove',  e => { if (startX !== null) deltaX = e.touches[0].clientX - startX; }, { passive: true });
        track.addEventListener('touchend',   () => {
            if (Math.abs(deltaX) > 50) {
                if (deltaX < 0) update(current + 1);
                else            update(current - 1);
            }
            startX = null; deltaX = 0;
        });

        // Init
        update(0);
    }

    // ---------- QUANTITY INPUTS ----------
    document.querySelectorAll('[data-qty-group]').forEach(group => {
        const input = group.querySelector('input');
        const dec = group.querySelector('[data-qty-dec]');
        const inc = group.querySelector('[data-qty-inc]');
        if (dec) dec.addEventListener('click', () => {
            const v = Math.max(1, (parseInt(input.value) || 1) - 1);
            input.value = v;
            input.dispatchEvent(new Event('change'));
        });
        if (inc) inc.addEventListener('click', () => {
            const max = parseInt(input.max) || 99;
            const v = Math.min(max, (parseInt(input.value) || 1) + 1);
            input.value = v;
            input.dispatchEvent(new Event('change'));
        });
    });

    // ---------- CART QTY AUTO-SUBMIT ----------
    document.querySelectorAll('form[data-auto-submit] input').forEach(input => {
        input.addEventListener('change', () => input.form.submit());
    });

    // ---------- CONFIRM DELETE ----------
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            const msg = el.getAttribute('data-confirm') || 'Yakin ingin melanjutkan?';
            if (!confirm(msg)) e.preventDefault();
        });
    });

    // ---------- PRINT INVOICE ----------
    const printBtn = document.querySelector('[data-print]');
    if (printBtn) {
        printBtn.addEventListener('click', e => {
            e.preventDefault();
            window.print();
        });
    }

    // ---------- FLASH AUTO-HIDE ----------
    setTimeout(() => {
        document.querySelectorAll('.flash').forEach(f => {
            f.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            f.style.opacity = '0';
            f.style.transform = 'translateY(-6px)';
            setTimeout(() => f.remove(), 400);
        });
    }, 4500);

    // ---------- CART BADGE LIVE UPDATE (page reload cache) ----------
    // Tidak ada yang perlu dilakukan di sini, server sudah render badge.

    // ---------- KEYBOARD SHORTCUT: Ctrl+K untuk search ----------
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const search = document.querySelector('.searchbar input');
            if (search) search.focus();
        }
    });

    // ---------- HOVER DEMO ----------
    // Tidak diperlukan JS untuk hover - semua sudah via CSS.

    console.log('%cANOHOBBY', 'color:#FF5722;font-size:28px;font-weight:900;');
    console.log('Selamat datang! Toko hobby online Anda siap pakai.');
})();
