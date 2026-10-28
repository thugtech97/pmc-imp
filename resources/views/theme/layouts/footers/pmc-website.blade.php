<style>
    :root {
        --pmc-footer-ink: #111a18;
        --pmc-footer-ink-deep: #0b1211;
        --pmc-footer-surface: rgba(255, 255, 255, .055);
        --pmc-footer-line: rgba(236, 212, 154, .18);
        --pmc-footer-gold: #ecd49a;
        --pmc-footer-muted: rgba(255, 255, 255, .68);
    }

    #footer-pmc {
        color: #fff;
        font-family: "DM Sans", sans-serif;
        position: relative;
        z-index: 30;
    }
    #footer-copyrights {
        background-color: var(--pmc-footer-ink) !important;
        border-top: 1px solid var(--pmc-footer-line);
        margin-top: 0;
        padding: 10px 0;
        position: relative;
        z-index: 31;
    }
    #footer-copyrights .copy-text {
        color: rgba(255, 255, 255, .48);
        font-size: 12px;
        line-height: 1.7;
    }
    #footer-copyrights .copy-text a {
        color: rgba(255, 255, 255, .62) !important;
        transition: color .2s ease;
    }
    #footer-copyrights .copy-text a:hover {
        color: var(--pmc-footer-gold) !important;
    }
    @media (max-width: 767.98px) {
        #footer-pmc #footer-copyrights > .container {
            max-width: none;
            padding-left: 12px;
            padding-right: 12px;
            width: 100%;
        }
    }
</style>

<footer id="footer-pmc">
    {{-- Copyrights bar --}}
    <div id="footer-copyrights">
        <div class="container">
            <div class="text-center">
                <span class="copy-text">
                    Copyrights &copy; 2020 All Rights Reserved by PMC &nbsp;&mdash;&nbsp;
                    <a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/terms-and-conditions">Terms of Use</a>
                    &nbsp;/&nbsp;
                    <a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/data-privacy-policy">Privacy Policy</a>
                </span>
            </div>
        </div>
    </div>
</footer>
