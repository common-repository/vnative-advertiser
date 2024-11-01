<?php
function vnad_ui_metabox($post) {
    global $vnad;
    // Add an nonce field so we can check for it later.
    wp_nonce_field('vnad_meta_box', 'vnad_meta_box_nonce');

    $args=array('metabox'=>TRUE, 'field'=>'id');
    $ids=$vnad->Manager->getCodes(-1, $post, $args);

    $allIds=array();
    $snippets=$vnad->Manager->values();
    $postType=$post->post_type;
    foreach($snippets as $snippet) {
        if($snippet['trackMode']==vnad_TRACK_MODE_CODE) {
            if($snippet['active']!=0) {
                if($snippet['exceptPostsOfType_'.$postType.'_Active']==0
                    || !in_array(-1, $snippet['exceptPostsOfType_'.$postType])) {
                    $allIds[]=$snippet['id'];
                }
            }
        }
    }
    ?>
    <div>
        <?php $vnad->Lang->P('Select existing Tracking Code')?>..
    </div>
    <input type="hidden" name="vnad_all_ids" value="<?php echo implode(',', $allIds)?>" />

    <div>
        <?php
        foreach($snippets as $snippet) {
            $id=$snippet['id'];
            if($snippet['trackMode']!=vnad_TRACK_MODE_CODE) {
                continue;
            }

            $disabled='';
            $checked='';

            if(!in_array($id, $allIds)) {
                $disabled=' DISABLED';
            } elseif(in_array($id, $ids)) {
                $checked=' CHECKED';
            }
            ?>
            <input type="checkbox" class="vnad-checkbox" name="vnad_ids[]" value="<?php echo $id?>" <?php echo $checked ?> <?php echo $disabled ?> />
            <?php echo $snippet['name']?>
            <a href="<?php echo vnad_TAB_EDITOR_URI?>&id=<?php echo $id?>" target="_blank">&nbsp;››</a>
            <br/>
        <?php } ?>
    </div>

    <br/>
    <div>
        <label for="vnad_name"><?php $vnad->Lang->P('Or add a name')?></label>
        <br/>
        <input type="text" name="vnad_name" value="" style="width:100%"/>
    </div>
    <div>
        <label for="code"><?php $vnad->Lang->P('and paste HTML code here')?></label>
        <br/>
        <textarea dir="ltr" dirname="ltr" name="vnad_code" class="vnad-textarea" style="width:100%; height:175px;"></textarea>
    </div>

    <div style="clear:both"></div>
    <i>Saving the post you'll save the tracking code</i>
<?php }

//si aggancia per creare i metabox in post e page
add_action('add_meta_boxes', 'vnad_add_meta_box');
function vnad_add_meta_box() {
    global $vnad;

    $free=array('post', 'page');
    $options=$vnad->Options->getMetaboxPostTypes();
    $screens=array();
    foreach($options as $k=>$v) {
        if(intval($v)>0) {
            $screens[]=$k;
        }
    }
    if(count($screens)>0) {
        foreach ($screens as $screen) {
            add_meta_box(
                'vnad_sectionid'
                , $vnad->Lang->L('Tracking Code PRO by vnative')
                , 'vnad_ui_metabox'
                , $screen
                , 'side'
            );
        }
    }
}
function vnad_edit_snippet_array($post, &$snippet, $prefix, $diff) {
    global $vnad;
    $postId=$vnad->Utils->get($post, 'ID', FALSE);
    if($postId===FALSE) {
        $postId=$vnad->Utils->get($post, 'post_ID');
    }
    $postType=$vnad->Utils->get($post, 'post_type');

    $keyArray='PostsOfType_'.$postType;
    $keyActive=$keyArray.'_Active';
    if($snippet[$prefix.$keyActive]==0) {
        $snippet[$prefix.$keyArray]=array();
    }
    $k=$prefix.$keyArray;
    if($diff) {
        $snippet[$k]=array_diff($snippet[$k], array($postId));
    } else {
        $snippet[$k]=array_merge($snippet[$k], array($postId));
        if(in_array(-1, $snippet[$k])) {
            $snippet[$k]=array(-1);
        }
    }
    $snippet[$k]=array_unique($snippet[$k]);
    $snippet[$prefix.$keyActive]=(count($snippet[$k])>0 ? 1 : 0);
    return $snippet;
}
//si aggancia a quando un post viene salvato per salvare anche gli altri dati del metabox
add_action('save_post', 'vnad_save_meta_box_data');
function vnad_save_meta_box_data($postId) {
    global $vnad;

    //in case of custom post type edit_ does not exist
    //if (!current_user_can('edit_'.$postType, $postId)) {
    //    return;
    //}

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!isset($_POST['vnad_meta_box_nonce']) || !isset($_POST['post_type'])) {
        return;
    }
    // Verify that the nonce is valid.
    if (!wp_verify_nonce($_POST['vnad_meta_box_nonce'], 'vnad_meta_box')) {
        return;
    }

    $args=array('metabox'=>TRUE, 'field'=>'id');
    $ids=$vnad->Manager->getCodes(-1, $_POST, $args);
    if(!is_array($ids)) {
        $ids=array();
    }

    $allIds=$vnad->Utils->qs('vnad_all_ids');
    if($allIds===FALSE || $allIds=='') {
        $allIds=array();
    } else {
        $allIds=explode(',', $allIds);
    }
    $currentIds=$vnad->Utils->qs('vnad_ids', array());
    if(!is_array($currentIds)) {
        $currentIds=array();
    }

    if($ids!=$currentIds) {
        foreach($allIds as $id) {
            $id=intval($id);
            if($id<=0) {
                continue;
            }
            if(in_array($id, $currentIds) && in_array($id, $ids)) {
                //selected now and already selected
                continue;
            }
            if(!in_array($id, $currentIds) && !in_array($id, $ids)) {
                //not selected now and not already selected
                continue;
            }

            $snippet=$vnad->Manager->get($id);
            if($snippet==NULL) {
                continue;
            }

            $snippet=vnad_edit_snippet_array($_POST, $snippet, 'include', TRUE);
            $snippet=vnad_edit_snippet_array($_POST, $snippet, 'except', TRUE);
            if(in_array($id, $currentIds)) {
                $snippet=vnad_edit_snippet_array($_POST, $snippet, 'include', FALSE);
            } else {
                $snippet=vnad_edit_snippet_array($_POST, $snippet, 'except', FALSE);
            }
            $vnad->Manager->put($id, $snippet);
        }
    }

    $name=stripslashes($vnad->Utils->qs('vnad_name'));
    $code=stripslashes($vnad->Utils->qs('vnad_code'));
    if($name!='' && $code!='') {
        $postType=$_POST['post_type'];
        $keyArray='PostsOfType_'.$postType;
        $keyActive=$keyArray.'_Active';

        $snippet=array(
            'active'=>1
            , 'name'=>$name
            , 'code'=>$code
            , 'trackPage'=>vnad_TRACK_PAGE_SPECIFIC
            , 'trackMode'=>vnad_TRACK_MODE_CODE
        );
        $snippet['include'.$keyActive]=1;
        $snippet['include'.$keyArray]=array($postId);
        $snippet=$vnad->Manager->put('', $snippet);
        $vnad->Log->debug("NEW SNIPPET REGISTRED=%s", $snippet);
    }
}
