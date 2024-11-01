<?php
class vnad_Tabs {
    private $tabs = array();

    function __construct() {
    }
    public function init() {
        global $vnad;
        if($vnad->Utils->isAdminUser()) {
            add_action('admin_menu', array(&$this, 'attachMenu'));
            add_filter('plugin_action_links', array(&$this, 'pluginActions'), 10, 2);
            if($vnad->Utils->isPluginPage()) {
                add_action('admin_enqueue_scripts', array(&$this, 'enqueueScripts'));
            }
        }
    }

    function attachMenu() {
        add_submenu_page('options-general.php'
            , vnad_PLUGIN_NAME, vnad_PLUGIN_NAME
            , 'manage_options', vnad_PLUGIN_SLUG, array(&$this, 'showTabPage'));
    }
    function pluginActions($links, $file) {
        global $vnad;
        if($file==vnad_PLUGIN_SLUG.'/index.php'){
            $settings=array();
            $settings[]="<a href='".vnad_TAB_MANAGER_URI."'>".$vnad->Lang->L('Settings').'</a>';
            $settings[]="<a href='".vnad_PAGE_PREMIUM."'>".$vnad->Lang->L('PREMIUM').'</a>';
            $links=array_merge($settings, $links);
        }
        return $links;
    }
    function enqueueScripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jQuery');
        wp_enqueue_script('jquery-ui-sortable');

        $this->wpEnqueueStyle('assets/css/style.css');
        $this->wpEnqueueStyle('assets/deps/select2-3.5.2/select2.css');
        $this->wpEnqueueScript('assets/deps/select2-3.5.2/select2.min.js');
        $this->wpEnqueueScript('assets/deps/starrr/starrr.js');

