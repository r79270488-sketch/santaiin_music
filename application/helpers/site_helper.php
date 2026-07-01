<?php
function _httpGet($url, $timeout = 15)
{
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($result !== false && $result !== '' && $httpCode >= 200 && $httpCode < 300) {
            return $result;
        }
    }

    $ctx = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => $timeout,
            'header' => "User-Agent: Mozilla/5.0\r\nAccept: application/json\r\n"
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]);
    $result = @file_get_contents($url, false, $ctx);
    return $result !== false ? $result : '';
}

function getConfig(){
	static $config = null;
	if ($config !== null) return $config;
	$config = json_decode(file_get_contents(FCPATH.'config/site.json'));
	return $config;
}

function siteLogMessage($level, $message)
{
    if (function_exists('log_message')) {
        log_message($level, $message);
        return;
    }

    error_log(strtoupper($level) . ': ' . $message);
}


function siteBase($key){
	
	$json = getConfig();
	// Cek Permintaan Parameter
	
	if($key == 'siteName'){
		return $json->siteName;
	}elseif($key == 'siteDesc'){
		return $json->siteDesc;
	}elseif($key == 'siteAuthor'){
		return $json->siteAuthor;
	}elseif($key == 'siteKeywords'){
		return $json->siteKeywords;
	}elseif($key == 'siteLogo'){
		return $json->siteLogo;
	}elseif($key == 'Ads1'){
		return $json->Ads1;
	}elseif($key == 'Ads2'){
		return $json->Ads2;
	}elseif($key == 'Ads3'){
		return $json->Ads3;
	}elseif($key == 'AdsPopup'){
		return $json->AdsPopup;
	}elseif($key == 'textDesc'){
		$query = $json->textDesc;
		$text = str_replace( '%site_name%',$json->siteName,  $query );
		return $text;
	}
	
	return $json;
}

function siteAd($key, $class = '')
{
    $ad = siteBase($key);

    if (empty($ad)) {
        return '';
    }

    $class = trim('ad-slot ' . $class);

    return '<div class="' . html_escape($class) . '" aria-label="Advertisement">'
        . '<span class="ad-label">Advertisement</span>'
        . '<div class="ad-content">' . $ad . '</div>'
        . '</div>';
}

function siteAsset($path)
{
    $path = ltrim($path, '/');
    $version = file_exists(FCPATH . $path) ? filemtime(FCPATH . $path) : time();
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '/index.php';
    $basePath = trim(str_replace('\\', '/', dirname($scriptName)), '/');
    $prefix = $basePath === '' ? '' : '/' . $basePath;

    return $prefix . '/' . $path . '?v=' . $version;
}

function search_permalink($str){
    return base_url('music/'.url_title($str));
}

function single_permalink($id,$judul){
    return base_url('detail/'.url_title($id).'-'.url_title($judul));
}

function cleanSongTitleForLyrics($title)
{
    $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
    $title = preg_replace('/\s*\[[^\]]+\]\s*/', ' ', $title);
    $title = preg_replace('/\s*\([^\)]*(official|lyrics?|audio|video|mv|music|visualizer)[^\)]*\)\s*/i', ' ', $title);
    $title = preg_replace('/\b(official music video|official video|official audio|lyrics?|music video|visualizer)\b/i', ' ', $title);
    $title = preg_replace('/\s+/', ' ', $title);

    return trim($title, " \t\n\r\0\x0B-_|");
}

