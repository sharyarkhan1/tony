<footer class="site-footer" id="footer">
    <div class="container">
        <div class="footer-main">
            <div class="footer-brand-column" data-reveal>
                <a class="brand footer-brand" href="#home" aria-label="Tony Handy Work Services home">
                    <span class="brand-mark"><img src="<?= h(asset_url('assets/images/logo.svg')) ?>" alt="Tony Handy Work Services" width="180" height="173" loading="lazy"></span>
                </a>
                <p class="footer-brand-copy">A friendly local handyman for everyday home jobs and small moves across North London.</p>
            </div>

            <nav class="footer-column footer-links" aria-label="Footer navigation" data-reveal style="--reveal-delay: 70ms">
                <h2>Quick links</h2>
                <a href="#home">Home <span class="fa-icon fa-icon-arrow-right" aria-hidden="true"></span></a>
                <a href="#services">Services <span class="fa-icon fa-icon-arrow-right" aria-hidden="true"></span></a>
                <a href="#process">How it works <span class="fa-icon fa-icon-arrow-right" aria-hidden="true"></span></a>
                <a href="#moves">Small moves <span class="fa-icon fa-icon-arrow-right" aria-hidden="true"></span></a>
                <a href="#about">Why Tony <span class="fa-icon fa-icon-arrow-right" aria-hidden="true"></span></a>
                <a href="#faq">FAQs <span class="fa-icon fa-icon-arrow-right" aria-hidden="true"></span></a>
            </nav>

            <div class="footer-column footer-contact-column" data-reveal style="--reveal-delay: 130ms">
                <h2>Get in touch</h2>
                <a class="footer-contact-row" href="tel:<?= h(CONTACT_TEL) ?>">
                    <span class="footer-contact-icon" aria-hidden="true"><span class="fa-icon fa-icon-phone"></span></span>
                    <span class="footer-contact-copy"><strong><?= h(CONTACT_PHONE) ?></strong><small>Call or text Tony</small></span>
                </a>
                <a class="footer-contact-row" href="mailto:<?= h(CONTACT_EMAIL) ?>">
                    <span class="footer-contact-icon" aria-hidden="true"><span class="fa-icon fa-icon-envelope"></span></span>
                    <span class="footer-contact-copy"><strong class="footer-email"><?= h(CONTACT_EMAIL) ?></strong><small>Email Tony</small></span>
                </a>
                <div class="footer-contact-row">
                    <span class="footer-contact-icon" aria-hidden="true"><span class="fa-icon fa-icon-location-dot"></span></span>
                    <span class="footer-contact-copy"><strong>North London</strong><small>N15 &amp; surrounding areas</small></span>
                </div>
            </div>

            <div class="footer-column footer-newsletter" data-reveal style="--reveal-delay: 190ms">
                <h2>Newsletter</h2>
                <p>Get occasional service updates from Tony Handy Work Services.</p>
                <form class="footer-newsletter-form" action="#footer" method="post" data-newsletter-form novalidate>
                    <input type="hidden" name="newsletter_form" value="1">
                    <label class="visually-hidden" for="footer-newsletter-email">Email address</label>
                    <input id="footer-newsletter-email" name="email" type="email" autocomplete="email" maxlength="160" value="<?= h($newsletterEmail) ?>" placeholder="Email address" required>
                    <button class="button button-primary" type="submit">Join the newsletter <span class="fa-icon fa-icon-envelope" aria-hidden="true"></span></button>
                    <p class="footer-newsletter-status<?= $newsletterStatusType !== '' ? ' is-' . h($newsletterStatusType) : '' ?>" role="status" aria-live="polite"><?= h($newsletterStatus) ?></p>
                </form>
            </div>

        </div>

        <div class="footer-bottom">
            <p class="footer-copyright">© <?= date('Y') ?> <?= h(SITE_NAME) ?>. All rights reserved.</p>
            <p class="footer-credit">Designed &amp; Developed by <a href="https://www.creativewebsitesolution.co.uk/" target="_blank" rel="noopener">CreativeWebsiteSolution</a></p>
        </div>
    </div>
</footer>

<script src="<?= h(asset_url('assets/vendor/jquery-3.7.1.min.js')) ?>" defer></script>
<script src="<?= h(asset_url('assets/vendor/owl.carousel-2.3.4.min.js')) ?>" defer></script>
<script src="<?= h(asset_url('assets/js/main.js')) ?>?v=<?= (int) filemtime(__DIR__ . '/../assets/js/main.js') ?>" defer></script>
