<style>
/* ── Welcome page responsive layout ───────────────────────────────── */
.sq-welcome-wrap {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: #FDFDFC;
    padding: 1rem;
}
html.dark .sq-welcome-wrap { background: #0a0a0a; }

.sq-welcome-card {
    display: flex;
    flex-direction: column;       /* mobile: stack */
    width: 100%;
    max-width: 1100px;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 4px 40px rgba(0,0,0,0.10);
}

/* ── Image panel ──────────────────────────────────────────────────── */
.sq-welcome-image {
    position: relative;
    width: 100%;
    height: 220px;                /* compact on mobile */
    background: #f0f0f0;
    overflow: hidden;
    flex-shrink: 0;
}
html.dark .sq-welcome-image { background: #18181b; }

.sq-welcome-image img {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    max-width: 88%;
    max-height: 88%;
    width: auto;
    height: auto;
    object-fit: contain;
}

/* ── Text panel ───────────────────────────────────────────────────── */
.sq-welcome-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 2rem 1.5rem 2.5rem;
    background: #fff;
}
html.dark .sq-welcome-content {
    background: #161615;
    color: #EDEDEC;
}

.sq-welcome-title {
    margin: 0 0 0.65rem;
    font-size: 1.65rem;
    font-weight: 700;
    color: #1b1b18;
    line-height: 1.2;
}
html.dark .sq-welcome-title { color: #EDEDEC; }

.sq-welcome-desc {
    margin: 0;
    font-size: 0.925rem;
    line-height: 1.65;
    color: #706f6c;
}
html.dark .sq-welcome-desc { color: #A1A09A; }

.sq-welcome-actions {
    margin-top: 1.75rem;
    display: flex;
    flex-direction: column;       /* mobile: full-width stacked buttons */
    gap: 0.6rem;
}

.sq-welcome-btn {
    display: block;
    width: 100%;
    text-align: center;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.15s, border-color 0.15s;
    box-sizing: border-box;
}
.sq-welcome-btn-primary {
    background: #1b1b18;
    color: #fff;
    border: 1px solid #1b1b18;
}
.sq-welcome-btn-primary:hover { background: #333; border-color: #333; }
html.dark .sq-welcome-btn-primary { background: #EDEDEC; color: #1b1b18; border-color: #EDEDEC; }
html.dark .sq-welcome-btn-primary:hover { background: #d4d4d0; }

.sq-welcome-btn-secondary {
    background: transparent;
    color: #1b1b18;
    border: 1px solid rgba(25,20,0,0.22);
}
.sq-welcome-btn-secondary:hover { border-color: rgba(25,20,0,0.45); }
html.dark .sq-welcome-btn-secondary { color: #EDEDEC; border-color: #3E3E3A; }
html.dark .sq-welcome-btn-secondary:hover { border-color: #62605b; }

/* ── Desktop: side-by-side ─────────────────────────────────────────── */
@media (min-width: 780px) {
    .sq-welcome-wrap { padding: 2rem; }

    .sq-welcome-card { flex-direction: row; min-height: 520px; }

    /* Image goes on the RIGHT on desktop */
    .sq-welcome-image {
        order: 2;
        flex: 1;
        height: auto;             /* fill card height */
    }

    .sq-welcome-content {
        order: 1;
        flex: 1;
        padding: 3.5rem;
    }

    .sq-welcome-title { font-size: 2.1rem; }

    .sq-welcome-actions {
        flex-direction: row;      /* inline buttons on desktop */
        flex-wrap: wrap;
    }

    .sq-welcome-btn {
        width: auto;
        display: inline-block;
    }
}
</style>

<div class="sq-welcome-wrap">
    <main class="sq-welcome-card">

        <?php /* Image panel */ ?>
        <div class="sq-welcome-image">
            <img
                src="<?= esc(base_url('login-design.png')) ?>"
                alt="SideQuest preview"
            >
        </div>

        <?php /* Text panel */ ?>
        <div class="sq-welcome-content">
            <h1 class="sq-welcome-title">Let's get started</h1>
            <p class="sq-welcome-desc">
                SideQuest is a place to connect, share, and discover what matters to you.
                Join the conversation and explore what your community is talking about.
            </p>

            <div class="sq-welcome-actions">
                <a href="<?= esc(site_url('login')) ?>" class="sq-welcome-btn sq-welcome-btn-primary">
                    Log in
                </a>
                <?php if (! empty($canRegister)): ?>
                    <a href="<?= esc(site_url('register')) ?>" class="sq-welcome-btn sq-welcome-btn-secondary">
                        Create account
                    </a>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>