function getSongLyrics($title)
{
    $query = cleanSongTitleForLyrics($title);

    if ($query === '') {
        return '';
    }

    $cacheFile = FCPATH . 'application/cache/lyrics_' . md5(strtolower($query)) . '.txt';
    $cacheTime = 7 * 24 * 60 * 60;

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        return trim(file_get_contents($cacheFile));
    }

    $url = 'https://lrclib.net/api/search?' . http_build_query(['q' => $query]);
    $json = _httpGet($url, 10);

    if (!$json) {
        return '';
    }

    $items = json_decode($json, true);

    if (empty($items) || !is_array($items)) {
        return '';
    }

    foreach ($items as $item) {
        $lyrics = $item['plainLyrics'] ?? '';

        if ($lyrics === '' && !empty($item['syncedLyrics'])) {
            $lyrics = preg_replace('/^\[[0-9:.]+\]\s*/m', '', $item['syncedLyrics']);
        }

        $lyrics = trim($lyrics);

        if ($lyrics !== '') {
            file_put_contents($cacheFile, $lyrics);
            return $lyrics;
        }
    }

    return '';
}

function get_title($str){
    return  str_replace('-',' ', $str);
}

function getYoutubeTopSong($limit='20')
{
		$getApiYoutube = get_apikey_youtube();

		$trending = 'https://youtube.googleapis.com/youtube/v3/videos?part=snippet&part=contentDetails&chart=mostPopular&maxResults='.$limit.'&videoCategoryId=10&type=video&key='.$getApiYoutube;

		$json =  _httpGet($trending, 15);
        $arr = json_decode($json,true);
        $i = 0 ;
        if(isset($arr['items'])){
            foreach ($arr['items'] as $item) {
                $results[$i] = [
                    'id' => $item['id'],
                    'judul' => $item['snippet']['title'],
                    'description' => $item['snippet']['description'],
                    'thumbnails' => $item['snippet']['thumbnails']['high']['url'],
                    'uploader' => $item['snippet']['channelTitle'],
                ];
                $i++;
            }
		}

		return $results;
}

function getYoutubePopularMusic($limit = 12, $region = 'ID')
{
    $limit = (int) $limit;

    if ($limit < 1) {
        $limit = 12;
    }

    if ($limit > 25) {
        $limit = 25;
    }

    $region = strtoupper($region);

    $cacheFile = FCPATH . 'application/cache/youtube_popular_music_' . $region . '_' . $limit . '.json';
    $cacheTime = 3 * 60 * 60;

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        $cache = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cache)) {
            return $cache;
        }
    }

    $apiKey = get_apikey_youtube();

    if (empty($apiKey)) {
        return [];
    }

    $publishedAfter = gmdate('Y-m-d\TH:i:s\Z', strtotime('-90 days'));

    $url = 'https://youtube.googleapis.com/youtube/v3/search?' . http_build_query([
        'part' => 'snippet',
        'maxResults' => $limit,
        'order' => 'viewCount',
        'type' => 'video',
        'videoCategoryId' => '10',
        'regionCode' => $region,
        'publishedAfter' => $publishedAfter,
        'q' => 'lagu indonesia music official audio official video',
        'key' => $apiKey
    ]);

    $json = _httpGet($url, 15);

    if (!$json) {
        return [];
    }

    $data = json_decode($json, true);

    if (isset($data['error']) || empty($data['items'])) {
        log_message('error', 'YouTube popular music error: ' . $json);
        return [];
    }

    $results = [];

    foreach ($data['items'] as $item) {
        if (empty($item['id']['videoId'])) {
            continue;
        }

        $videoId = $item['id']['videoId'];
        $title = $item['snippet']['title'] ?? '';

        $results[] = [
            'id' => $videoId,
            'judul' => $title,
            'artist' => $item['snippet']['channelTitle'] ?? '',
            'description' => $item['snippet']['description'] ?? '',
            'thumbnails' => $item['snippet']['thumbnails']['medium']['url']
                ?? $item['snippet']['thumbnails']['high']['url']
                ?? $item['snippet']['thumbnails']['default']['url']
                ?? '',
            'uploader' => $item['snippet']['channelTitle'] ?? '',
            'url_detail' => single_permalink($videoId, $title),
        ];
    }

    if (!empty($results)) {
        file_put_contents($cacheFile, json_encode($results));
    }

    return $results;
}

