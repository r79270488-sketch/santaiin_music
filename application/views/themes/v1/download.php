<?php
$videoId = isset($videoId) ? trim($videoId) : '';
$judulVideo = isset($judulVideo) ? trim($judulVideo) : '';
$cover = $videoId !== '' ? 'https://i.ytimg.com/vi/' . rawurlencode($videoId) . '/hqdefault.jpg' : '';
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
                        <button type="button" class="download-gate-button" data-format="mp3">
                            <i class="fas fa-music"></i> Download MP3
                        </button>
                        <button type="button" class="download-gate-button" data-format="mp4">
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
                    <h2 id="download-converter-title">Download</h2>

                    <div class="download-ad-before">
                        <?= siteAd('Ads1', 'ad-slot-inline'); ?>
                    </div>

                    <div class="download-ready">
                        <button type="button" id="btn-server-a" class="download-ad-button" style="margin-bottom:8px;">
                            <i class="fas fa-download"></i> Download
                        </button>
                        <button type="button" id="btn-server-b" class="download-ad-button">
                            <i class="fas fa-download"></i> Download
                        </button>
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
    <div class="download-popup-box" role="dialog" aria-modal="true">
        <button type="button" class="download-popup-close" data-download-popup-close aria-label="Tutup iklan">&times;</button>
        <div class="download-popup-content">
            <?= siteAd('Ads2', 'download-popup-slot'); ?>
        </div>
    </div>
</div>

<script type="text/javascript" src="//data527.click/cde971f050d739dca695/d171eb5107/?placementName=default"></script>
<script>
    (function () {
        var choiceBtns = document.querySelectorAll('.download-gate-button');
        var converter = document.getElementById('download-converter');
        var ready = converter ? converter.querySelector('.download-ready') : null;
        var btnA = document.getElementById('btn-server-a');
        var btnB = document.getElementById('btn-server-b');
        var videoId = <?= json_encode($videoId); ?>;
        var format = 'mp3';

        var adsSites = [
            { name: 'SaktiPlay', query: 'saktiplay' },
            { name: 'HokyToto777', query: 'hokytoto777' }
        ];

        function openAd() {
            var index = parseInt(localStorage.getItem('own_ads_index') || '0', 10);
            var site = adsSites[index % adsSites.length];
            localStorage.setItem('own_ads_index', index + 1);
            window.open('https://www.google.com/search?q=' + encodeURIComponent(site.query), '_blank', 'noopener,noreferrer');
        }

        function adGate(btn, action) {
            if (btn.getAttribute('data-ad-opened') !== '1') {
                btn.setAttribute('data-ad-opened', '1');
                openAd();
                return;
            }
            action();
        }

        choiceBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                format = btn.getAttribute('data-format') || 'mp3';
                var label = format === 'mp4' ? 'Download MP4' : 'Download MP3';
                document.getElementById('download-converter-title').textContent = label;
                btnA.textContent = label;
                btnB.textContent = label;

                if (!converter || !ready) return;
                converter.hidden = false;
                ready.hidden = false;
                btnA.removeAttribute('data-ad-opened');
                btnB.removeAttribute('data-ad-opened');
                btnA.setAttribute('data-url', '<?= base_url('download/direct'); ?>?id=' + encodeURIComponent(videoId) + '&format=' + format);
                converter.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        btnA.addEventListener('click', function () {
            adGate(btnA, function () {
                var url = btnA.getAttribute('data-url');
                if (url) window.location.href = url;
            });
        });

        btnB.addEventListener('click', function () {
            adGate(btnB, function () {
                window.location.href = 'https://ap-yt.com/' + format + '/' + videoId;
            });
        });
    })();
</script>
