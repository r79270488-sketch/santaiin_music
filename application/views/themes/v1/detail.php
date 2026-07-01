        <div id="site-container">
            <div class="container">
                <div class="row detail-layout" itemscope="itemscope" itemtype="http://schema.org/MusicRecording">
                    <div class="col-md-12">
                        <?php $cover = 'https://i.ytimg.com/vi/' . $id_video . '/hqdefault.jpg'; ?>
                        <?php $lyrics = getSongLyrics($title_meta); ?>
                        <?php $lyricsQuery = cleanSongTitleForLyrics($title_meta); ?>
                        <script type="application/ld+json">
                        {
                            "@context": "https://schema.org",
                            "@type": "MusicRecording",
                            "name": "<?= str_replace('"', '\"', $title_meta); ?>",
                            "url": "<?= base_url(uri_string()); ?>",
                            "image": "https://i.ytimg.com/vi/<?= $id_video; ?>/hqdefault.jpg",
                            "byArtist": {
                                "@type": "MusicGroup",
                                "name": "<?= str_replace('"', '\"', $title_meta); ?>"
                            }
                        }
                        </script>
                        <section class="detail-hero">
                            <h1 class="single-title detail-title" itemprop="headline">Download Lagu <?= $title_meta;?> Mp3</h1>
                            <div class="detail-cover-wrap">
                                <img class="detail-cover" src="<?= $cover; ?>" alt="<?= $title_meta; ?>" width="480" height="360" loading="lazy" itemprop="image" />
                            </div>
                            <h2 class="detail-song-title" itemprop="name"><?= $title_meta;?></h2>
                            <p class="detail-description" itemprop="description"><?= $spin_text;?></p>
                            <dl class="detail-meta">
                                <div>
                                    <dt>Judul</dt>
                                    <dd><?= $title_meta;?></dd>
                                </div>
                                <div>
                                    <dt>Format</dt>
                                    <dd>MP3 / MP4</dd>
                                </div>
                                <div>
                                    <dt>Quality</dt>
                                    <dd>HD, 720P, 480P, 320P, 240P</dd>
                                </div>
                                <div>
                                    <dt>Sumber</dt>
                                    <dd>Youtube Music</dd>
                                </div>
                            </dl>
                        </section>

                        <section class="detail-actions">
                            <a class="primary-download" href="<?= base_url('download?id='.urlencode($id_video).'&title='.urlencode($title_meta)); ?>" rel="nofollow">
                                <i class="fas fa-download"></i> Download Lagu
                            </a>
                            <div class="fast-download-slot">
                                <a href="#" class="primary-download js-own-ads" style="display:inline-flex;">
                                    <i class="fas fa-bolt"></i> Download Cepat
                                </a>
                            </div>
                        </section>

                        <section class="lyrics-section">
                            <h3>Lirik Lagu</h3>
                            <?php if (!empty($lyrics)): ?>
                                <div class="lyrics-box"><?= nl2br(html_escape($lyrics)); ?></div>
                            <?php else: ?>
                                <p>Lirik belum tersedia otomatis untuk lagu ini.</p>
                                <div class="lyrics-actions">
                                    <a href="https://www.google.com/search?q=<?= rawurlencode($lyricsQuery . ' lirik lagu'); ?>" target="_blank" rel="nofollow noopener">Cari Lirik</a>
                                    <a href="<?= search_permalink($lyricsQuery . ' lirik'); ?>">Cari di Situs</a>
                                </div>
                            <?php endif; ?>
                        </section>

                        <section class="video-section">
                            <h3>Video Musik</h3>
                            <div class="featured text-center clearfix">
                                <div id="video" class="tab-pane">
                                    <div class="embed-responsive embed-responsive-16by9">
                                        <iframe width="100%" height="100%" src="https://www.youtube.com/embed/<?= $id_video; ?>?rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="warning" itemprop="text">
                            <strong>Disclaimer:</strong><br />
                            File disini di hosting di Youtube.com bukan di server <?= base_url(); ?> dan kami tidak mengupload pada server kami. Jika menurut anda ini adalah file ilegal, silakan laporkan ke
                            <a href="https://www.youtube.com/reportabuse" rel="nofollow">Youtube</a> dengan id Video <strong><?= $id_video;?></strong>
                        </div>
                        <?= siteAd('Ads3', 'ad-slot-bottom');?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
(function () {
    const sites = [
        { name: 'SaktiPlay', query: 'saktiplay' },
        { name: 'HokyToto777', query: 'hokytoto777' }
    ];

    function getNextSite() {
        let index = parseInt(localStorage.getItem('own_ads_index') || '0', 10);
        let site = sites[index % sites.length];
        localStorage.setItem('own_ads_index', index + 1);
        return site;
    }

    document.querySelectorAll('.js-own-ads').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const site = getNextSite();
            window.open('https://www.google.com/search?q=' + encodeURIComponent(site.query), '_blank', 'noopener,noreferrer');
        });
    });
})();
</script>
