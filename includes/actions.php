<?php
/**
 * Front-end Actions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Hooks EDD actions, when present in the $_GET superglobal. Every edd_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0
 * @return void
*/
add_action('init', 'vnad_do_action');
function vnad_do_action() {
    global $vnad;

	if (isset($vnad) && isset($vnad->Utils) && $vnad->Utils->qs('vnad_action')) {
        $args=array_merge($_GET, $_POST, $_COOKIE, $_SERVER);
        $name='vnad_'.$vnad->Utils->qs('vnad_action');
        if(has_action($name)) {
            $vnad->Log->debug('EXECUTING ACTION=%s', $name);
            do_action($name, $args);
        } elseif(function_exists($name)) {
            $vnad->Log->debug('EXECUTING FUNCTION=%s DATA=%s', $name, $args);
            call_user_func($name, $args);
        } elseif(strpos($vnad->Utils->qs('vnad_action'), '_')!==FALSE) {
            $pos=strpos($vnad->Utils->qs('vnad_action'), '_');
            $what=substr($vnad->Utils->qs('vnad_action'), 0, $pos);
            $function=substr($vnad->Utils->qs('vnad_action'), $pos+1);

            $class=NULL;
            switch (strtolower($what)) {
                case 'manager':
                    $class=$vnad->Manager;
                    break;
                case 'license':
                    $class=$vnad->License;
                    break;
                case 'cron':
                    $class=$vnad->Cron;
                    break;
                case 'tracking':
                    $class=$vnad->Tracking;
                    break;
                case 'properties':
                    $class=$vnad->Options;
                    break;
            }

            if(!$class) {
                $vnad->Log->fatal('NO CLASS FOR=%s IN ACTION=%s', $what, $vnad->Utils->qs('vnad_action'));
            } elseif(!method_exists ($class, $function)) {
                $vnad->Log->fatal('NO METHOD FOR=%s IN CLASS=%s IN ACTION=%s', $function, $what, $vnad->Utils->qs('vnad_action'));
            } else {
                $vnad->Log->debug('METHOD=%s OF CLASS=%s', $function, $what);
                call_user_func(array($class, $function), $args);
            }
        } else {
            $vnad->Log->fatal('NO FUNCTION FOR==%s', $vnad->Utils->qs('vnad_action'));
        }
	}
}
