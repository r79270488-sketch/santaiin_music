        </main>
<div id="footer-container">
            <div class="container">
                <p class="copy">
                    &copy; <?= date('Y');?> <?= sitebase('siteName'); ?>. All rights reserved.
                </p>
                <ul class="footer-menu">
                    <li>
                        <a href="<?= base_url();?>page/disclaimer" title="Disclaimer">Disclaimer</a>
                    </li>
                    <li>
                        <a href="<?= base_url();?>page/copyright" title="Copyright">Copyright</a>
                    </li>
                    <li>
                        <a href="<?= base_url();?>page/term-of-services" title="Term Of Services">TOS</a>
                    </li>
                </ul>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/sidr@2.2.1/dist/jquery.sidr.min.js" integrity="sha256-/VeucihXSoNSfLiRfsWg/5RKp4eTTuW4Wnl28lm3rjE=" crossorigin="anonymous"></script>
        <script type="text/javascript">
            function renderAudioPlayer(id, audioUrl, title) {
                $(".divPlayer").html("");
                var safeTitle = $("<div>").text(title || "Preview audio").html();
                var safeUrl = $("<div>").text(audioUrl).html();
                var html = '<div class="audio-preview">' +
                    '<div class="audio-preview-title">' + safeTitle + '</div>' +
                    '<audio controls autoplay preload="none" src="' + safeUrl + '"></audio>' +
                    '</div>';
                $("#player-" + id).html(html);
            }

            function renderAudioMessage(id, message, loading) {
                $(".divPlayer").html("");
                $("#player-" + id).html(
                    '<div class="audio-preview audio-preview-empty">' +
                    '<i class="fas ' + (loading ? 'fa-spinner fa-spin' : 'fa-info-circle') + '"></i> ' +
                    $("<div>").text(message).html() +
                    '</div>'
                );
            }

            function playAudio(id, audioUrl, title) {
                if (audioUrl) {
                    renderAudioPlayer(id, audioUrl, title);
                    return;
                }

                renderAudioMessage(id, "Mencari preview audio...", true);

                fetch("<?= base_url('audio/preview'); ?>?q=" + encodeURIComponent(title || ""), {
                    headers: { "Accept": "application/json" }
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        if (data && data.ok && data.previewUrl) {
                            renderAudioPlayer(id, data.previewUrl, title);
                            return;
                        }

                        renderAudioMessage(id, "Preview audio belum tersedia. Gunakan tombol Download untuk membuka converter.", false);
                    })
                    .catch(function () {
                        renderAudioMessage(id, "Preview audio gagal dimuat. Coba lagi nanti.", false);
                    });
            }
            if ("serviceWorker" in navigator) {
                window.addEventListener("load", function () {
                    var scriptUrl = "<?= base_url('sw.js'); ?>";
                    var registerWorker = function () {
                        return navigator.serviceWorker.register(scriptUrl, { scope: "/" });
                    };

                    navigator.serviceWorker.getRegistration("/")
                        .then(function (registration) {
                            if (registration && registration.active && registration.active.scriptURL.indexOf("?v=") !== -1) {
                                return registration.unregister().then(registerWorker);
                            }

                            return registerWorker();
                        })
                        .catch(function () {
                            return null;
                        });
                });
            }
            var $ = jQuery.noConflict();
            (function ($) {
                "use strict";
                jQuery(function ($) {
                    function alignClosedMobileMenu() {
                        var status = $.sidr("status");

                        if (status.opened) {
                            return;
                        }

                        var $menu = $("#menu");

                        if (!$menu.length) {
                            return;
                        }

                        var width = $menu.outerWidth();

                        if ($menu.hasClass("right")) {
                            $menu.css("right", -width);
                            return;
                        }

                        $menu.css("left", -width);
                    }
                    $("#mobile-menu").sidr({
                        name: "menu",
                        source: "#top-nav",
                        displace: false,
                        onCloseEnd: alignClosedMobileMenu
                    });
                    alignClosedMobileMenu();
                    window.setTimeout(alignClosedMobileMenu, 250);
                    $(window).resize(function () {
                        $.sidr("close", "menu");
                        window.setTimeout(alignClosedMobileMenu, 250);
                    });
                    $("#sidr-id-close-menu-button").click(function (e) {
                        e.preventDefault();
                        $.sidr("close", "menu");
                        window.setTimeout(alignClosedMobileMenu, 250);
                    });
                    $(".sidr-class-menu-item-has-children").click(function () {
                        $(this)
                            .find("ul.sidr-class-sub-menu")
                            .slideToggle(function (e) {
                                e.preventDefault();
                            });
                    });
                    $(document.body).click(function (e) {
                        if ($.sidr("status").opened) {
                            var isBlur = true;
                            if ($(e.target).closest(".sidr").length !== 0) {
                                isBlur = false;
                            }
                            if ($(e.target).closest(".js-sidr-trigger").length !== 0) {
                                isBlur = false;
                            }
                            if (isBlur) {
                                $.sidr("close", $.sidr("status").opened);
                                window.setTimeout(alignClosedMobileMenu, 250);
                            }
                        }
                    });
                });
            })(jQuery);

            (function () {
                function labelAdFrames() {
                    document.querySelectorAll('.ad-slot iframe:not([title])').forEach(function (frame, index) {
                        frame.setAttribute('title', 'Iklan sponsor ' + (index + 1));
                    });

                    document.querySelectorAll('body > a > img:not([width])').forEach(function (img) {
                        img.setAttribute('width', img.naturalWidth || 728);
                    });

                    document.querySelectorAll('body > a > img:not([height])').forEach(function (img) {
                        img.setAttribute('height', img.naturalHeight || 90);
                    });
                }

                if ('MutationObserver' in window) {
                    var observer = new MutationObserver(labelAdFrames);
                    observer.observe(document.documentElement, { childList: true, subtree: true });
                }

                window.addEventListener('load', labelAdFrames);
                labelAdFrames();
            })();
        </script>
<script src="<?= siteAsset('assets/theme.js');?>"></script>
<?= siteBase('AdsPopup');?>
    </body>
</html>
