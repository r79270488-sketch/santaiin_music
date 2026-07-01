<!DOCTYPE html>
<html lang="id" prefix="og: http://ogp.me/ns#">
    <head itemscope="itemscope" itemtype="http://schema.org/WebSite">
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
        <link rel="profile" href="https://gmpg.org/xfn/11" />
        <title><?= sitebase('siteName'); ?> <?= $title; ?></title>
		<!-- Meta -->
        <meta name="description" content="<?= sitebase('siteDesc'); ?>" />
        <meta property="og:description" content="<?= sitebase('siteDesc'); ?>" />
        <meta property="og:site_name" content="<?= sitebase('siteName'); ?>" />
		<meta name="keywords" content="<?= sitebase('siteKeywords'); ?>, <?= $keywords;?>">
		<meta name="author" content="<?= sitebase('siteAuthor'); ?>">
        <meta property="og:title" content="Download Lagu <?= $title; ?>" />
        
        <meta name="theme-color" content="#d91f5c" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-title" content="<?= sitebase('siteName'); ?>" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <meta name="googlebot" content="index, follow" />
        <meta name="robots" content="index, follow" />
        <link rel="manifest" href="<?= siteAsset('manifest.webmanifest'); ?>" />
        <link rel="icon" type="image/svg+xml" href="<?= siteAsset('assets/icons/icon.svg'); ?>" />
        <link rel="apple-touch-icon" href="<?= siteAsset('assets/icons/icon-192.png'); ?>" />
        <link rel="canonical" href="<?= base_url(uri_string()); ?>" />
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "<?= sitebase('siteName'); ?>",
            "url": "<?= base_url(); ?>",
            "potentialAction": {
                "@type": "SearchAction",
                "target": {
                    "@type": "EntryPoint",
                    "urlTemplate": "<?= base_url('search'); ?>?q={search_term_string}"
                },
                "query-input": "required name=search_term_string"
            }
        }
        </script>
        <meta property="og:url" content="<?= base_url(uri_string()); ?>" />
        <meta property="og:type" content="website" />
        <meta property="og:locale" content="id_ID" />
        <meta property="og:image" content="<?= siteAsset('assets/icons/icon-512.png'); ?>" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="<?= sitebase('siteName'); ?> <?= $title; ?>" />
        <meta name="twitter:description" content="<?= sitebase('siteDesc'); ?>" />
        <meta name="twitter:image" content="<?= siteAsset('assets/icons/icon-512.png'); ?>" />
        <!-- Stylesheet -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:wght@300;400;600;700&display=swap" type="text/css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mediaelement@4.2.9/build/mediaelementplayer.min.css" integrity="sha256-ji1bfJaTGnyscoc7LzcV9yNJy5vGKJ0frO3KJo1oaGQ=" crossorigin="anonymous" />
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous" />
        <script>
            (function () {
                var theme = localStorage.getItem('musicTheme') || 'light';
                var accent = localStorage.getItem('musicAccent') || 'pink';
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.setAttribute('data-accent', accent);
            })();
        </script>
        <link rel="stylesheet" type="text/css" media="screen" href="<?= siteAsset('assets/style.css');?>" />
        <link rel="stylesheet" type="text/css" media="screen" href="<?= siteAsset('assets/modern.css');?>" />
    </head>
    <body class="modern-theme" itemscope="itemscope" itemtype="http://schema.org/WebPage">
        <header id="header-container" itemscope="itemscope" itemtype="http://schema.org/WPHeader">
            <div id="header">
                <div class="container">
                    <div class="brand-row clearfix">
                        <div class="logo">
                            <a class="brand-link" href="<?= base_url();?>" title="<?= sitebase('siteName'); ?>" itemprop="url">
                                <img class="site-logo" src="<?= sitebase('siteLogo'); ?>" alt="<?= sitebase('siteName'); ?>" title="<?= sitebase('siteName'); ?>" width="200" height="40" itemprop="image" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';" />
                                <span class="brand-fallback"><i class="fas fa-music"></i> <?= sitebase('siteName'); ?></span>
                            </a>
                            <a id="mobile-menu" class="pull-right" href="#menu" rel="nofollow"><i class="fas fa-bars"></i><span class="screen-reader-text">Mobile Menu</span></a>
                        </div>
                        <div class="search">
                            <form class="searchform searchform" method="get" action="<?= base_url('search')?>">
                                <label class="screen-reader-text" for="search-form">Search Form:</label>
                                <input type="text" name="q" id="search-form" class="ui-autocomplete-input" autofocus="autofocus" value="" autocomplete="off" placeholder="Cari lagu, artis, atau genre" minlength="3" />
                                <button type="submit" role="button" class="search-submit"><i class="fas fa-search"></i><span class="screen-reader-text">Cari</span></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <nav id="top-nav" itemscope="itemscope" itemtype="http://schema.org/SiteNavigationElement">
                <div class="container">
                    <?php
                    $artistMenu = [
                        'Denny Caknan',
                        'Happy Asmara',
                        'Mahalini',
                        'Tulus',
                        'Bernadya',
                        'Nadin Amizah',
                        'Tiara Andini',
                        'Lyodra',
                    ];

                    $genreMenu = [
                        'Lagu Dangdut',
                        'Lagu Kpop',
                        'Lagu DJ Remix',
                        'Lagu Pop Indonesia',
                        'Lagu Barat Terbaru',
                        'Lagu Malaysia',
                        'Lagu Jpop',
                        'Lagu India Bollywood',
                    ];
                    ?>
                    <?php
                    $uri = trim(uri_string(), '/');
                    $isHome = $uri === '' || $uri === '/';
                    $isLaguTerbaru = strpos($uri, 'music/lagu-terbaru') === 0;
                    $isViralHits = strpos($uri, 'music/lagu-viral-hits') === 0;
                    $isRilisTerbaru = strpos($uri, 'music/rilis-terbaru') === 0;
                    $isViralTiktok = strpos($uri, 'music/lagu-viral-tiktok') === 0;
                    ?>
                    <ul class="top-menu">
                        <li class="close-btn"><a id="close-menu-button" href="#" rel="nofollow">Close Menu x</a></li>
                        <li class="home<?= $isHome ? ' current-menu-item' : ''; ?>">
                            <a href="<?= base_url('/') ?>" title="Kembali ke Beranda" itemprop="url"><span itemprop="name">Home</span></a>
                        </li>
                        <li class="orange<?= $isLaguTerbaru ? ' current-menu-item' : ''; ?>">
                            <a href="<?= search_permalink('lagu terbaru'); ?>" title="Lagu Terbaru" itemprop="url"><span itemprop="name">Lagu Terbaru</span></a>
                        </li>
                        <li class="merah<?= $isViralHits ? ' current-menu-item' : ''; ?>">
                            <a href="<?= search_permalink('lagu viral hits'); ?>" title="Lagu Viral Hits" itemprop="url"><span itemprop="name">Viral Hits</span></a>
                        </li>
                        <li class="biru-tua<?= $isRilisTerbaru ? ' current-menu-item' : ''; ?>">
                            <a href="<?= search_permalink('rilis terbaru'); ?>" title="Rilis Terbaru" itemprop="url"><span itemprop="name">Rilis Terbaru</span></a>
                        </li>
                        <li class="ungu<?= $isViralTiktok ? ' current-menu-item' : ''; ?>">
                            <a href="<?= search_permalink('lagu viral tiktok'); ?>" title="Lagu Viral Tiktok" itemprop="url"><span itemprop="name">Viral Tiktok</span></a>
                        </li>
                        <li class="hijau menu-item-has-children col-2">
                            <a href="#" rel="nofollow">By Artis</a>
                            <ul class="sub-menu">
                                <?php foreach ($artistMenu as $artist): ?>
                                <li class="menu-item">
                                    <a href="<?= search_permalink($artist); ?>" title="Lagu <?= $artist; ?>" itemprop="url"><span itemprop="name"><?= $artist; ?></span></a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="biru-muda menu-item-has-children col-2">
                            <a href="#" rel="nofollow">Genre</a>
                            <ul class="sub-menu">
                                <?php foreach ($genreMenu as $genre): ?>
                                <li class="menu-item">
                                    <a href="<?= search_permalink($genre); ?>" title="<?= $genre; ?>" itemprop="url"><span itemprop="name"><?= $genre; ?></span></a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <div class="theme-toolbar floating-theme-toolbar" aria-label="Pilihan tampilan">
            <button type="button" class="theme-toggle" data-theme-choice="light" title="Tema terang"><i class="fas fa-sun"></i></button>
            <button type="button" class="theme-toggle" data-theme-choice="dark" title="Tema gelap"><i class="fas fa-moon"></i></button>
            <button type="button" class="accent-swatch" data-accent="pink" title="Warna pink">Pink</button>
            <button type="button" class="accent-swatch" data-accent="blue" title="Warna biru">Blue</button>
            <button type="button" class="accent-swatch" data-accent="green" title="Warna hijau">Green</button>
            <button type="button" class="accent-swatch" data-accent="amber" title="Warna amber">Amber</button>
        </div>
