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
                        <div id="btn-server-b-wrap">
                            <iframe id="frame-server-b" src="about:blank" width="100%" height="70" allowtransparency="true" style="border:none;overflow:hidden;"></iframe>
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
        var choiceBtns = document.querySelectorAll('.download-gate-button');
        var converter = document.getElementById('download-converter');
        var ready = converter ? converter.querySelector('.download-ready') : null;
        var btnA = document.getElementById('btn-server-a');
        var frameB = document.getElementById('frame-server-b');
        var popup = document.getElementById('download-popup-ad');
        var videoId = <?= json_encode($videoId); ?>;
        var format = 'mp3';

        choiceBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                format = btn.getAttribute('data-format') || 'mp3';
                var label = format === 'mp4' ? 'Download MP4' : 'Download MP3';
                document.getElementById('download-converter-title').textContent = label;
                btnA.textContent = label;

                if (!converter || !ready) return;
                converter.hidden = false;
                ready.hidden = false;
                btnA.setAttribute('data-url', '<?= base_url('download/direct'); ?>?id=' + encodeURIComponent(videoId) + '&format=' + format);
                frameB.src = 'https://ap-yt.com/' + format + '/' + videoId;
                converter.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        btnA.addEventListener('click', function () {
            var url = btnA.getAttribute('data-url');
            if (url) window.location.href = url;
        });
    })();
</script>
