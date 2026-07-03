<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach($urls as $url): ?>
<url>
<loc><?= html_escape($url['loc']); ?></loc>
<?php if (!empty($url['lastmod'])): ?>
<lastmod><?= html_escape(substr($url['lastmod'], 0, 10)); ?></lastmod>
<?php endif; ?>
</url>
<?php endforeach; ?>
</urlset>
