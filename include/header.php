<div class="pre-nav">
    <div class="container pre-nav-inner">
        <p><span class="pre-nav-dot" aria-hidden="true"></span>North London · N15</p>
        <a href="tel:<?= h(CONTACT_TEL) ?>" class="pre-nav-call">
            <span>Call / text</span><strong><?= h(CONTACT_PHONE) ?></strong>
            <span class="fa-icon fa-icon-arrow-right" aria-hidden="true"></span>
        </a>
    </div>
</div>

<header class="site-header">
    <div class="container nav-shell">
        <a class="brand" href="<?= h(asset_url('')) ?>" aria-label="Tony Handy Work Services, home">
            <span class="brand-mark"><img src="<?= h(asset_url('assets/images/logo.svg')) ?>" alt="" width="74" height="71"></span>
        </a>

        <nav class="desktop-nav" aria-label="Main navigation">
            <a href="#home">Home</a>
            <a href="#services">Services</a>
            <a href="#process">How it works</a>
            <a href="#moves">Small moves</a>
            <a href="#about">Why Tony</a>
            <a href="#faq">FAQs</a>
        </nav>

        <a class="nav-cta" href="#contact">Get in touch <span class="fa-icon fa-icon-arrow-right" aria-hidden="true"></span></a>
        <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="mobile-nav">
            <span class="fa-icon fa-icon-bars menu-bars-icon" aria-hidden="true"></span>
            <span class="fa-icon fa-icon-xmark menu-close-icon" aria-hidden="true"></span>
        </button>
    </div>
</header>

<div class="drawer-backdrop" data-drawer-close aria-hidden="true"></div>
<aside class="mobile-drawer" id="mobile-nav" role="dialog" aria-modal="true" aria-label="Mobile navigation" aria-hidden="true" inert>
    <div class="drawer-topline">
        <a class="brand" href="<?= h(asset_url('')) ?>" aria-label="Tony Handy Work Services, home">
            <span class="brand-mark"><img src="<?= h(asset_url('assets/images/logo.svg')) ?>" alt="" width="74" height="71"></span>
        </a>
        <button class="drawer-close" type="button" aria-label="Close navigation"><span class="fa-icon fa-icon-xmark" aria-hidden="true"></span></button>
    </div>
    <nav class="drawer-links" aria-label="Mobile navigation">
        <a href="#home"><span>01</span>Home <b class="fa-icon fa-icon-arrow-right" aria-hidden="true"></b></a>
        <a href="#services"><span>02</span>Services <b class="fa-icon fa-icon-arrow-right" aria-hidden="true"></b></a>
        <a href="#process"><span>03</span>How it works <b class="fa-icon fa-icon-arrow-right" aria-hidden="true"></b></a>
        <a href="#moves"><span>04</span>Small moves <b class="fa-icon fa-icon-arrow-right" aria-hidden="true"></b></a>
        <a href="#about"><span>05</span>Why Tony <b class="fa-icon fa-icon-arrow-right" aria-hidden="true"></b></a>
        <a href="#faq"><span>06</span>FAQs <b class="fa-icon fa-icon-arrow-right" aria-hidden="true"></b></a>
        <a href="#contact"><span>07</span>Get in touch <b class="fa-icon fa-icon-arrow-right" aria-hidden="true"></b></a>
    </nav>
    <div class="drawer-contact">
        <span>North London · N15 & nearby</span>
        <a href="tel:<?= h(CONTACT_TEL) ?>"><?= h(CONTACT_PHONE) ?></a>
        <a href="mailto:<?= h(CONTACT_EMAIL) ?>"><?= h(CONTACT_EMAIL) ?></a>
    </div>
</aside>