function getAppleNewReleases($country = 'id', $limit = 12)
{
    $country = strtolower($country);
    $limit = (int) $limit;

    if ($limit < 1) {
        $limit = 12;
    }

    if ($limit > 50) {
        $limit = 50;
    }

    $cacheFile = FCPATH . 'application/cache/apple_new_releases_' . $country . '_' . $limit . '.json';
    $cacheTime = 6 * 60 * 60;

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        $cache = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cache)) {
            return $cache;
        }
    }

    $urls = [
        "https://rss.applemarketingtools.com/api/v2/{$country}/music/new-releases/{$limit}/albums.json",
        "https://rss.applemarketingtools.com/api/v2/{$country}/music/new-music/{$limit}/albums.json",
        "https://rss.applemarketingtools.com/api/v2/{$country}/music/most-played/{$limit}/songs.json",
    ];

    $arr = null;

    foreach ($urls as $url) {
    $json = _httpGet($url, 15);

        if (!$json) {
            continue;
        }

        $tmp = json_decode($json, true);

        if (!empty($tmp['feed']['results'])) {
            $arr = $tmp;
            break;
        }
    }

    if (empty($arr['feed']['results'])) {
        return [];
    }

    $results = [];

    foreach ($arr['feed']['results'] as $item) {
        $artistName = $item['artistName'] ?? '';
        $name = $item['name'] ?? '';

        $thumb = $item['artworkUrl100'] ?? '';
        $thumb = str_replace('100x100', '300x300', $thumb);

        $results[] = [
            'artistName' => $artistName,
            'name' => $name,
            'songName' => trim($artistName . ' - ' . $name, ' -'),
            'releaseDate' => $item['releaseDate'] ?? '',
            'thumbNail' => $thumb,
            'url' => $item['url'] ?? '',
        ];
    }

    if (!empty($results)) {
        file_put_contents($cacheFile, json_encode($results));
    }

    return $results;
}

function buildYoutubeMusicQuery($query)
{
    $clean = trim(preg_replace('/\s+/', ' ', strtolower($query)));

    $map = [
        'rilis terbaru' => 'lagu terbaru official music video indonesia',
        'lagu terbaru' => 'lagu terbaru official music video indonesia',
        'lagu viral hits' => 'lagu viral hits official music video',
        'lagu viral tiktok' => 'lagu viral tiktok official music video',
        'viral hits' => 'lagu viral hits official music video',
        'viral tiktok' => 'lagu viral tiktok official music video',
    ];

    if (isset($map[$clean])) {
        return $map[$clean];
    }

    if (strpos($clean, 'lagu') === false && strpos($clean, 'music') === false && strpos($clean, 'song') === false) {
        return $query . ' lagu official music video';
    }

    return $query . ' official music video';
}

function youtubeSearchOrder($query)
{
    $query = strtolower($query);

    if (
        strpos($query, 'terbaru') !== false ||
        strpos($query, 'rilis') !== false ||
        strpos($query, 'viral') !== false ||
        strpos($query, 'hits') !== false
    ) {
        return 'date';
    }

    return 'relevance';
}

function youtubeDurationToSeconds($duration)
{
    if (!preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/', $duration, $matches)) {
        return 0;
    }

    $hours = isset($matches[1]) && $matches[1] !== '' ? (int) $matches[1] : 0;
    $minutes = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : 0;
    $seconds = isset($matches[3]) && $matches[3] !== '' ? (int) $matches[3] : 0;

    return ($hours * 3600) + ($minutes * 60) + $seconds;
}

function getYoutubeVideoDetails($videoIds)
{
    $videoIds = array_filter(array_unique($videoIds));

    if (empty($videoIds)) {
        return [];
    }

    $url = 'https://youtube.googleapis.com/youtube/v3/videos?' . http_build_query([
        'part' => 'snippet,contentDetails',
        'id' => join(',', $videoIds),
        'key' => get_apikey_youtube(),
    ]);

    $json = _httpGet($url, 15);

    if (!$json) {
        return [];
    }

    $data = json_decode($json, true);

    if (empty($data['items'])) {
        return [];
    }

    $details = [];

    foreach ($data['items'] as $item) {
        $id = $item['id'] ?? '';

        if ($id === '') {
            continue;
        }

        $details[$id] = [
            'duration' => youtubeDurationToSeconds($item['contentDetails']['duration'] ?? ''),
            'categoryId' => $item['snippet']['categoryId'] ?? '',
        ];
    }

    return $details;
}

