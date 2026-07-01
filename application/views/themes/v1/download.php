<?php
$videoId = isset($videoId) ? trim($videoId) : '';
$judulVideo = isset($judulVideo) ? trim($judulVideo) : '';
$cover = $videoId !== '' ? 'https://i.ytimg.com/vi/' . rawurlencode($videoId) . '/hqdefault.jpg' : '';
$adClickUrl = base_url();
$adHtml = siteBase('Ads2');

if (!empty($adHtml) && preg_match('/href=["\']([^"\']+)/i', $adHtml, $matches)) {
    $adClickUrl = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
}
?>
<div id="site-container">
    <div class="container">
        <div class="row download-page" itemscope="itemscope" itemtype="http://schema.org/CreativeWork">
            <div class="col-md-12">
                <div class="download-breadcrumb">
                    <a href="<?= base_url(); ?>">Home</a>
                    <span>&rsaquo;</span>
                    <a href="<?= search_permalink($judulVideo); ?>"><?= html_escape($judulVideo); ?></a>
                    <span>&rsaquo;</span>
                    <strong>Download Lagu <?= html_escape($judulVideo); ?></strong>
                </div>

                <?= siteAd('Ads2', 'ad-slot-top download-top-ad'); ?>

                <section class="download-stage" itemprop="text">
                    <h1 class="single-title download-title" itemprop="headline">
                        Download Lagu <?= html_escape($judulVideo); ?> Mp3
                    </h1>

                    <div class="download-choice">
                        <button type="button" class="download-gate-button" data-format="mp3" data-label="Download MP3">
                            <i class="fas fa-music"></i> Download MP3
                        </button>
                        <button type="button" class="download-gate-button download-gate-video" data-format="mp4" data-label="Download Video">
                            <i class="fas fa-video"></i> Download Video
                        </button>
                    </div>

                    <?php if ($cover !== ''): ?>
                    <div class="download-cover-wrap">
                        <img class="download-cover" src="<?= $cover; ?>" alt="<?= html_escape($judulVideo); ?>" loading="lazy">
                    </div>
                    <?php endif; ?>

                    <div class="download-summary">
                        <strong><?= html_escape($judulVideo); ?></strong>
                        <span>Audio MP3 atau video MP4 dari Youtube Music.</span>
                    </div>
                </section>

                <section id="download-converter" class="download-converter" hidden>
                    <h2 id="download-converter-title">Menyiapkan download</h2>

                    <div class="download-ad-before">
                        <?= siteAd('Ads1', 'ad-slot-inline'); ?>
                    </div>

                    <div class="download-loading" aria-live="polite">
                        <span class="download-spinner"></span>
                        <span>Tunggu <strong id="download-countdown">3</strong> detik, link download sedang disiapkan.</span>
                    </div>

                    <div class="download-real-action" hidden>
                        <div class="download-arrow-line">^^^^^^^</div>
                        <button type="button" id="download-ad-button" class="download-ad-button">Download Ads</button>
                        <div class="download-arrow-line">vvvvvvv</div>
                        <div class="download-frame-box" data-frame-mp3="https://ytmp3.plus/button-api/#<?= rawurlencode($videoId); ?>|mp3" data-frame-mp4="https://ytmp3.plus/button-api/#<?= rawurlencode($videoId); ?>|mp4">
                            <iframe
                                id="download-frame"
                                src="about:blank"
                                width="300"
                                height="54"
                                title="Download"
                                scrolling="no"
                                loading="lazy"
                                referrerpolicy="no-referrer"
                                style="border:none;overflow:hidden;">
                            </iframe>
                        </div>
                    </div>

                    <div class="download-ad-after">
                        <?= siteAd('Ads3', 'ad-slot-bottom'); ?>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<div id="download-popup-ad" class="download-popup-ad" hidden>
    <div class="download-popup-backdrop" data-download-popup-close></div>
    <div class="download-popup-box" role="dialog" aria-modal="true" aria-label="Advertisement">
        <button type="button" class="download-popup-close" data-download-popup-close aria-label="Tutup iklan">&times;</button>
        <div class="download-popup-content">
            <?= siteAd('Ads2', 'download-popup-slot'); ?>
        </div>
    </div>
</div>

<script>
    (function () {
        var buttons = document.querySelectorAll('.download-gate-button');
        var converter = document.getElementById('download-converter');
        var title = document.getElementById('download-converter-title');
        var countdown = document.getElementById('download-countdown');
        var loading = converter ? converter.querySelector('.download-loading') : null;
        var realAction = converter ? converter.querySelector('.download-real-action') : null;
        var frameBox = converter ? converter.querySelector('.download-frame-box') : null;
        var frame = document.getElementById('download-frame');
        var popup = document.getElementById('download-popup-ad');
        var popupClose = document.querySelectorAll('[data-download-popup-close]');
        var adButton = document.getElementById('download-ad-button');
        var adClickUrl = <?= json_encode($adClickUrl); ?>;
        var popupKey = 'downloadPopupSeen:' + <?= json_encode($videoId); ?>;
        var timer = null;

        function openDownloadAdTab() {
            if (!adClickUrl) return;
            window.open(adClickUrl, '_blank', 'noopener,noreferrer');
        }

        function showDownloadPopup() {
            if (!popup) return;
            popup.hidden = false;
            document.body.classList.add('download-popup-open');
        }

        function hideDownloadPopup() {
            if (!popup) return;
            popup.hidden = true;
            document.body.classList.remove('download-popup-open');
        }

        function showAutoPopupOnce() {
            var hasSeen = false;
            try {
                hasSeen = window.sessionStorage.getItem(popupKey) === '1';
            } catch (error) {
                hasSeen = popup.getAttribute('data-shown') === '1';
            }
            if (hasSeen) return;
            showDownloadPopup();
            try {
                window.sessionStorage.setItem(popupKey, '1');
            } catch (error) {
                popup.setAttribute('data-shown', '1');
            }
        }

        function startDownloadGate(format) {
            var label = format === 'mp4' ? 'Download Video' : 'Download MP3';
            var frameUrl = frameBox ? frameBox.getAttribute('data-frame-' + format) : '';
            var seconds = 3;

            if (!converter || !frame || !frameUrl) return;

            window.clearInterval(timer);
            converter.hidden = false;
            loading.hidden = false;
            realAction.hidden = true;
            frame.setAttribute('src', 'about:blank');
            title.textContent = label;
            if (adButton) adButton.textContent = label;
            countdown.textContent = seconds;

            converter.scrollIntoView({ behavior: 'smooth', block: 'start' });

            timer = window.setInterval(function () {
                seconds -= 1;
                countdown.textContent = seconds;
                if (seconds <= 0) {
                    window.clearInterval(timer);
                    loading.hidden = true;
                    realAction.hidden = false;
                    frame.setAttribute('title', label);
                    frame.setAttribute('src', frameUrl);
                }
            }, 1000);
        }

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var label = button.getAttribute('data-label') || button.textContent.trim();
                if (button.getAttribute('data-ad-opened') !== '1') {
                    button.setAttribute('data-ad-opened', '1');
                    button.classList.add('is-ready');
                    button.innerHTML = '<i class="fas fa-download"></i> Klik Lagi ' + label;
                    openDownloadAdTab();
                    return;
                }
                startDownloadGate(button.getAttribute('data-format') || 'mp3');
            });
        });

        if (adButton) adButton.addEventListener('click', openDownloadAdTab);

        popupClose.forEach(function (button) {
            button.addEventListener('click', hideDownloadPopup);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') hideDownloadPopup();
        });

        window.setTimeout(showAutoPopupOnce, 700);
    })();
</script>
