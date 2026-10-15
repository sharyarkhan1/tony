<?php
require_once __DIR__ . '/include/config.php';
$services = [
    ['category' => 'Furniture · assembly', 'title' => 'Flat-pack assembly', 'summary' => 'IKEA, Argos, Wayfair & more.', 'detail' => 'Assembly for flat-pack furniture from IKEA, Argos, Wayfair and more. Tell Tony which item needs putting together and include its brand or model if you know it. Share any useful notes about the job so the assembly can be understood before arranging a visit.', 'image' => 'assets/images/flat-pack-assembly.webp', 'alt' => 'A person assembling a wooden furniture frame at home', 'image_position' => '43% 48%'],
    ['category' => 'Wall · installation', 'title' => 'TV mounting', 'summary' => 'Safe & secure installation.', 'detail' => 'Safe and secure TV mounting for your home. When you enquire, mention the TV size and where you would like it mounted. A few details about the wall and installation will help explain the job clearly.', 'image' => 'assets/images/tv-mounting.webp', 'alt' => 'A wall-mounted television in a bright modern living room', 'image_position' => '38% 42%'],
    ['category' => 'Wall · storage', 'title' => 'Shelf installation', 'summary' => 'Floating shelves & more.', 'detail' => 'Floating shelves and other shelving fitted neatly around your home. Share how many shelves you need, where you would like them installed and any details you know about the wall. That gives Tony a clearer picture of the work before you arrange it.', 'image' => 'assets/images/shelf-installation.webp', 'alt' => 'Wooden shelves filled with books and home decor', 'image_position' => '43% 50%'],
    ['category' => 'Wall · finishing touches', 'title' => 'Picture & mirror hanging', 'summary' => 'Neatly & securely hung.', 'detail' => 'Pictures and mirrors hung neatly and securely. Mention how many pieces you have, their approximate size and the rooms where they need to go. That helps Tony understand the hanging job and what will be involved before you get in touch.', 'image' => 'assets/images/picture-mirror-hanging.webp', 'alt' => 'Hands carefully aligning framed artwork on a wall', 'image_position' => '53% 48%'],
    ['category' => 'Furniture · care', 'title' => 'Furniture disassembly', 'summary' => 'Quick & careful service.', 'detail' => 'Quick, careful furniture disassembly for jobs around the home. Tell Tony which items need taking apart and whether it is connected to a move or a room change. Sharing those details helps explain the work before arranging the service.', 'image' => 'assets/images/furniture-disassembly.webp', 'alt' => 'A craftsperson working on a chair in a furniture workshop', 'image_position' => '53% 48%'],
    ['category' => 'Furniture · reset', 'title' => 'Disassembly & re-assembly', 'summary' => 'Taken apart and put back together.', 'detail' => 'Furniture taken apart and put back together again. Let Tony know which pieces need disassembly and re-assembly and where the work will take place. Include any details about the items or move so the job is clear before you get in touch.', 'image' => 'assets/images/furniture-reassembly.webp', 'alt' => 'A hand tool being used to work on a wooden furniture piece', 'image_position' => '50% 46%'],
    ['category' => 'Home · repair', 'title' => 'Minor home repairs', 'summary' => 'Fixing the small things.', 'detail' => 'Small home repairs and the little fixes that need attention. Describe what needs fixing, where it is in the home and any useful details about the problem. Tony can review what you need and discuss whether it is a suitable job.', 'image' => 'assets/images/minor-home-repair.webp', 'alt' => 'People working together to fix a light fitting at home', 'image_position' => '50% 42%'],
    ['category' => 'North London · moving', 'title' => 'One-bed & studio removals', 'summary' => 'Small moves, big care.', 'detail' => 'One-bed flat and studio removals for smaller moves, with a careful service. Share the collection and destination areas, preferred date and what needs moving. Tony is based in N15 and serves nearby North London areas, so those details help explain your move.', 'image' => 'assets/images/small-move.webp', 'alt' => 'A mover unloading furniture from the back of a moving truck', 'image_position' => '50% 48%', 'href' => '#moves'],
];
$contactFormStatus = '';
$contactFormStatusType = '';
$contactFormValues = ['name' => '', 'email' => '', 'phone' => '', 'service' => '', 'message' => ''];
$newsletterStatus = '';
$newsletterStatusType = '';
$newsletterEmail = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['contact_form'] ?? '') === '1') {
    foreach ($contactFormValues as $field => $unusedValue) {
        $submittedValue = $_POST[$field] ?? '';
        $contactFormValues[$field] = is_string($submittedValue) ? trim($submittedValue) : '';
    }

    $allowedServices = array_column($services, 'title');
    $allowedServices[] = 'Something else';
    $nameIsValid = preg_match('/^.{2,100}$/us', $contactFormValues['name']) === 1;
    $emailIsValid = filter_var($contactFormValues['email'], FILTER_VALIDATE_EMAIL) !== false
        && strlen($contactFormValues['email']) <= 160
        && !preg_match('/[\r\n]/', $contactFormValues['email']);
    $phoneIsValid = strlen($contactFormValues['phone']) <= 32;
    $serviceIsValid = in_array($contactFormValues['service'], $allowedServices, true);
    $messageIsValid = preg_match('/^.{10,2000}$/us', $contactFormValues['message']) === 1;
    $isValidSubmission = $nameIsValid && $emailIsValid && $phoneIsValid && $serviceIsValid && $messageIsValid;

    if (!$isValidSubmission) {
        $contactFormStatusType = 'error';
        $contactFormStatus = 'Some details need checking. Please correct the fields and try again.';
        http_response_code(422);
    } else {
        $subject = 'New website enquiry - ' . $contactFormValues['service'];
        $message = implode("\r\n", [
            'Name: ' . $contactFormValues['name'],
            'Email: ' . $contactFormValues['email'],
            'Phone: ' . ($contactFormValues['phone'] !== '' ? $contactFormValues['phone'] : 'Not provided'),
            'Service: ' . $contactFormValues['service'],
            '',
            'Job details:',
            $contactFormValues['message'],
        ]);
        $headers = implode("\r\n", [
            'From: ' . SITE_NAME . ' <' . CONTACT_EMAIL . '>',
            'Reply-To: ' . $contactFormValues['email'],
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ]);
        $mailAccepted = function_exists('mail') && @mail(CONTACT_EMAIL, $subject, $message, $headers);

        if ($mailAccepted) {
            $contactFormStatusType = 'success';
            $contactFormStatus = 'Thank you. Your enquiry has been sent and Tony will be in touch.';
            $contactFormValues = ['name' => '', 'email' => '', 'phone' => '', 'service' => '', 'message' => ''];
        } else {
            $contactFormStatusType = 'error';
            $contactFormStatus = "We couldn't send your enquiry right now. Please try again or email Tony directly at " . CONTACT_EMAIL . '.';
            http_response_code(503);
        }
    }

    if (strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest') {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'ok' => $contactFormStatusType === 'success',
            'message' => $contactFormStatus,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['newsletter_form'] ?? '') === '1') {
    $submittedNewsletterEmail = $_POST['email'] ?? '';
    $newsletterEmail = is_string($submittedNewsletterEmail) ? trim($submittedNewsletterEmail) : '';
    $newsletterEmailIsValid = filter_var($newsletterEmail, FILTER_VALIDATE_EMAIL) !== false
        && strlen($newsletterEmail) <= 160
        && !preg_match('/[\r\n]/', $newsletterEmail);

    if (!$newsletterEmailIsValid) {
        $newsletterStatusType = 'error';
        $newsletterStatus = 'Enter a valid email address and try again.';
        http_response_code(422);
    } else {
        $newsletterHeaders = implode("\r\n", [
            'From: ' . SITE_NAME . ' <' . CONTACT_EMAIL . '>',
            'Reply-To: ' . $newsletterEmail,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ]);
        $newsletterBody = 'Newsletter updates requested for: ' . $newsletterEmail;
        $newsletterMailAccepted = function_exists('mail')
            && @mail(CONTACT_EMAIL, 'Newsletter updates request', $newsletterBody, $newsletterHeaders);

        if ($newsletterMailAccepted) {
            $newsletterStatusType = 'success';
            $newsletterStatus = 'Thank you. Your request for updates has been sent to Tony.';
            $newsletterEmail = '';
        } else {
            $newsletterStatusType = 'error';
            $newsletterStatus = "We couldn't send your request right now. Please email Tony directly at " . CONTACT_EMAIL . '.';
            http_response_code(503);
        }
    }

    if (strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest') {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'ok' => $newsletterStatusType === 'success',
            'message' => $newsletterStatus,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>
<!doctype html>
<html lang="en-GB">
<head>
    <?php require __DIR__ . '/include/head.php'; ?>
</head>
<body>
    <div class="page-loader" data-page-loader role="status" aria-label="Loading Tony Handy Work Services">
        <span class="page-loader-brand"><img src="<?= h(asset_url('assets/images/logo.svg')) ?>" alt="" width="180" height="173"></span>
        <span class="page-loader-track" aria-hidden="true"><span></span></span>
    </div>
    <script>
        (() => {
            const loader = document.querySelector('[data-page-loader]');
            if (!loader) return;
            const hideLoader = () => {
                loader.classList.add('is-hidden');
                loader.setAttribute('aria-hidden', 'true');
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', hideLoader, { once: true });
                window.setTimeout(hideLoader, 8000);
            } else {
                hideLoader();
            }
        })();
    </script>
    <a class="skip-link" href="#main">Skip to content</a>
    <?php require __DIR__ . '/include/header.php'; ?>

    <main id="main">
        <section class="hero-section" id="home" aria-labelledby="hero-title">
            <div class="hero-marquee" aria-hidden="true">
                <div class="hero-marquee-track">
                    <?php for ($copy = 0; $copy < 2; $copy++): ?>
                        <div class="hero-marquee-group">
                            <?php foreach ($services as $serviceIndex => $service): ?>
                                <figure class="hero-marquee-item">
                                    <img src="<?= h(asset_url($service['image'])) ?>" alt="" width="1500" height="1125" loading="eager" fetchpriority="high" decoding="async">
                                </figure>
                            <?php endforeach; ?>
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="hero-marquee-veil"></div>
            </div>
            <div class="container hero-content">
                <div class="hero-copy" data-reveal>
                    <p class="eyebrow"><span class="hero-eyebrow-text">North London handyman &amp; small removals</span></p>
                    <h1 id="hero-title">Handyman help.<br><span>Small moves.</span></h1>
                    <p class="hero-intro">Flat-pack assembly, TV mounting, shelf and picture hanging, furniture disassembly and re-assembly, minor home repairs, plus studio and one-bed removals. Based in N15, serving nearby North London areas.</p>
                    <div class="hero-actions">
                        <a class="button button-primary" href="tel:<?= h(CONTACT_TEL) ?>"><span class="fa-icon fa-icon-phone" aria-hidden="true"></span> Call Tony</a>
                        <a class="text-link" href="#services">Browse all services <span class="fa-icon fa-icon-arrow-down" aria-hidden="true"></span></a>
                    </div>
                </div>
            </div>
            <div class="container hero-bottom">
                <span>Assembly &middot; TV mounting &middot; shelf fitting &middot; home repairs &middot; small moves</span>
                <a href="#contact">Talk through the job <b class="fa-icon fa-icon-arrow-down" aria-hidden="true"></b></a>
            </div>
        </section>

        <section class="qualities-strip" aria-label="Service qualities">
            <div class="container qualities-grid">
                <div data-reveal><span class="quality-icon" aria-hidden="true"><span class="fa-icon fa-icon-check"></span></span><p><strong>Reliable</strong><small>You can count on me</small></p></div>
                <div data-reveal style="--reveal-delay: 70ms"><span class="quality-icon" aria-hidden="true"><span class="fa-icon fa-icon-clock"></span></span><p><strong>On time</strong><small>Punctual & professional</small></p></div>
                <div data-reveal style="--reveal-delay: 140ms"><span class="quality-icon" aria-hidden="true"><span class="fa-icon fa-icon-star"></span></span><p><strong>Quality work</strong><small>Done right, every time</small></p></div>
                <div data-reveal style="--reveal-delay: 210ms"><span class="quality-icon" aria-hidden="true"><span class="fa-icon fa-icon-arrows-left-right"></span></span><p><strong>Friendly service</strong><small>Respectful & tidy</small></p></div>
            </div>
        </section>

        <div class="marquee-band" aria-hidden="true">
            <div class="marquee-track" data-marquee-track>
                <div class="marquee-group" data-marquee-group>
                    <span>Honest service. Quality work. Customer satisfaction. <b class="fa-icon fa-icon-star" aria-hidden="true"></b></span>
                    <span>Reliable help for the little things. <b class="fa-icon fa-icon-star" aria-hidden="true"></b></span>
                    <span>Flat-packs, fittings and minor repairs. <b class="fa-icon fa-icon-star" aria-hidden="true"></b></span>
                    <span>Careful help with small moves. <b class="fa-icon fa-icon-star" aria-hidden="true"></b></span>
                </div>
            </div>
        </div>

        <section class="services-section" id="services" aria-labelledby="services-title">
            <div class="services-scroll-stage" data-services-stage>
                <div class="services-pin" data-services-pin>
                    <div class="container">
                        <div class="section-heading section-heading-row services-heading">
                            <div data-reveal>
                                <p class="eyebrow"><span class="eyebrow-mark" aria-hidden="true"></span> What can Tony help with?</p>
                                <h2 id="services-title">A good pair of hands.<br><span>For a lot of little jobs.</span></h2>
                            </div>
                            <p class="section-aside" data-reveal style="--reveal-delay: 120ms">From one shelf to a one-bed move, get the right help for the jobs that make home feel like home.</p>
                        </div>
                    </div>
                    <div class="container">
                        <div class="services-track-viewport" data-services-viewport role="region" aria-label="Available handyman services">
                            <div class="services-track" data-services-track>
                                <?php foreach ($services as $index => $service): ?>
                                    <article class="service-card" data-reveal style="--reveal-delay: <?= ($index % 4) * 60 ?>ms">
                                        <figure class="image-frame service-image">
                                            <img src="<?= h(asset_url($service['image'])) ?>" alt="<?= h($service['alt']) ?>" width="1500" height="1125" loading="lazy">
                                        </figure>
                                        <div class="service-card-copy">
                                            <p class="service-category"><?= h($service['category']) ?></p>
                                            <h3><?= h($service['title']) ?></h3>
                                            <p><?= h($service['summary']) ?></p>
                                            <button class="service-detail-button" type="button" data-service-details data-title="<?= h($service['title']) ?>" data-category="<?= h($service['category']) ?>" data-summary="<?= h($service['summary']) ?>" data-detail="<?= h($service['detail']) ?>" data-image="<?= h(asset_url($service['image'])) ?>" data-image-position="<?= h($service['image_position']) ?>" data-alt="<?= h($service['alt']) ?>" aria-haspopup="dialog" aria-controls="service-detail-modal" aria-label="View details for <?= h($service['title']) ?>">View details <span class="fa-icon fa-icon-arrow-right" aria-hidden="true"></span></button>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="container services-scroll-footer">
                        <span>Scroll or swipe to explore every service</span>
                        <div class="services-progress" data-services-progress role="progressbar" aria-label="Service list progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span></span></div>
                        <strong data-services-count>8 services</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="process-section section-pad" id="process" aria-labelledby="process-title">
            <div class="container">
                <div class="section-heading process-heading" data-reveal>
                    <p class="eyebrow"><span class="eyebrow-mark" aria-hidden="true"></span> Getting started</p>
                    <h2 id="process-title">A clear way to<br><span>get things sorted.</span></h2>
                </div>
                <div class="process-grid">
                    <article class="process-step" data-reveal>
                        <span class="process-number">01</span>
                        <h3>Make a list</h3>
                        <p>Note the fittings, repairs or furniture jobs you would like help with.</p>
                    </article>
                    <article class="process-step" data-reveal style="--reveal-delay: 90ms">
                        <span class="process-number">02</span>
                        <h3>Get in touch</h3>
                        <p>Call, text or email Tony with a few details about the work.</p>
                    </article>
                    <article class="process-step" data-reveal style="--reveal-delay: 180ms">
                        <span class="process-number">03</span>
                        <h3>Talk it through</h3>
                        <p>Discuss the job, your location and a suitable time to take the next step.</p>
                    </article>
                </div>
                <a class="text-link process-link" href="#contact" data-reveal>Share your list <span class="fa-icon fa-icon-arrow-right" aria-hidden="true"></span></a>
            </div>
        </section>

        <section class="moves-section section-pad" id="moves" aria-labelledby="moves-title">
            <div class="container moves-grid">
                <figure class="moves-image-stage" data-reveal>
                    <div class="moves-image">
                        <img src="<?= h(asset_url('assets/images/tony-van.webp')) ?>" alt="Tony's branded white handyman van parked on a residential street" width="1254" height="1254" loading="lazy">
                    </div>
                </figure>
                <div class="moves-copy" data-reveal style="--reveal-delay: 140ms">
                    <p class="eyebrow eyebrow-light"><span class="eyebrow-mark" aria-hidden="true"></span> Small moves · big care</p>
                    <h2 id="moves-title">Moving a little?<br><span>Move with care.</span></h2>
                    <p>For one-bed flats and studio removals, Tony offers a careful, reliable service for the smaller move.</p>
                    <div class="moves-actions">
                        <a class="button button-blue-light" href="tel:<?= h(CONTACT_TEL) ?>"><span class="fa-icon fa-icon-phone" aria-hidden="true"></span> Ask about a small move</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="standards-section section-pad" id="about" aria-labelledby="standards-title">
            <div class="container">
                <div class="section-heading standards-heading">
                    <div data-reveal>
                        <p class="eyebrow eyebrow-light"><span class="eyebrow-mark" aria-hidden="true"></span> The Tony standard</p>
                        <h2 id="standards-title">The small things<br>deserve <span>good work.</span></h2>
                    </div>
                    <p class="standards-aside" data-reveal style="--reveal-delay: 120ms">A friendly local service, with the basics done right: showing up, working carefully and leaving things tidy.</p>
                </div>
                <div class="standards-grid" aria-label="Tony's service qualities">
                    <article class="standard-card" data-reveal>
                        <span class="standard-icon" aria-hidden="true"><span class="fa-icon fa-icon-check"></span></span>
                        <div class="standard-card-copy"><p>RELIABLE</p><h3>You can count on me.</h3><small>Clear communication and dependable help.</small></div>
                    </article>
                    <article class="standard-card" data-reveal style="--reveal-delay: 60ms">
                        <span class="standard-icon" aria-hidden="true"><span class="fa-icon fa-icon-clock"></span></span>
                        <div class="standard-card-copy"><p>ON TIME</p><h3>Punctual &amp; professional.</h3><small>Arriving when agreed and working efficiently.</small></div>
                    </article>
                    <article class="standard-card" data-reveal style="--reveal-delay: 120ms">
                        <span class="standard-icon" aria-hidden="true"><span class="fa-icon fa-icon-star"></span></span>
                        <div class="standard-card-copy"><p>QUALITY WORK</p><h3>Done right, every time.</h3><small>Careful, tidy work completed properly.</small></div>
                    </article>
                    <article class="standard-card" data-reveal style="--reveal-delay: 180ms">
                        <span class="standard-icon" aria-hidden="true"><span class="fa-icon fa-icon-arrows-left-right"></span></span>
                        <div class="standard-card-copy"><p>FRIENDLY SERVICE</p><h3>Respectful &amp; tidy.</h3><small>Friendly service in your home.</small></div>
                    </article>
                </div>
                <div class="standards-bottom"><span data-reveal>Fully insured</span><span data-reveal style="--reveal-delay: 70ms">Affordable service</span><span data-reveal style="--reveal-delay: 140ms">North London · N15 & surrounding areas</span></div>
            </div>
        </section>

        <section class="faq-section section-pad" id="faq" aria-labelledby="faq-title">
            <div class="container faq-grid">
                <div class="faq-heading" data-reveal>
                    <p class="eyebrow"><span class="eyebrow-mark" aria-hidden="true"></span> Before you get in touch</p>
                    <h2 id="faq-title">A few useful<br><span>things to know.</span></h2>
                    <p>Got a question about a job? Call or text Tony and talk it through.</p>
                    <a class="text-link" href="tel:<?= h(CONTACT_TEL) ?>"><span class="fa-icon fa-icon-phone" aria-hidden="true"></span> Call / text <?= h(CONTACT_PHONE) ?></a>
                </div>
                <div class="faq-list" data-reveal style="--reveal-delay: 120ms">
                    <div class="faq-item">
                        <h3 class="faq-item-heading"><button class="faq-question" id="faq-question-areas" type="button" aria-expanded="true" aria-controls="faq-answer-areas"><span>Which areas do you cover?</span><span class="faq-toggle" aria-hidden="true"><span class="fa-icon fa-icon-plus"></span><span class="fa-icon fa-icon-minus"></span></span></button></h3>
                        <div class="faq-answer" id="faq-answer-areas" role="region" aria-labelledby="faq-question-areas" aria-hidden="false"><div class="faq-answer-inner"><p>Tony is based in N15 and serves the surrounding areas of North London.</p></div></div>
                    </div>
                    <div class="faq-item">
                        <h3 class="faq-item-heading"><button class="faq-question" id="faq-question-services" type="button" aria-expanded="false" aria-controls="faq-answer-services"><span>What kind of jobs can you help with?</span><span class="faq-toggle" aria-hidden="true"><span class="fa-icon fa-icon-plus"></span><span class="fa-icon fa-icon-minus"></span></span></button></h3>
                        <div class="faq-answer" id="faq-answer-services" role="region" aria-labelledby="faq-question-services" aria-hidden="true" inert><div class="faq-answer-inner"><p>Flat-pack assembly, TV mounting, shelf installation, picture and mirror hanging, furniture disassembly and re-assembly, minor home repairs, and one-bed flat or studio removals.</p></div></div>
                    </div>
                    <div class="faq-item">
                        <h3 class="faq-item-heading"><button class="faq-question" id="faq-question-booking" type="button" aria-expanded="false" aria-controls="faq-answer-booking"><span>How can I book?</span><span class="faq-toggle" aria-hidden="true"><span class="fa-icon fa-icon-plus"></span><span class="fa-icon fa-icon-minus"></span></span></button></h3>
                        <div class="faq-answer" id="faq-answer-booking" role="region" aria-labelledby="faq-question-booking" aria-hidden="true" inert><div class="faq-answer-inner"><p>Call or text <a href="tel:<?= h(CONTACT_TEL) ?>"><?= h(CONTACT_PHONE) ?></a>, or email <a href="mailto:<?= h(CONTACT_EMAIL) ?>"><?= h(CONTACT_EMAIL) ?></a> with a few details about the job.</p></div></div>
                    </div>
                    <div class="faq-item">
                        <h3 class="faq-item-heading"><button class="faq-question" id="faq-question-insured" type="button" aria-expanded="false" aria-controls="faq-answer-insured"><span>Is the service insured?</span><span class="faq-toggle" aria-hidden="true"><span class="fa-icon fa-icon-plus"></span><span class="fa-icon fa-icon-minus"></span></span></button></h3>
                        <div class="faq-answer" id="faq-answer-insured" role="region" aria-labelledby="faq-question-insured" aria-hidden="true" inert><div class="faq-answer-inner"><p>Yes. The service is fully insured.</p></div></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="contact-section" id="contact" aria-labelledby="contact-title">
            <div class="container contact-inner">
                <div class="contact-copy" data-reveal>
                    <p class="eyebrow"><span class="eyebrow-mark" aria-hidden="true"></span> North London · N15 & nearby</p>
                    <h2 id="contact-title">Got a list of<br><span>little jobs?</span></h2>
                    <p>From mounting shelves to minor home repairs, tell Tony what you need.</p>
                    <div class="contact-actions">
                        <a class="button button-dark" href="tel:<?= h(CONTACT_TEL) ?>"><span class="fa-icon fa-icon-phone" aria-hidden="true"></span> Call / text <?= h(CONTACT_PHONE) ?></a>
                    </div>
                </div>
                <form class="contact-form" action="#contact" method="post" data-contact-form data-reveal style="--reveal-delay: 120ms" novalidate>
                    <input type="hidden" name="contact_form" value="1">
                    <div class="contact-form-heading">
                        <p class="eyebrow"><span class="eyebrow-mark" aria-hidden="true"></span> A few details</p>
                        <h3>Tell Tony about the job.</h3>
                    </div>
                    <div class="contact-form-grid">
                        <div class="contact-field">
                            <label for="enquiry-name">Your name</label>
                            <input class="form-control" id="enquiry-name" name="name" type="text" autocomplete="name" minlength="2" maxlength="100" value="<?= h($contactFormValues['name']) ?>" required>
                        </div>
                        <div class="contact-field">
                            <label for="enquiry-email">Email address</label>
                            <input class="form-control" id="enquiry-email" name="email" type="email" autocomplete="email" maxlength="160" value="<?= h($contactFormValues['email']) ?>" required>
                        </div>
                        <div class="contact-field">
                            <label for="enquiry-phone">Phone number <span>(optional)</span></label>
                            <input class="form-control" id="enquiry-phone" name="phone" type="tel" autocomplete="tel" maxlength="32" value="<?= h($contactFormValues['phone']) ?>">
                        </div>
                        <div class="contact-field">
                            <label for="enquiry-service">What do you need help with?</label>
                            <select class="form-select" id="enquiry-service" name="service" required>
                                <option value="" <?= $contactFormValues['service'] === '' ? 'selected' : '' ?> disabled>Choose a service</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= h($service['title']) ?>" <?= $contactFormValues['service'] === $service['title'] ? 'selected' : '' ?>><?= h($service['title']) ?></option>
                                <?php endforeach; ?>
                                <option value="Something else" <?= $contactFormValues['service'] === 'Something else' ? 'selected' : '' ?>>Something else</option>
                            </select>
                        </div>
                        <div class="contact-field contact-field-wide">
                            <label for="enquiry-message">A few details about the job</label>
                            <textarea class="form-control" id="enquiry-message" name="message" rows="4" minlength="10" maxlength="2000" required><?= h($contactFormValues['message']) ?></textarea>
                        </div>
                    </div>
                    <div class="contact-form-submit">
                        <button class="button button-dark" type="submit">Send enquiry <span class="fa-icon fa-icon-paper-plane" aria-hidden="true"></span></button>
                    </div>
                    <p class="form-status<?= $contactFormStatusType !== '' ? ' is-' . h($contactFormStatusType) : '' ?>" role="status" aria-live="polite"><?= h($contactFormStatus) ?></p>
                </form>
            </div>
        </section>
    </main>

    <div class="service-modal" id="service-detail-modal" data-service-modal hidden aria-hidden="true">
        <section class="service-modal-panel" role="dialog" aria-modal="true" aria-labelledby="service-modal-title" aria-describedby="service-modal-summary" tabindex="-1">
            <button class="service-modal-close" type="button" data-service-modal-close aria-label="Close service details"><span class="fa-icon fa-icon-xmark" aria-hidden="true"></span></button>
            <div class="service-modal-media">
                <img data-service-modal-image src="" alt="" width="1500" height="1125">
                <span class="service-modal-image-label">Tony Handy Work Services <b>·</b> North London</span>
            </div>
            <div class="service-modal-content">
                <p class="service-modal-kicker"><span class="eyebrow-mark" aria-hidden="true"></span> Service details</p>
                <p class="service-modal-category" data-service-modal-category></p>
                <h2 id="service-modal-title" data-service-modal-title></h2>
                <p class="service-modal-summary" id="service-modal-summary" data-service-modal-summary></p>
                <p class="service-modal-detail" data-service-modal-detail></p>
                <div class="service-modal-note"><span class="service-modal-note-icon" aria-hidden="true"><span class="fa-icon fa-icon-check"></span></span> Fully insured · serving N15 &amp; nearby</div>
                <a class="button button-primary service-modal-ask" href="#contact" data-service-enquiry>Ask about this service <span class="fa-icon fa-icon-arrow-right" aria-hidden="true"></span></a>
            </div>
        </section>
    </div>

    <?php require __DIR__ . '/include/footer.php'; ?>
</body>
</html>
