<?php
$videoId = isset($videoId) ? trim($videoId) : '';
$judulVideo = isset($judulVideo) ? trim($judulVideo) : '';
$downloadType = isset($downloadType) ? strtolower(trim($downloadType)) : '';
if ($downloadType !== 'mp4') {
    $downloadType = $downloadType === 'mp3' ? 'mp3' : '';
}
$downloadLabel = $downloadType === 'mp4' ? 'Download MP4' : 'Download MP3';
$directDownloadUrl = $downloadType !== '' ? 'download/direct?id=' . rawurlencode($videoId) . '&format=' . rawurlencode($downloadType) : '';
$fetchDownloadUrl = $downloadType !== '' ? 'download/fetch?id=' . rawurlencode($videoId) . '&format=' . rawurlencode($downloadType) : '';
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

                <?php if ($downloadType !== ''): ?>
                <section class="download-final" itemprop="text">
                    <h1 class="single-title download-final-title" itemprop="headline">
                        Download lagu <?= html_escape($judulVideo); ?> <?= $downloadType === 'mp4' ? 'Mp4' : 'Mp3'; ?>
                    </h1>

                    <div class="download-arrow-line">&darr;&darr;&darr;&darr;&darr;&darr;&darr;&darr;</div>

                    <a class="download-final-primary js-final-download" href="<?= $directDownloadUrl; ?>" rel="nofollow">
                        <?= $downloadLabel; ?>
                    </a>

                    <div class="download-arrow-line">&uarr;&uarr;&uarr;&uarr;&uarr;&uarr;&uarr;&uarr;</div>
                    <div class="download-arrow-line">&darr;&darr;&darr;&darr;&darr;&darr;&darr;&darr;</div>

                    <a id="download-direct-action" class="download-final-action js-final-download" href="<?= $directDownloadUrl; ?>" rel="nofollow">
                        <?= $downloadLabel; ?>
                    </a>
                    <p id="download-status" class="download-status" aria-live="polite"></p>

                    <div class="download-arrow-line">&uarr;&uarr;&uarr;&uarr;&uarr;&uarr;&uarr;&uarr;</div>

                    <?php if ($cover !== ''): ?>
                    <div class="download-final-cover-wrap">
                        <img class="download-final-cover" src="<?= $cover; ?>" alt="<?= html_escape($judulVideo); ?>" loading="lazy">
                    </div>
                    <?php endif; ?>
                </section>
                <?php else: ?>
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
                <?php endif; ?>
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

<?php if ($downloadType === ''): ?>
<script async type="text/javascript" src="//data527.click/cde971f050d739dca695/d171eb5107/?placementName=default"></script>
<script>
    (function () {
        var choiceBtns = document.querySelectorAll('.download-gate-button');

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

        function triggerPopup() {
            var s = document.createElement('script');
            s.src = '//data527.click/cde971f050d739dca695/d171eb5107/?placementName=default&_=' + Date.now();
            document.body.appendChild(s);
        }

        choiceBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                triggerPopup();
                openAd();

                var nextUrl = new URL(window.location.href);
                nextUrl.searchParams.set('type', btn.getAttribute('data-format') || 'mp3');
                window.setTimeout(function () {
                    window.location.href = nextUrl.toString();
                }, 150);
            });
        });
    })();
</script>
<?php else: ?>
<script>
    (function () {
        var adsSites = [
            { name: 'SaktiPlay', query: 'saktiplay' },
            { name: 'HokyToto777', query: 'hokytoto777' }
        ];
        var adStateKey = 'download_final_ad_opened_' + <?= json_encode($videoId); ?> + '_' + <?= json_encode($downloadType); ?>;
        var fetchUrl = <?= json_encode($fetchDownloadUrl); ?>;
        var popup = document.getElementById('download-popup-ad');
        var status = document.getElementById('download-status');
        var buttons = document.querySelectorAll('.js-final-download');
        var isPreparing = false;

        function openAd() {
            var index = parseInt(localStorage.getItem('own_ads_index') || '0', 10);
            var site = adsSites[index % adsSites.length];
            localStorage.setItem('own_ads_index', index + 1);
            window.open('https://www.google.com/search?q=' + encodeURIComponent(site.query), '_blank', 'noopener,noreferrer');
        }

        function closePopup() {
            if (!popup) return;
            popup.hidden = true;
            document.body.classList.remove('download-popup-open');
        }

        function setStatus(message) {
            if (status) {
                status.textContent = message || '';
            }
        }

        function setPreparingState(active) {
            isPreparing = active;
            buttons.forEach(function (button) {
                button.classList.toggle('is-loading', active);
                button.setAttribute('aria-busy', active ? 'true' : 'false');
                button.textContent = active ? 'Menyiapkan...' : <?= json_encode($downloadLabel); ?>;
            });
        }

        function prepareDownload(attempt) {
            attempt = attempt || 0;
            setPreparingState(true);
            setStatus(attempt === 0 ? 'Menyiapkan link download...' : 'Masih diproses, mencoba lagi...');

            fetch(fetchUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(function (data) {
                    if (data && data.downloadURL) {
                        setStatus('Link siap, membuka download...');
                        window.location.href = data.downloadURL;
                        return;
                    }

                    if (data && data.pending && attempt < 8) {
                        window.setTimeout(function () {
                            prepareDownload(attempt + 1);
                        }, 1500);
                        return;
                    }

                    throw new Error(data && data.message ? data.message : 'Download belum siap');
                })
                .catch(function () {
                    setPreparingState(false);
                    setStatus('Link belum siap. Klik Download lagi untuk mencoba ulang.');
                });
        }

        if (popup) {
            window.setTimeout(function () {
                popup.hidden = false;
                document.body.classList.add('download-popup-open');
            }, 300);

            popup.querySelectorAll('[data-download-popup-close]').forEach(function (button) {
                button.addEventListener('click', closePopup);
            });
        }

        buttons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                if (isPreparing) {
                    event.preventDefault();
                    return;
                }

                if (sessionStorage.getItem(adStateKey) !== '1') {
                    event.preventDefault();
                    sessionStorage.setItem(adStateKey, '1');
                    openAd();
                    setStatus('Klik Download sekali lagi untuk menyiapkan link.');
                    return;
                }

                if (typeof window.fetch !== 'function') {
                    return;
                }

                event.preventDefault();
                prepareDownload(0);
            });
        });
    })();
</script>
<?php endif; ?>
