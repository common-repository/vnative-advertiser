<?php
//per agganciarsi ogni volta che viene scritto un contenuto
add_filter('wp_head', 'vnad_head');
function vnad_head(){
    global $post, $vnad;

    $vnad->Options->setPostShown(NULL);
    if($post && isset($post->ID) && (is_page($post->ID) || is_single($post->ID))) {
        $vnad->Options->setPostShown($post);
        $vnad->Log->info('POST ID=%s IS SHOWN', $post->ID);
    }

    //future development
    //is_archive();
    //is_post_type_archive();
    //is_post_type_hierarchical();
    //is_attachment();
    $vnad->Manager->writeCodes(vnad_POSITION_HEAD);
}
add_action('wp_footer', 'vnad_footer');
function vnad_footer() {
    global $vnad;
    //there isn't a hook when <BODY> starts
    $vnad->Manager->writeCodes(vnad_POSITION_BODY);
    $vnad->Manager->writeCodes(vnad_POSITION_CONVERSION);
    $vnad->Manager->writeCodes(vnad_POSITION_FOOTER);
}

//volendo funziona anche con gli shortcode
add_shortcode('vnad', 'vnad_shortcode');
add_shortcode('vna', 'vnad_shortcode');
function vnad_shortcode($atts, $content='') {
    global $vnad;
    extract(shortcode_atts(array('id' => false), $atts));

    if (!isset($id) || !$id) {
        return '';
    }

    $snippet=$vnad->Manager->get($id, true);
    return $snippet['code'];
}

function vnad_ui_first_time() {
    global $vnad;
    if($vnad->Options->isShowActivationNotice()) {
        //$vnad->Options->pushSuccessMessage('FirstTimeActivation');
        //$vnad->Options->writeMessages();
        $vnad->Options->setShowActivationNotice(FALSE);
    }
}
function vnad_admin_footer() {
    global $vnad;
    if($vnad->Lang->bundle->autoPush && vnad_AUTOSAVE_LANG) {
        $vnad->Lang->bundle->store(vnad_PLUGIN_DIR.'languages/Lang.txt');
    }
}
add_filter('admin_footer', 'vnad_admin_footer');