        $this->wpEnqueueScript('assets/js/library.js');
        $this->wpEnqueueScript('assets/js/plugin.js');
    }
    function wpEnqueueStyle($uri, $name='') {
        if($name=='') {
            $name=explode('/', $uri);
            $name=$name[count($name)-1];
            $dot=strrpos($name, '.');
            if($dot!==FALSE) {
                $name=substr($name, 0, $dot);
            }
            $name=vnad_PLUGIN_PREFIX.'_'.$name;
        }

        $v='?v='.vnad_PLUGIN_VERSION;
        wp_enqueue_style($name, vnad_PLUGIN_URI.$uri.$v);
    }
    function wpEnqueueScript($uri, $name='', $version=FALSE) {
        if($name=='') {
            $name=explode('/', $uri);
            $name=$name[count($name)-1];
            $dot=strrpos($name, '.');
            if($dot!==FALSE) {
                $name=substr($name, 0, $dot);
            }
            $name=vnad_PLUGIN_PREFIX.'_'.$name;
        }

        $v='?v='.vnad_PLUGIN_VERSION;
        $deps=array();
        wp_enqueue_script($name, vnad_PLUGIN_URI.$uri.$v, $deps, $version, FALSE);
    }

    function showTabPage() {
        global $vnad;

        $v=$vnad->Options->getShowWhatsNewSeenVersion();
        if($v!=vnad_WHATSNEW_VERSION) {
            $vnad->Options->setShowWhatsNew(TRUE);
        }

        $hwb=intval($vnad->Utils->qs('hwb', ''));
        if($hwb!='') {
            $vnad->Options->setShowWhatsNew(FALSE);
        }

        $id=intval($vnad->Utils->qs('id', 0));
        $defaultTab=vnad_TAB_MANAGER;
        $tab=$vnad->Utils->qs('tab', $defaultTab);

        if($vnad->Options->isShowWhatsNew()) {
            $tab=vnad_TAB_WHATS_NEW;
            $defaultTab=$tab;
            $this->tabs[vnad_TAB_WHATS_NEW]=$vnad->Lang->L('What\'s New');
            //$this->tabs[vnad_TAB_MANAGER]=$vnad->Lang->L('Start using the plugin!');
        } else {
            if($id>0) {
                $this->tabs[vnad_TAB_EDITOR]=$vnad->Lang->L($id>0 && $tab==vnad_TAB_EDITOR ? 'Edit Script' : 'Add New Script');
            } elseif($tab!=vnad_TAB_EDITOR) {
                $tab = vnad_TAB_MANAGER;
            }

            $this->tabs[vnad_TAB_MANAGER]=$vnad->Lang->L('Manager');
            $this->tabs[vnad_TAB_DOCS]=$vnad->Lang->L('Docs & FAQ');
        }

        ?>

        <div class="wrap" style="margin: 5px;">
            <?php
            $this->showTabs($defaultTab);
            $header='';
            switch ($tab) {
                case vnad_TAB_EDITOR:
                    $header=($id>0 ? 'Edit' : 'Add');
                    break;
                case vnad_TAB_WHATS_NEW:
                    $header='';
                    break;
                case vnad_TAB_MANAGER:
                    $header='Manager';
                    break;
            }

            if($vnad->Lang->H($header.'Title')) { ?>
                <h2><?php $vnad->Lang->P($header . 'Title', vnad_PLUGIN_VERSION) ?></h2>
                <?php if ($vnad->Lang->H($header . 'Subtitle')) { ?>
                    <div><?php $vnad->Lang->P($header . 'Subtitle') ?></div>
                <?php } ?>
                <br/>
            <?php }


            ?>
            <div style="float:left; margin:5px;">
                <?php
                $vnad->Options->setShowWhatsNew(FALSE);
                $styles=array();
                $styles[]='float:left';
                $styles[]='margin-right:20px';
                if($tab!=vnad_TAB_WHATS_NEW) {
                    $styles[]='max-width:750px';
                }
                $styles=implode('; ', $styles);
                ?>
                <div id="vnad-page" style="<?php echo $styles?>">
                    <?php switch ($tab) {
                        case vnad_TAB_WHATS_NEW:
                            vnad_ui_whats_new();
                            break;
                        case vnad_TAB_EDITOR:
                            vnad_ui_editor();
                            break;
                        case vnad_TAB_MANAGER:
                            vnad_ui_manager();
                            break;
                    } ?>
                </div>
                <?php if($tab!=vnad_TAB_WHATS_NEW) { ?>
                    <div id="vnad-sidebar" style="float:left; max-width: 250px;">
                        <?php
                        $count=$this->getPluginsCount();
                        $plugins=array();
                        while(count($plugins)<2) {
                            $id=rand(1, $count);
                            if(!isset($plugins[$id])) {
                                $plugins[$id]=$id;
                            }
                        }

                        $this->drawContactUsWidget();
                        ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div style="clear:both"></div>
    <?php }
    function getPluginsCount() {
        global $vnad;
        $index=1;
        while($vnad->Lang->H('Plugin'.$index.'.Name')) {
            $index++;
        }
        return $index-1;
    }

    function drawContactUsWidget() {
        global $vnad;
        ?>
        <b><?php $vnad->Lang->P('Sidebar.Title') ?></b>
        <ul style="list-style: circle;">
            <?php
            $index=1;
            while($vnad->Lang->H('Sidebar'.$index.'.Name')) { ?>
                <li>
                    <a href="<?php $vnad->Lang->P('Sidebar'.$index.'.Url')?>" target="_blank">
                        <?php $vnad->Lang->P('Sidebar'.$index.'.Name')?>
                    </a>
                </li>
                <?php $index++;
            } ?>
        </ul>
    <?php }
    function showTabs($defaultTab) {
        global $vnad;
        $tab=$vnad->Check->of('tab', $defaultTab);
        if($tab==vnad_TAB_DOCS) {
            $vnad->Utils->redirect(vnad_TAB_DOCS_URI);
        }
        if($vnad->Options->isShowWhatsNew()) {
            
        }

        ?>
        <h2 class="nav-tab-wrapper" style="float:left; width:97%;">
            <?php
            foreach ($this->tabs as $k=>$v) {
                $active = ($tab==$k ? 'nav-tab-active' : '');
                $style='';
                $target='_self';
                if($vnad->Options->isShowWhatsNew() && $k==vnad_TAB_MANAGER) {
                    $active='';
                    $style='background-color:#F2E49B';
                }
                if($k==vnad_TAB_DOCS) {
                    $target='_blank';
                    $style='background-color:#F2E49B';
                }
                ?>
                <a style="float:left; margin-left:10px; <?php echo $style?>" class="nav-tab <?php echo $active?>" target="<?php echo $target ?>" href="?page=<?php echo vnad_PLUGIN_SLUG?>&tab=<?php echo $k?>"><?php echo $v?></a>
            <?php
            }
            ?>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.2.0/css/font-awesome.min.css">
            <style>
                .starrr {display:inline-block}
                .starrr i{font-size:16px;padding:0 1px;cursor:pointer;color:#2ea2cc;}
            </style>
            
            <script>
                jQuery(function() {
                    jQuery(".starrr").starrr();
                    jQuery('#vnad-rate').on('starrr:change', function(e, value){
                        var url='https://wordpress.org/support/view/plugin-reviews/vnative-advertiser?rate=5#postform';
                        window.open(url);
                    });
                    jQuery('#rate-box').show();
                });
            </script>
        </h2>
        <div style="clear:both;"></div>
    <?php }
}