function isSingleMusicVideo($title, $duration, $categoryId)
{
    $title = strtolower($title);

    if ($categoryId !== '' && $categoryId !== '10') {
        return false;
    }

    if ($duration > 0 && ($duration < 60 || $duration > 600)) {
        return false;
    }

    $blocked = [
        'playlist',
        'full album',
        'album full',
        '#shorts',
        'shorts',
        'youtube short',
        'ytshorts',
        'nonstop',
        'non stop',
        'kompilasi',
        'kumpulan lagu',
        'lagu santai',
        'medley',
        'mixtape',
        'best of',
        'top 100',
        'top hits',
        'lagu anak',
        'lagu anak-anak',
        'anak anak',
        'anak-anak',
        'lagu balita',
        'balita',
        'lagu bayi',
        'nursery rhyme',
        'kids song',
        'kids songs',
        'cocomelon',
        'babybus',
        'baby shark',
        'ayo sikat gigi',
        'sikat gigi',
        '1 jam',
        '2 jam',
        '3 jam',
        'hour',
        'hours',
        'live streaming',
        'unboxing',
        'review',
        'spesifikasi',
        'harga',
        'smartphone',
        'iphone',
        'samsung',
        'xiaomi',
        'redmi',
        'oppo',
        'vivo',
        'realme',
        'infinix',
        'tecno',
        'itel',
        'baterai',
        'mah',
        'ram ',
        'gb/',
        'hp ',
        ' hp',
    ];

    foreach ($blocked as $word) {
        if (strpos($title, $word) !== false) {
            return false;
        }
    }

    return true;
}

function getYoutubeSearch($query){
    $apiKey = get_apikey_youtube();

    if (empty($apiKey)) {
        return [];
    }

    $params = [
        'part' => 'snippet',
        'maxResults' => 50,
        'order' => youtubeSearchOrder($query),
        'type' => 'video',
        'videoCategoryId' => '10',
        'videoEmbeddable' => 'true',
        'regionCode' => 'ID',
        'q' => buildYoutubeMusicQuery($query),
        'key' => $apiKey,
    ];

    if (youtubeSearchOrder($query) === 'date') {
        $params['publishedAfter'] = gmdate('Y-m-d\TH:i:s\Z', strtotime('-365 days'));
    }

	$url = 'https://youtube.googleapis.com/youtube/v3/search?' . http_build_query($params);

    $json = _httpGet($url, 15);

    if (!$json) {
        return [];
    }

	$data = json_decode($json,true);

    if (isset($data['error']) || empty($data['items'])) {
        siteLogMessage('error', 'YouTube search music error: ' . $json);
        return [];
    }

    $videoIds = [];

    foreach ($data['items'] as $item) {
        if (!empty($item['id']['videoId'])) {
            $videoIds[] = $item['id']['videoId'];
        }
    }

    $details = getYoutubeVideoDetails($videoIds);
	$results = [];

	foreach ($data['items'] as $item) {
        if (empty($item['id']['videoId'])) {
            continue;
        }

        $videoId = $item['id']['videoId'];
        $title = $item['snippet']['title'] ?? '';
        $duration = $details[$videoId]['duration'] ?? 0;
        $categoryId = $details[$videoId]['categoryId'] ?? '';

        if (!isSingleMusicVideo($title, $duration, $categoryId)) {
            continue;
        }

        $results[] = [
            'id' => $videoId,
            'judul' => $title,
            'description' => $item['snippet']['description'] ?? '',
            'thumbnails' => $item['snippet']['thumbnails']['high']['url']
                ?? $item['snippet']['thumbnails']['medium']['url']
                ?? $item['snippet']['thumbnails']['default']['url']
                ?? '',
            'uploader' => $item['snippet']['channelTitle'] ?? '',
            'duration' => $duration,
        ];

        if (count($results) >= 20) {
            break;
        }
	}

	return $results;
}

