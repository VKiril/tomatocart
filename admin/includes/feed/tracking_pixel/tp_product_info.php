<?php
    $config = DIR_FS_CATALOG."admin/includes/feed/config/Config.php";
    require($config);
    $configuration = new Config();
    if($configuration->getConfig('FEED_TRACKING_PIXEL_STATUS') != 'Y')
        return;
?>
        <script type="text/javascript">
            var _feeparams = _feeparams || new Object();
            _feeparams.client = '<?php echo $configuration->getConfig('FEED_CLIENT_ID'); ?>';
            _feeparams.event = 'click';
            (function() {
                console.log(_feeparams);
                var head = document.getElementsByTagName('head')[0];
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = (location.protocol == "https:" ? "https:" : "http:") + '//daily-feed.com/bundles/managementtracking/js/pixel.js';
                // fire the loading
                head.appendChild(script);
            })();
        </script>

<?php //} ?>