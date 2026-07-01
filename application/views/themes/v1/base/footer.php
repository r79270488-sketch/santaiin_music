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
        <script src="https://cdn.jsdelivr.net/npm/mediaelement@4.2.9/build/mediaelement-and-player.min.js" integrity="sha256-bGz/0MMW4d9dsyq3BEXee8f377noiWxTibmRZqWvvYI=" crossorigin="anonymous"></script>
        <script type="text/javascript">
            function playAudio(id, ytid) {
                $(".divPlayer").html("");
                var html = '<iframe width="100%" height="200" src="https://www.youtube.com/embed/' + ytid + '?autoplay=1&rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="max-width:100%"></iframe>';
                $("#player-" + id).html(html);
            }
            if ("serviceWorker" in navigator) {
                window.addEventListener("load", function () {
                    navigator.serviceWorker
                        .register("<?= siteAsset('sw.js'); ?>", { scope: "/" })
                        .then(function (registration) {
                            console.log("PWA service worker ready");
                            registration.update();
                        })
                        .catch(function (error) {
                            console.log("Registration failed with " + error);
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

                    $("#youtube-audio, #youtube-video").mediaelementplayer();
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
        </script>
<script src="<?= siteAsset('assets/theme.js');?>"></script>
<?= siteBase('AdsPopup');?>
    </body>
</html>