function getItunesPlaylist($country = 'id', $limit = 20)
{
    $country = strtolower($country);
    $limit = (int) $limit;

    if ($limit < 1) {
        $limit = 10;
    }

    if ($limit > 100) {
        $limit = 100;
    }

    // Endpoint Apple RSS baru
    $url = "https://rss.applemarketingtools.com/api/v2/{$country}/music/most-played/{$limit}/songs.json";
    $json = _httpGet($url, 15);

    if (!$json) {
        log_message('error', 'Apple RSS gagal dibuka: ' . $url);
        return [];
    }

    $arr = json_decode($json, true);

    if (isset($arr['error'])) {
        log_message('error', 'Apple RSS error: ' . json_encode($arr['error']));
        return [];
    }

    if (empty($arr['feed']['results']) || !is_array($arr['feed']['results'])) {
        log_message('error', 'Apple RSS results kosong: ' . $json);
        return [];
    }

    $results = [];

    foreach ($arr['feed']['results'] as $item) {
        $artistName = $item['artistName'] ?? '';
        $songName   = $item['name'] ?? '';

        $results[] = [
            'artistName'  => $artistName,
            'releaseDate' => $item['releaseDate'] ?? '',
            'name'        => $songName,
            'thumbNail'   => $item['artworkUrl100'] ?? '',
            'songName'    => trim($artistName . ' - ' . $songName, ' -'),
            'url'         => $item['url'] ?? '',
        ];
    }

    return $results;
}

function getItunesSearch($query, $country = 'id', $limit = 20)
{
    $url = 'https://itunes.apple.com/search?' . http_build_query([
        'term'    => $query,
        'country' => $country,
        'media'   => 'music',
        'entity'  => 'song',
        'limit'   => $limit,
        'explicit'=> 'Yes'
    ]);

    $json = _httpGet($url, 10);

    if (!$json) {
        return [];
    }

    $arr = json_decode($json, true);

    if (!isset($arr['results'])) {
        return [];
    }

    $results = [];

    foreach ($arr['results'] as $item) {
        $results[] = [
            'artistName'  => isset($item['artistName']) ? $item['artistName'] : '',
            'songName'    => isset($item['trackName']) ? $item['trackName'] : '',
            'collection'  => isset($item['collectionName']) ? $item['collectionName'] : '',
            'releaseDate' => isset($item['releaseDate']) ? substr($item['releaseDate'], 0, 10) : '',
            'thumbNail'   => isset($item['artworkUrl100']) ? $item['artworkUrl100'] : '',
            'previewUrl'  => isset($item['previewUrl']) ? $item['previewUrl'] : '',
            'trackViewUrl'=> isset($item['trackViewUrl']) ? $item['trackViewUrl'] : '',
            'fullTitle'   => (isset($item['artistName']) ? $item['artistName'] : '') . ' - ' . (isset($item['trackName']) ? $item['trackName'] : ''),
        ];
    }

    return $results;
}

