<style>
    /* ============ FOOTER ADMIN BREWNEST ============ */
    @keyframes ftRise {
        from {
            opacity: 0;
            transform: translateY(14px);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    @keyframes ftFlow {
        0% {
            background-position: 0% 50%;
        }

        100% {
            background-position: 200% 50%;
        }
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

    @keyframes ftBob {

        0%,
        100% {
            transform: translateY(0) rotate(0);
        }

        50% {
            transform: translateY(-2px) rotate(-6deg);
        }
    }

    @keyframes ftShimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    .ft-footer {
        position: relative;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(180deg, #fff, #fbf8f3);
        animation: ftRise .7s cubic-bezier(.2, .8, .2, 1) both;
    }

    /* garis gradasi mengalir di atas footer */
    .ft-footer::before {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        height: 2px;
        background: linear-gradient(90deg, #f3e6d6, #b9854f, #754a30, #b9854f, #f3e6d6);
        background-size: 200% 100%;
        animation: ftFlow 6s linear infinite;
    }

    .ft-copy {
        display: flex;
        align-items: center;
        gap: .55rem;
    }

    .ft-badge {
        display: grid;
        place-items: center;
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
        background: linear-gradient(90deg, #754a30 0%, #b9854f 45%, #754a30 90%);
        background-size: 200% auto;
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        animation: ftShimmer 6s linear infinite;
    }

    /* cangkir mengepul */
    .ft-cup {
        position: relative;
        display: inline-block;
        margin: 0 .15rem;
        animation: ftBob 3.4s ease-in-out infinite;
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
        gap: .2rem;
        padding: .3rem .85rem;
        border-radius: 9999px;
        background: #fbf6f0;
        border: 1px solid #ecd8c8;
        transition: transform .3s cubic-bezier(.34, 1.56, .64, 1), box-shadow .3s;
    }

    .ft-made:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -12px rgba(117, 74, 48, .55);
    }

    /* tombol kembali ke atas */
    .ft-top {
        position: fixed;
        right: 1.5rem;
        bottom: 1.5rem;
        z-index: 40;
        display: grid;
        place-items: center;
        width: 44px;
        height: 44px;
        border: 0;
        border-radius: 9999px;
        color: #fff;
        font-size: 1.1rem;
        cursor: pointer;
        background: linear-gradient(145deg, #8a5636, #5c3822);
        box-shadow: 0 12px 24px -10px rgba(117, 74, 48, .8);
        opacity: 0;
        transform: translateY(16px) scale(.8);
        pointer-events: none;
        transition: opacity .3s ease, transform .35s cubic-bezier(.34, 1.56, .64, 1), box-shadow .3s;
    }

    .ft-top.is-visible {
        opacity: 1;
        transform: none;
        pointer-events: auto;
    }

    .ft-top:hover {
        transform: translateY(-3px) scale(1.06);
        box-shadow: 0 16px 28px -10px rgba(117, 74, 48, .9);
    }

    .ft-top:active {
        transform: scale(.92);
    }

    @media (prefers-reduced-motion: reduce) {

        .ft-footer,
        .ft-footer *,
        .ft-footer::before {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
    }
</style>

<footer class="ft-footer">
    <div class="flex flex-col items-center justify-between gap-3 text-sm text-stone-500 sm:flex-row">

        <p class="ft-copy">
            <span class="ft-badge">☕</span>
            <span>
                &copy; {{ date('Y') }} <span class="ft-brand">BrewNest Coffee.</span>
                All rights reserved.
            </span>
        </p>

        <p class="ft-made text-xs font-medium text-stone-600">
            Made with
            <span class="ft-cup"><i></i><i></i><i></i>☕</span>
            and passion.
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
