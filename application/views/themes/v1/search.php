<!-- Header -->
        <div id="site-container">
            <div class="container">
                <div class="row" itemscope="itemscope" itemtype="http://schema.org/MusicAlbum">
                    <div class="col-md-main">
                        <h1 class="main-title" itemprop="headline"><span class="highlight">Download Lagu MP3</span> <?= $title_parameter;?></h1>
                        <div class="success" itemprop="text">
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
                                <div class="clearfix search-content" itemprop="itemListElement" itemscope="itemscope" itemtype="http://schema.org/ListItem">
                                    <span itemprop="position" content="<?= $i; ?>"></span>
                                    <div class="content-left pull-left">
                                        <img src="<?= $item['thumbnails'];?>" alt="<?= $item['judul'];?>" width="120" height="90" loading="lazy" itemprop="image" />
                                    </div>
                                    <div class="content-right" itemprop="item" itemscope="itemscope" itemtype="http://schema.org/MusicRecording">
                                        <h2 class="content-title">
                                            <a href="<?= single_permalink($item['id'],$item['judul']);?>" title="<?= $item['judul'];?> mp3" itemprop="url" rel="nofollow">
                                                <span itemprop="name"><?= $item['judul'];?></span>
                                            </a>
                                        </h2>
                                        <div class="button">
                                            <a href="javascript:void(0);" onclick="playAudio(<?= $i;?>,'<?= $item['id'];?>');" title="Dengarkan lagu Maroon 5 - Memories (Official Video)" rel="nofollow">Play</a>
                                            <a title="<?= $item['judul'];?>" href="<?= base_url('download')?>?id=<?=$item['id'];?>&title=<?= $item['judul'];?>" rel="nofollow">Download</a>
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
