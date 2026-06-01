<style>
    #footer-pmc {
        background-color: #111111;
        background-image: url('https://www.philsaga.com/images/foot-bg.png');
        background-repeat: repeat;
        background-size: cover;
        color: #cccccc;
        padding: 56px 0 0;
    }
    #footer-pmc .footer-brand img {
        height: 80px;
        opacity: .95;
    }
    #footer-pmc .footer-tagline {
        color: #aaaaaa;
        font-size: 13px;
        margin-top: 10px;
        line-height: 1.6;
    }
    #footer-pmc .footer-tagline strong {
        color: #e0b84a;
    }
    #footer-pmc .footer-divider {
        border: none;
        border-top: 1px solid #2e2e2e;
        margin: 32px 0 28px;
    }
    #footer-pmc .footer-col-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #e0b84a;
        margin-bottom: 16px;
    }
    #footer-pmc .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    #footer-pmc .footer-links li {
        margin-bottom: 10px;
    }
    #footer-pmc .footer-links li a {
        color: #b0b0b0;
        font-size: 13px;
        text-decoration: none;
        transition: color .2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    #footer-pmc .footer-links li a::before {
        content: '›';
        color: #e0b84a;
        font-size: 16px;
        line-height: 1;
    }
    #footer-pmc .footer-links li a:hover {
        color: #ffffff;
    }
    #footer-copyrights {
        background-color: #0a0a0a;
        border-top: 1px solid #222222;
        padding: 18px 0;
        margin-top: 48px;
    }
    #footer-copyrights .copy-text {
        color: #666666;
        font-size: 12px;
    }
    #footer-copyrights .copy-links a {
        color: #888888;
        font-size: 12px;
        text-decoration: none;
        margin: 0 6px;
        transition: color .2s;
    }
    #footer-copyrights .copy-links a:hover {
        color: #e0b84a;
    }
    #footer-copyrights .copy-links a + a::before {
        content: '/';
        margin-right: 12px;
        color: #333333;
    }
</style>

<footer id="footer-pmc">
    <div class="container">

        {{-- Brand + tagline --}}
        <div class="d-flex align-items-center gap-4 footer-brand">
            <img src="{{ asset('img/pmc-logo.png') }}" alt="PMC Logo">
            <div style="border-left: 1px solid #2e2e2e; padding-left: 24px;">
                <p class="footer-tagline mb-0">
                    A <strong>Socially Responsible</strong> &amp; <strong>Environment Friendly</strong> Company
                </p>
            </div>
        </div>

        <hr class="footer-divider">

        {{-- Navigation columns --}}
        <div class="row">
            <div class="col-lg-3 col-6 mb-4">
                <div class="footer-col-title">Company</div>
                <ul class="footer-links">
                    <li><a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public">Home</a></li>
                    <li><a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/the-way-of-the-tiger">About</a></li>
                    <li><a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/careers">Career</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-6 mb-4">
                <div class="footer-col-title">Sustainability</div>
                <ul class="footer-links">
                    <li><a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/news?category=Community Social Responsibility">CSR</a></li>
                    <li><a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/news?category=Health and Safety">Health and Safety</a></li>
                    <li><a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/news?category=Community Programs">Community Relations</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-6 mb-4">
                <div class="footer-col-title">Resources</div>
                <ul class="footer-links">
                    <li><a target="_blank" href="https://vendor.philsaga.com">Supplier Portal</a></li>
                    <li><a target="_blank" href="https://www.x64.gold/projects/overview">Projects</a></li>
                    <li><a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/the-vein">The Vein</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-6 mb-4">
                <div class="footer-col-title">More</div>
                <ul class="footer-links">
                    <li><a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/careers">Career</a></li>
                    <li><a target="_blank" href="https://www.x64.gold/projects/overview">X64</a></li>
                    <li><a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/sitemap">Site Map</a></li>
                </ul>
            </div>
        </div>

    </div>

    {{-- Copyrights bar --}}
    <div id="footer-copyrights">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <span class="copy-text">
                        Copyrights &copy; 2020 All Rights Reserved by PMC &nbsp;&mdash;&nbsp;
                        <a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/terms-and-conditions" style="color:#666;text-decoration:none;">Terms of Use</a>
                        &nbsp;/&nbsp;
                        <a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/data-privacy-policy" style="color:#666;text-decoration:none;">Privacy Policy</a>
                    </span>
                </div>
                <div class="col-12 col-md-6 text-center text-md-end">
                    <div class="copy-links">
                        <a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public">Home</a>
                        <a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/the-way-of-the-tiger">About Us</a>
                        <a href="https://cms4.webfocusprod.wsiph2.com/pmc-site-new/pmc-site/public/careers">Careers</a>
                        <a href="https://www.x64.gold/projects/overview">Projects</a>
                        <a href="#">FAQs</a>
                        <a href="#">Contact</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
