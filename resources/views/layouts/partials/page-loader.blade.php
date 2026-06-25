@once
<style>
    .global-page-loader {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.42);
        backdrop-filter: blur(2px);
        transition: opacity 0.28s ease, visibility 0.28s ease;
    }

    .global-page-loader--hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .global-page-loader__card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        margin: 0 1rem;
        max-width: 18rem;
        border-radius: 1rem;
        background: #fff;
        padding: 1.75rem 2rem;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18), 0 0 0 1px rgb(226 232 240);
    }

    .global-page-loader__spinner {
        width: 3rem;
        height: 3rem;
        border-radius: 9999px;
        border: 4px solid rgb(204 251 241);
        border-top-color: rgb(13 148 136);
        animation: global-page-loader-spin 0.75s linear infinite;
    }

    .global-page-loader__message {
        margin: 0;
        text-align: center;
        font-size: 0.875rem;
        font-weight: 600;
        color: rgb(51 65 85);
    }

    html.page-loader-active,
    html.page-loader-active body {
        overflow: hidden;
    }

    @keyframes global-page-loader-spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
@endonce

<div id="globalPageLoader"
     class="global-page-loader"
     role="status"
     aria-live="polite"
     aria-busy="true"
     aria-label="Loading page">
    <div class="global-page-loader__card">
        <div class="global-page-loader__spinner" aria-hidden="true"></div>
        <p class="global-page-loader__message">Loading…</p>
    </div>
</div>
