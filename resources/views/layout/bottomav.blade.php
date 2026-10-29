<style>
    /* ============ FOOTER ADMIN BREWNEST ============ */
    :root {
        /* samakan dengan tinggi bottomav.blade.php kamu */
        --ft-bottomnav-h: 64px;
        --ft-bottomnav-bp: 768px;
    }

    @keyframes ftSteam {
        0% {
            opacity: 0;
            transform: translateY(2px) scaleX(1);
        }

        30% {
            opacity: .75;
        }

        100% {
            opacity: 0;
            transform: translateY(-13px) scaleX(1.8);
        }
    }

    .ft-footer {
        position: relative;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(180deg, #fff, #fbf8f3);
    }

    /* garis tipis di atas footer */
    .ft-footer::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 2px;
        background: linear-gradient(90deg, #f3e6d6, #b9854f, #754a30, #b9854f, #f3e6d6);
    }

    .ft-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .85rem;
        max-width: 80rem;
        margin: 0 auto;
        font-size: .875rem;
        color: #78716c;
        text-align: center;
    }

    .ft-copy {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .5rem;
        margin: 0;
    }

    .ft-badge {
        display: grid;
        place-items: center;
        flex: none;
        width: 28px;
        height: 28px;
        border-radius: .6rem;
        font-size: .85rem;
        background: linear-gradient(145deg, #754a30, #3b2416);
        box-shadow: 0 6px 14px -8px rgba(117, 74, 48, .8);
        transition: transform .4s cubic-bezier(.34, 1.56, .64, 1);
    }

    .ft-copy:hover .ft-badge {
        transform: rotate(-10deg) scale(1.12);
    }

    .ft-brand {
        font-weight: 700;
        color: #754a30;
    }

    .ft-rights {
        display: block;
    }

    /* cangkir mengepul: satu-satunya animasi yang jalan terus */
    .ft-cup {
        position: relative;
        display: inline-block;
        margin: 0 .15rem;
    }

    .ft-cup i {
        position: absolute;
        bottom: 85%;
        width: 2px;
        height: 8px;
        border-radius: 9px;
        background: rgba(185, 133, 79, .8);
        filter: blur(1px);
        opacity: 0;
        animation: ftSteam 2.4s ease-in infinite;
    }

    .ft-cup i:nth-child(1) {
        left: 22%;
    }

    .ft-cup i:nth-child(2) {
        left: 48%;
        animation-delay: .8s;
    }

    .ft-cup i:nth-child(3) {
        left: 72%;
        animation-delay: 1.6s;
    }

    .ft-made {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        margin: 0;
        padding: .35rem .9rem;
        border-radius: 9999px;
        background: #fbf6f0;
        border: 1px solid #ecd8c8;
        font-size: .75rem;
        font-weight: 500;
        color: #57534e;
        white-space: nowrap;
    }

    /* tombol kembali ke atas */
    .ft-top {
        position: fixed;
        right: 1rem;
        /* di mobile: naik di atas bottom nav + safe area iPhone */
        bottom: calc(var(--ft-bottomnav-h) + 1rem + env(safe-area-inset-bottom, 0px));
        z-index: 40;
        display: grid;
        place-items: center;
        width: 44px;
        height: 44px;
        border: 0;
        border-radius: 9999px;
        color: #fff;
        font-size: 1.1rem;
        line-height: 1;
        cursor: pointer;
        background: linear-gradient(145deg, #8a5636, #5c3822);
        box-shadow: 0 12px 24px -10px rgba(117, 74, 48, .8);
        opacity: 0;
        transform: translateY(16px) scale(.8);
        pointer-events: none;
        transition: opacity .3s ease, transform .35s cubic-bezier(.34, 1.56, .64, 1), box-shadow .3s;
        -webkit-tap-highlight-color: transparent;
    }

    .ft-top.is-visible {
        opacity: 1;
        transform: none;
        pointer-events: auto;
    }

    .ft-top:active {
        transform: scale(.92);
    }

    .ft-top:focus-visible {
        outline: 3px solid #b9854f;
        outline-offset: 3px;
    }

    /* ---------- MOBILE: footer jangan tertutup bottom nav ---------- */
    @media (max-width: 767.98px) {
        .ft-footer {
            padding-bottom: calc(1.25rem + var(--ft-bottomnav-h) + env(safe-area-inset-bottom, 0px));
        }
    }

    /* ---------- TABLET / DESKTOP ---------- */
    @media (min-width: 768px) {
        .ft-inner {
            flex-direction: row;
            justify-content: space-between;
            text-align: left;
        }

        .ft-copy {
            flex-direction: row;
            gap: .65rem;
        }

        .ft-rights {
            display: inline;
        }

        .ft-top {
            right: 1.5rem;
            bottom: 1.5rem;
        }

        .ft-top:hover {
            transform: translateY(-3px) scale(1.06);
            box-shadow: 0 16px 28px -10px rgba(117, 74, 48, .9);
        }

        .ft-made {
            transition: transform .3s cubic-bezier(.34, 1.56, .64, 1), box-shadow .3s;
        }

        .ft-made:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -12px rgba(117, 74, 48, .55);
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .ft-footer *,
        .ft-top {
            animation: none !important;
            transition-duration: .01ms !important;
        }
    }
</style>

<footer class="ft-footer">
    <div class="ft-inner">

        <p class="ft-copy">
            <span class="ft-badge" aria-hidden="true">☕</span>
            <span>
                &copy; {{ date('Y') }} <span class="ft-brand">BrewNest Coffee</span>.
                <span class="ft-rights">All rights reserved.</span>
            </span>
        </p>

        <p class="ft-made">
            Made with
            <span class="ft-cup" aria-hidden="true"><i></i><i></i><i></i>☕</span>
            and passion
        </p>
    </div>
</footer>

<!-- Tombol kembali ke atas -->
<button type="button" id="ftTop" class="ft-top" aria-label="Kembali ke atas">↑</button>

<script>
    (function() {
        const btn = document.getElementById('ftTop');
        if (!btn) return;
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function toggle() {
            btn.classList.toggle('is-visible', window.scrollY > 300);
        }
        window.addEventListener('scroll', toggle, {
            passive: true
        });
        toggle();

        btn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: reduce ? 'auto' : 'smooth'
            });
        });
    })();
</script>
