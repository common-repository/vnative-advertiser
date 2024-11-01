<?php 

function vnad_uninstall($networkwide=NULL) {
	global $wpdb;

}

register_uninstall_hook(vnad_PLUGIN_FILE, 'vnad_uninstall');
?>