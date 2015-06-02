<?php

global $osC_Language;
echo 'Ext.namespace("Toc.feed");';
include('feed_config_dialog.php');
?>

Ext.override(TocDesktop.FeedWindow, {
    createWindow : function() {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('feed-win');
        if(!win){
            win = desktop.createWindow({
                id: 'feed-win',
                title: '<?php echo $osC_Language->get('heading_title'); ?>',
                width: 650,
                height: 500,
                padding: 30,
                iconCls: 'icon-configuration-win',
                autoScroll: true
            }, Toc.feed.Feed);
        }
        win.show();
    }
});