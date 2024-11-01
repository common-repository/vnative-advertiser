<?php

register_activation_hook(vnad_PLUGIN_FILE, 'vnad_install');
function vnad_install($networkwide=NULL) {
	global $wpdb, $vnad;

    $time=$vnad->Options->getPluginInstallDate();
    if($time==0) {
        $vnad->Options->setPluginInstallDate(time());
    }
    $vnad->Options->setPluginUpdateDate(time());
    $vnad->Options->setShowWhatsNew(TRUE);
    $vnad->Options->setPluginFirstInstall(TRUE);
}

add_action('admin_init', 'vnad_first_redirect');
function vnad_first_redirect() {
    global $vnad;
    $v=$vnad->Options->getShowWhatsNewSeenVersion();
    if($v>=0 && $v!=vnad_WHATSNEW_VERSION) {
        $vnad->Options->setShowWhatsNewSeenVersion(-1);
        vnad_install();
    }

    if ($vnad->Options->isPluginFirstInstall()) {
        $vnad->Options->setPluginFirstInstall(FALSE);
        $vnad->Options->setShowActivationNotice(TRUE);
        $vnad->Utils->redirect(vnad_PAGE_MANAGER);
    }
}
