<!-- Header -->
        <div id="site-container">
            <div class="container">
                <div class="info intro-copy">Silahkan anda mendownload lagu secara gratis disini hanya untuk review. Jika anda menyukainya, silahkan beli lagu yang asli</div>
                <?= siteAd('Ads2', 'ad-slot-top');?>
                <div class="row" itemscope="itemscope" itemtype="http://schema.org/CreativeWork">
                    <h1 class="screen-reader-text" itemprop="headline">Download Lagu Gratis</h1>
                    <div class="col-md-main">
                        <h2 class="main-title"><span class="highlight">Lagu</span> Terpopuler</h2>
                        <div class="success intro-copy" itemprop="text">
                            <?= siteBase('textDesc');?>
                        </div>
                        <?php
                            foreach ($trending as $data):
                                
                        ?>
                        <div class="clearfix list-content track-row">
                            <div class="content-left pull-left">
                                <a href="<?= single_permalink($data['id'],$data['judul']);?>" title="Download Lagu <?=$data['judul']; ?>" itemprop="url">
                                    <img
                                        src="<?= html_escape(smallMusicThumbnail($data['thumbnails'])); ?>"
                                        alt="<?=$data['judul']; ?>"
                                        width="60"
                                        height="60"
                                        loading="lazy"
                                        itemprop="image"
                                    />
                                </a>
                            </div>
                            <div class="content-right">
                                <h2 class="content-title" itemprop="headline">
                                    <a href="<?= single_permalink($data['id'],$data['judul']);?>" title="Download Lagu <?=$data['judul']; ?>" itemprop="url"> <?=$data['judul']; ?> </a>
                                </h2>
                                <div class="meta-content">
                                    <span class="separator">
                                        <a href="<?= base_url('search')?>?q=<?=$data['uploader']; ?>" title="Download Lagu <?=$data['uploader']; ?>" itemprop="url"> <?=$data['uploader']; ?> </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach;?>
                       
                        <?= siteAd('Ads3', 'ad-slot-inline');?>

                        <h2 class="main-title"><span class="highlight">Rilis</span> Terbaru</h2>
                        <div class="success intro-copy" itemprop="text">
                        <?= siteBase('textDesc');?>
                        </div>
                        <?php
                            foreach ($indonesia as $data):
                                
                        ?>
                        <div class="clearfix list-content track-row">
                            <div class="clearfix">
                                <div class="content-left pull-left">
                                <a href="<?= search_permalink($data['songName']);?>" title="Download Lagu <?=$data['songName']; ?>" itemprop="url">
                                    <img
                                        src="<?= html_escape(smallMusicThumbnail($data['thumbNail'])); ?>"
                                        alt="<?=$data['songName']; ?>"
                                        width="60"
                                        height="60"
                                        loading="lazy"
                                        itemprop="image"
                                    />
                                </a>
                                </div>
                                <div class="content-right">
                                <h2 class="content-title" itemprop="headline">
                                    <a href="<?= search_permalink($data['songName']);?>" title="Download Lagu <?=$data['songName']; ?>" itemprop="url"> <?=$data['songName']; ?> </a>
                                </h2>
                                <div class="meta-content">
                                    <span class="separator">
                                        <a href="<?= base_url('search')?>?q=<?=$data['artistName']; ?>" title="Download Lagu <?=$data['artistName']; ?>" itemprop="url"> <?=$data['artistName']; ?> </a>
                                    </span>
                                </div>
                            </div>
                            </div>
                        </div>
                        <?php endforeach;?>
                        

                        <!-- Inject AGK -->
                    </div>
                    
                    <?php $this->load->view('themes/v1/base/sidebar');?>
                </div>
            </div>
            <?= siteAd('Ads3', 'ad-slot-bottom');?>
        </div>
        