function getQuickPicksFromItunesYoutube($country = 'id', $limit = 12)
{
    $cacheFile = FCPATH . 'application/cache/quick_picks_' . $country . '_' . $limit . '.json';
    $cacheTime = 6 * 60 * 60; // 6 jam

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        $cache = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cache)) {
            return $cache;
        }
    }

    $itunesUrl = "https://rss.itunes.apple.com/api/v1/" . $country . "/itunes-music/top-songs/all/" . $limit . "/explicit.json";
    $itunesJson = _httpGet($itunesUrl, 15);

    if (!$itunesJson) {
        return [];
    }

    $itunesData = json_decode($itunesJson, true);

    if (empty($itunesData['feed']['results'])) {
        return [];
    }

    $results = [];

    foreach ($itunesData['feed']['results'] as $song) {
        $artist = $song['artistName'] ?? '';
        $title  = $song['name'] ?? '';

        if (empty($artist) || empty($title)) {
            continue;
        }

        $query = $artist . ' - ' . $title . ' official audio';

        $youtube = searchYoutubeOneVideo($query);

        $videoId = $youtube['id'] ?? '';
        $youtubeThumb = $youtube['thumbnails'] ?? '';

        $results[] = [
            'id' => $videoId,
            'judul' => $artist . ' - ' . $title,
            'title' => $title,
            'artist' => $artist,
            'description' => $youtube['description'] ?? '',
            'thumbnails' => !empty($youtubeThumb)
                ? $youtubeThumb
                : ($song['artworkUrl100'] ?? ''),
            'uploader' => $youtube['uploader'] ?? $artist,
            'releaseDate' => $song['releaseDate'] ?? '',
            'url_detail' => !empty($videoId)
                ? single_permalink($videoId, $artist . ' - ' . $title)
                : search_permalink($artist . ' - ' . $title),
        ];
    }

    if (!empty($results)) {
        file_put_contents($cacheFile, json_encode($results));
    }

    return $results;
}

function searchYoutubeOneVideo($query)
{
    $apiKey = get_apikey_youtube();

    if (empty($apiKey)) {
        return [];
    }

    $url = 'https://youtube.googleapis.com/youtube/v3/search?' . http_build_query([
        'part' => 'snippet',
        'maxResults' => 1,
        'order' => 'relevance',
        'type' => 'video',
        'videoCategoryId' => '10',
        'regionCode' => 'ID',
        'q' => $query,
        'key' => $apiKey
    ]);

    $json = _httpGet($url, 15);

    if (!$json) {
        return [];
    }

    $data = json_decode($json, true);

    if (isset($data['error']) || empty($data['items'][0])) {
        return [];
    }

    $item = $data['items'][0];

    return [
        'id' => $item['id']['videoId'] ?? '',
        'judul' => $item['snippet']['title'] ?? '',
        'description' => $item['snippet']['description'] ?? '',
        'thumbnails' => $item['snippet']['thumbnails']['medium']['url']
            ?? $item['snippet']['thumbnails']['high']['url']
            ?? $item['snippet']['thumbnails']['default']['url']
            ?? '',
        'uploader' => $item['snippet']['channelTitle'] ?? '',
    ];
}

