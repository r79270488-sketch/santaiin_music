<!-- Header -->
        <div id="site-container">
            <div class="container">
                <div class="row" itemscope="itemscope" itemtype="http://schema.org/MusicAlbum">
                    <div class="col-md-main">
                        <h1 class="main-title" itemprop="headline"><span class="highlight">Download Lagu MP3</span> <?= $title_parameter;?></h1>
                        <div class="success intro-copy" itemprop="text">
                            <p>Download lagu mp3 <?= $title_parameter;?> gratis. <?= $title_parameter;?> planetlagu, download mp3 <?= $title_parameter;?>, download <?= $title_parameter;?> lagu123.</p>
                            <p>Terdapat sekitar 10 pencarian lagu yang dapat anda download dan dengarkan. Jika hasilnya tidak berisi lagu yang anda cari, cobalah mencari lagu dengan nama artis atau dengan nama lagu tersebut.</p>
                        </div>
                        <?= siteAd('Ads3', 'ad-slot-inline');?>

                        <div itemscope="itemscope" itemtype="http://schema.org/MusicAlbum">
                            <span itemprop="keywords" content="Lagu,Download,Album,Lirik,Artis,Video,Mp3,Song"></span>
                            <span itemprop="publisher" content="Youtube"></span>
                            <span itemprop="name" content="<?= html_escape($title_parameter); ?>"></span>
                            <span itemprop="byArtist" content="<?= html_escape($title_parameter); ?>"></span>
                            <div itemprop="track" itemscope="itemscope" itemtype="http://schema.org/ItemList">
                                <span itemprop="numberOfItems" content="<?= count($music); ?>"></span>
                                    <?php if (empty($music)): ?>
                                    <p>Tidak ada hasil untuk "<?= html_escape($title_parameter); ?>". Coba kata kunci lain.</p>
                                    <?php else: ?>
                                    <?php
                                    $i = 0;
                                    foreach ($music as $item) {
                                        $i++;
                                    ?>
                                <?php
                                    $previewUrl = isset($item['previewUrl']) ? $item['previewUrl'] : '';
                                    $safeTitle = isset($item['judul']) ? $item['judul'] : '';
                                    $isAppleFallback = empty($item['id']) && !empty($item['externalUrl']);
                                    $itemUrl = $isAppleFallback ? $item['externalUrl'] : single_permalink($item['id'],$safeTitle);
                                    $previewJson = html_escape(json_encode($previewUrl));
                                    $titleJson = html_escape(json_encode($safeTitle));
                                ?>
                                <div class="clearfix search-content track-row" itemprop="itemListElement" itemscope="itemscope" itemtype="http://schema.org/ListItem">
                                    <span itemprop="position" content="<?= $i; ?>"></span>
                                    <div class="content-left pull-left">
                                        <img src="<?= html_escape(smallMusicThumbnail($item['thumbnails']));?>" alt="<?= html_escape($safeTitle);?>" width="76" height="76" loading="lazy" itemprop="image" />
                                    </div>
                                    <div class="content-right" itemprop="item" itemscope="itemscope" itemtype="http://schema.org/MusicRecording">
                                        <h2 class="content-title">
                                            <a href="<?= html_escape($itemUrl);?>" title="<?= html_escape($safeTitle);?> mp3" itemprop="url" rel="nofollow<?= $isAppleFallback ? ' noopener' : ''; ?>"<?= $isAppleFallback ? ' target="_blank"' : ''; ?>>
                                                <span itemprop="name"><?= html_escape($safeTitle);?></span>
                                            </a>
                                        </h2>
                                        <div class="button track-actions">
                                            <?php if ($isAppleFallback): ?>
                                            <a href="<?= html_escape($item['externalUrl']); ?>" title="Buka di Apple Music" target="_blank" rel="nofollow noopener">
                                                <i class="fas fa-music"></i> Apple Music
                                            </a>
                                            <a href="<?= search_permalink($safeTitle); ?>" title="Cari sumber download <?= html_escape($safeTitle); ?>" rel="nofollow">
                                                <i class="fas fa-search"></i> Cari Download
                                            </a>
                                            <?php else: ?>
                                            <a href="javascript:void(0);" onclick="playAudio(<?= $i;?>, <?= $previewJson; ?>, <?= $titleJson; ?>);" title="Dengarkan preview audio" rel="nofollow">
                                                <i class="fas fa-play"></i> Play Audio
                                            </a>
                                            <a title="<?= html_escape($safeTitle);?>" href="<?= base_url('download')?>?id=<?= urlencode($item['id']);?>&title=<?= urlencode($safeTitle);?>" rel="nofollow">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                            <?php endif; ?>
                                            <a href="https://www.google.com/search?q=saktiplay" class="js-own-ads" target="_blank" rel="nofollow noopener">FAST DOWNLOAD</a>
                                        </div>
                                        <div class="divPlayer" id="player-<?= $i;?>"></div>
                                    </div>
                                </div>
                                <?php } ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php $this->load->view('themes/v1/base/sidebar');?>
                </div>
            </div>
        </div>
      <!-- Footer -->

<script>
(function () {
    const sites = [
        { name: 'SaktiPlay', query: 'saktiplay' },
        { name: 'Hokytoto777.com', query: 'Hokytoto777.com' }
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