function autoSearchSitemap($str){
	$search = $str;
	$maxhistory = 25;
	$lastsearchfile = FCPATH.'keywoard/sitemap.txt';
	$lastsearch = file($lastsearchfile,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if($search) $history[] = $search;
		if(is_array($lastsearch)) {
			$i=count($history);
			foreach($lastsearch as $k => $v) {
				if($v != $search && $i < $maxhistory) {
					$history[] = $v;
					$i++;
				}
			}
	}
	if($history) {
		file_put_contents($lastsearchfile, join("\n", $history));
	}
	return $history;
}

function keywordRefreshDays()
{
	$config = getConfig();

	if (isset($config->keywordRefreshDays)) {
		$days = (int) $config->keywordRefreshDays;

		if ($days > 0) {
			return $days;
		}
	}

	return 3;
}

function keywordFileNeedsRefresh($file, $days)
{
	if (!file_exists($file)) {
		return true;
	}

	return (time() - filemtime($file)) >= ($days * 24 * 60 * 60);
}

function cleanAutoKeyword($keyword)
{
	$keyword = html_entity_decode($keyword, ENT_QUOTES, 'UTF-8');
	$keyword = strip_tags($keyword);
	$keyword = preg_replace('/\s*\[[^\]]+\]\s*/', ' ', $keyword);
	$keyword = preg_replace('/\s*\([^\)]*(official|lyrics?|audio|video|mv|music)[^\)]*\)\s*/i', ' ', $keyword);
	$keyword = preg_replace('/\b(official music video|official video|official audio|lyrics?|music video|visualizer)\b/i', ' ', $keyword);
	$keyword = preg_replace('/\s+/', ' ', $keyword);

	return trim($keyword, " \t\n\r\0\x0B-_|");
}

function collectAutoKeywords()
{
	$keywords = [];

	try {
		foreach (getAppleNewReleases('id', 50) as $item) {
			if (!empty($item['songName'])) {
				$keywords[] = $item['songName'];
			} elseif (!empty($item['name'])) {
				$keywords[] = $item['name'];
			}
		}
	} catch (Throwable $e) {
		siteLogMessage('error', 'Auto keyword Apple error: ' . $e->getMessage());
	}

	try {
		foreach (getYoutubePopularMusic(25, 'ID') as $item) {
			if (!empty($item['judul'])) {
				$keywords[] = $item['judul'];
			}
		}
	} catch (Throwable $e) {
		siteLogMessage('error', 'Auto keyword YouTube error: ' . $e->getMessage());
	}

	$clean = [];

	foreach ($keywords as $keyword) {
		$keyword = cleanAutoKeyword($keyword);

		if ($keyword === '' || strlen($keyword) < 3) {
			continue;
		}

		$key = strtolower($keyword);

		if (!isset($clean[$key])) {
			$clean[$key] = $keyword;
		}
	}

	return array_values($clean);
}

function refreshKeywordFile($file, $newKeywords, $limit = 80)
{
	$current = [];

	if (file_exists($file)) {
		$current = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	}

	$merged = [];

	foreach (array_merge($newKeywords, $current) as $keyword) {
		$keyword = cleanAutoKeyword($keyword);

		if ($keyword === '' || strlen($keyword) < 3) {
			continue;
		}

		$key = strtolower($keyword);

		if (!isset($merged[$key])) {
			$merged[$key] = $keyword;
		}

		if (count($merged) >= $limit) {
			break;
		}
	}

	if (!empty($merged)) {
		file_put_contents($file, join("\n", array_values($merged)));
	}
}

function refreshKeywordFilesIfNeeded()
{
	static $checked = false;

	if ($checked) {
		return;
	}

	$checked = true;
	$days = keywordRefreshDays();
	$kwFile = FCPATH.'keywoard/kw1.txt';
	$sitemapFile = FCPATH.'keywoard/sitemap.txt';

	if (
		!keywordFileNeedsRefresh($kwFile, $days) &&
		!keywordFileNeedsRefresh($sitemapFile, $days)
	) {
		return;
	}

	$keywords = collectAutoKeywords();

	if (empty($keywords)) {
		return;
	}

	if (keywordFileNeedsRefresh($kwFile, $days)) {
		refreshKeywordFile($kwFile, $keywords, 100);
	}

	if (keywordFileNeedsRefresh($sitemapFile, $days)) {
		refreshKeywordFile($sitemapFile, $keywords, 50);
	}
}


function get_kw()
{
	refreshKeywordFilesIfNeeded();

	$kw1 = file(FCPATH.'keywoard/kw1.txt',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	shuffle($kw1);
	$kw1 = array_slice($kw1,0,20);
	return $kw1;
}

function getSitemap($limit)
{
	refreshKeywordFilesIfNeeded();

	$sitemap = file(FCPATH.'keywoard/sitemap.txt',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	shuffle($sitemap);
	$sitemap = array_slice($sitemap,0,$limit);
	return $sitemap;
}
	
function spin($string){
	$spintax = new Spintax();
	return $spintax->process($string);
}

function get_apikey_youtube()
{
    $config = getConfig();

    if (!isset($config->ApiYoutube) || empty($config->ApiYoutube)) {
        return '';
    }

    $api_keys = explode(',', $config->ApiYoutube);

    $api_keys = array_map('trim', $api_keys);
    $api_keys = array_filter($api_keys);

    if (empty($api_keys)) {
        return '';
    }

    shuffle($api_keys);

    return $api_keys[0];
}
