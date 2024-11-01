<?php
function vnad_notice_pro_features() {
    global $vnad;
    ?>
    <br/>
<?php }
function vnad_ui_editor_check($snippet) {
    global $vnad;

    $snippet['trackMode']=intval($snippet['trackMode']);
    $snippet['trackPage']=intval($snippet['trackPage']);

    $snippet['includeEverywhereActive']=0;
    if($snippet['trackPage']==vnad_TRACK_PAGE_ALL) {
        $snippet['includeEverywhereActive']=1;
    }
    $snippet=$vnad->Manager->sanitize($snippet['id'], $snippet);

    if ($snippet['name'] == '') {
        $vnad->Options->pushErrorMessage('Please enter a unique name');
    } else {
        $exist=$vnad->Manager->exists($snippet['name']);
        if ($exist && $exist['id'] != $snippet['id']) {
            //nonostante il tutto il nome deve essee univoco
            $vnad->Options->pushErrorMessage('You have entered a name that already exists. IDs are NOT case-sensitive');
        }
    }
    if ($snippet['code'] == '') {
        $vnad->Options->pushErrorMessage('Paste your HTML Tracking Code into the textarea');
    }

    if($snippet['trackMode']==vnad_TRACK_MODE_CODE) {

        $types=$vnad->Utils->query(vnad_QUERY_POST_TYPES);
        if($snippet['trackPage']==vnad_TRACK_PAGE_SPECIFIC) {
            foreach ($types as $v) {
                $includeActiveKey='includePostsOfType_'.$v['id'].'_Active';
                $includeArrayKey='includePostsOfType_'.$v['id'];
                $exceptActiveKey='exceptPostsOfType_'.$v['id'].'_Active';
                $exceptArrayKey='exceptPostsOfType_'.$v['id'];

                if ($snippet[$includeActiveKey] == 1 && $snippet[$exceptActiveKey] == 1) {
                    if (in_array(-1, $snippet[$includeArrayKey]) && in_array(-1, $snippet[$exceptArrayKey])) {
                        $vnad->Options->pushErrorMessage('Error.IncludeExcludeAll', $v['name']);
                    }
                }
                if ($snippet[$includeActiveKey] == 1 && count($snippet[$includeArrayKey]) == 0) {
                    $vnad->Options->pushErrorMessage('Error.IncludeSelectAtLeastOne', $v['name']);
                }
            }

            //second loop to respect the display order
            foreach ($types as $v) {
                $includeActiveKey='includePostsOfType_'.$v['id'].'_Active';
                $includeArrayKey='includePostsOfType_'.$v['id'];
                $exceptActiveKey='exceptPostsOfType_'.$v['id'].'_Active';
                $exceptArrayKey='exceptPostsOfType_'.$v['id'];

                if ($snippet[$includeActiveKey] == 1 && in_array(-1, $snippet[$includeArrayKey])) {
                    if ($snippet[$exceptActiveKey] == 1 && count($snippet[$exceptArrayKey]) == 0) {
                        $vnad->Options->pushErrorMessage('Error.ExcludeSelectAtLeastOne', $v['name']);
                    }
                }
            }
        } else {
            foreach($types as $v) {
                $exceptActiveKey='exceptPostsOfType_'.$v['id'].'_Active';
                $exceptArrayKey='exceptPostsOfType_'.$v['id'];

                if(isset($snippet[$exceptActiveKey])
                    && $snippet[$exceptActiveKey]==1
                    && count($snippet[$exceptArrayKey])==0) {
                    $vnad->Options->pushErrorMessage('Error.ExcludeSelectAtLeastOne', $v['name']);
                }
            }
        }
    }
}
function vnad_ui_editor() {
    global $vnad;

    $vnad->Form->prefix='Editor';
    $id=intval($vnad->Utils->qs('id', 0));
    if($id==0 && $vnad->Manager->isLimitReached(FALSE)) {
        $vnad->Utils->redirect(vnad_TAB_MANAGER_URI);
    }

    $action=$vnad->Utils->qs('action');
    $snippet=$vnad->Manager->get($id, TRUE);
    //var_dump($snippet);

    if (wp_verify_nonce($vnad->Utils->qs('vnad_nonce'), 'vnad_nonce')) {
        //var_dump($_POST);
        //var_dump($_GET);
        foreach ($snippet as $k=>$v) {
            $snippet[$k]=$vnad->Utils->qs($k);
            if (is_string($snippet[$k])) {
                $snippet[$k]=stripslashes($snippet[$k]);
            }
        }

        vnad_ui_editor_check($snippet);
        if (!$vnad->Options->hasErrorMessages()) {
            $snippet=$vnad->Manager->put($snippet['id'], $snippet);
            /*if ($id <= 0) {
                $vnad->Options->pushSuccessMessage('Editor.Add', $snippet['id'], $snippet['name']);
                $snippet=$vnad->Manager->get('', TRUE);
            } else {
                $vnad->Utils->redirect(vnad_PAGE_MANAGER.'&id='.$id);
                exit();
            }*/
            $id=$snippet['id'];
            $vnad->Utils->redirect(vnad_PAGE_MANAGER.'&id='.$id);        }
    }
    $vnad->Options->writeMessages()
    ?>
    <script>
        jQuery(function(){
            //enable/disable some part of except creating coherence
            function vnaCheckVisible() {
                var $mode=jQuery('[name=trackMode]:checked');
                var showTrackCode=false;
                var showTrackConversion=false;
                if($mode.length>0) {
                    if(parseInt($mode.val())!=<?php echo vnad_TRACK_MODE_CODE ?>) {
                        showTrackConversion=true;
                        jQuery('#position-box').hide();

                        vnaShowHide('.box-track-conversion', false);
                        vnaShowHide('#box-track-conversion-'+$mode.val(), true);
                    } else {
                        showTrackCode=true;
                        jQuery('#position-box').show();
                    }
                }
                vnaShowHide('#box-track-conversion', showTrackConversion);
                vnaShowHide('#box-track-code', showTrackCode);

                var $all=jQuery('[name=trackPage]:checked');
                if($all.length>0 && parseInt($all.val())==<?php echo vnad_TRACK_PAGE_SPECIFIC ?>) {
                    showExcept=false;
                    jQuery('[type=checkbox]').each(function() {
                        var $check=jQuery(this);
                        var id=vnad.attr($check, 'id', '');
                        if(vnad.startsWith(id, 'include')) {
                            var $select=id.replace('_Active', '');
                            $select=vnad.jQuery($select);

                            isCheck=$check.is(':checked');
                            selection=$select.select2('val');
                            found=false;
                            for(i=0; i<selection.length; i++) {
                                if(parseInt(selection[i])==-1){
                                    found=true;
                                }
                            }

                            var $except=id.replace('_Active', '');
                            $except=$except.replace('Active', '')+'Box';
                            $except=$except.substr('include'.length);
                            $except='except'+$except;
                            $except=jQuery('[id='+$except+']');

                            if(found) {
                                showExcept=true;
                                if($except.length>0) {
                                    $except.show();
                                }
                            } else {
                                if($except.length>0) {
                                    $except.hide();
                                }
                            }
                        }
                    });
                }

                showInclude=false;
                if($all.length==0) {
                    showExcept=false;
                } else {
                    if(parseInt($all.val())==<?php echo vnad_TRACK_PAGE_ALL ?>) {
                        showExcept=true;
                    } else {
                        showInclude=true;
                    }
                }
                vnaShowHide('#vnad-except-div', showExcept);
                vnaShowHide('#vnad-include-div', showInclude);
            }
            function vnaShowHide(selector, show) {
                $selector=jQuery(selector);
                if(show) {
                    $selector.show();
                } else {
                    $selector.hide();
                }
            }

            /*jQuery(".vnaTags").select2({
                placeholder: "Type here..."
                , theme: "classic"
            }).on('change', function() {
                vnaCheckVisible();
            });*/
            jQuery('.vnaLineTags,.vnad-dropdown').select2({
                placeholder: "Type here..."
                , theme: "classic"
                , width: '550px'
            });

            jQuery('.vnad-hideShow').click(function() {
                vnaCheckVisible();
            });
            jQuery('.vnad-hideShow, input[type=checkbox], input[type=radio]').change(function() {
                vnaCheckVisible();
            });
            jQuery('.vnaLineTags').on('change', function() {
                vnaCheckVisible();
            });
            vnaCheckVisible();
        });
    </script>
    <?php

    $vnad->Form->formStarts();
    $vnad->Form->hidden('id', $snippet);
    $vnad->Form->hidden('order', $snippet);

    $vnad->Form->checkbox('active', $snippet);
    $vnad->Form->text('name', $snippet);
    $vnad->Form->editor('code', $snippet);

    $values=array(vnad_POSITION_HEAD, vnad_POSITION_BODY, vnad_POSITION_FOOTER);
    $vnad->Form->dropdown('position', $snippet, $values, FALSE);
    $values=array(vnad_DEVICE_TYPE_ALL, vnad_DEVICE_TYPE_DESKTOP, vnad_DEVICE_TYPE_MOBILE, vnad_DEVICE_TYPE_TABLET);
    $vnad->Form->dropdown('deviceType', $snippet, $values, TRUE);

    $args=array('id'=>'box-track-mode');
    $vnad->Form->divStarts($args);
    {
        $vnad->Form->p('Where do you want to add this code?');
        $vnad->Form->radio('trackMode', $snippet['trackMode'], vnad_TRACK_MODE_CODE);
        $plugins=$vnad->Ecommerce->getActivePlugins();
        if(count($plugins)==0) {
            $plugins=array('Ecommerce'=>array(
                'name'=>'Ecommerce'
                , 'id'=>vnad_PLUGINS_NO_PLUGINS
                , 'version'=>'')
            );
        }
        $vnad->Form->tagNew=TRUE;
        foreach($plugins as $k=>$v) {
            $ecommerce=$v['name'];
            if(isset($v['version']) && $v['version']!='') {
                $ecommerce.=' (v.'.$v['version'].')';
            }
            $args=array('label'=>$vnad->Lang->L('Editor.trackMode_1', $ecommerce));
            $vnad->Form->radio('trackMode', $snippet['trackMode'], $v['id'], $args);
        }
        $vnad->Form->tagNew=FALSE;

    }
    $vnad->Form->divEnds();

    $args=array('id'=>'box-track-conversion');
    $vnad->Form->divStarts($args);
    {
        $vnad->Form->p('ConversionProductQuestion');
        ?>
        <p style="font-style: italic;"><?php $vnad->Lang->P('Editor.PositionBlocked') ?></p>
        <?php
        foreach($plugins as $k=>$v) {
            $args=array('id'=>'box-track-conversion-'.$v['id'], 'class'=>'box-track-conversion');
            $vnad->Form->divStarts($args);
            {
                if($v['id']==vnad_PLUGINS_NO_PLUGINS) {
                    $plugins=$vnad->Ecommerce->getPlugins(FALSE);
                    $ecommerce='';
                    foreach($plugins as $k=>$v) {
                        if($ecommerce!='') {
                            $ecommerce.=', ';
                        }
                        $ecommerce.=$k;
                    }
                    $vnad->Options->pushErrorMessage('Editor.NoEcommerceFound', $ecommerce);
                    $vnad->Options->writeMessages();
                } else {
                    $postType=$vnad->Ecommerce->getCustomPostType($v['id']);
                    $keyActive='CTC_'.$v['id'].'_Active';
                    $label=$vnad->Lang->L('Editor.EcommerceCheck', $v['name'], $v['version']);

                    if($postType!='') {
                        $args=array('post_type'=>$postType, 'all'=>TRUE);
                        $values=$vnad->Utils->query(vnad_QUERY_POSTS_OF_TYPE, $args);
                        $keyArray='CTC_'.$v['id'].'_ProductsIds';
                        if(count($snippet[$keyArray])==0) {
                            //when enabled default selected -1
                            $snippet[$keyArray]=array(-1);
                        }

                        $args=array('label'=>$label, 'class'=>'vnad-select vnaLineTags');
                        $vnad->Form->labels=FALSE;
                        $vnad->Form->dropdown($keyArray, $snippet[$keyArray], $values, TRUE, $args);
                        $vnad->Form->labels=TRUE;
                    } else {
                        $args=array('label'=>$label);
                        $vnad->Form->checkbox($keyActive, $snippet[$keyActive], 1, $args);
                    }
                }
            }
            $vnad->Form->divEnds();

            $vnad->Form->br();
            $vnad->Form->i('ConversionDynamicFields');
            $vnad->Form->br();
            $vnad->Form->br();
        }
    }
    $vnad->Form->divEnds();

    $args=array('id'=>'box-track-code');
    $vnad->Form->divStarts($args);
    {
        $vnad->Form->p('In which page do you want to insert this code?');
        $vnad->Form->radio('trackPage', $snippet['trackPage'], vnad_TRACK_PAGE_ALL);
        $vnad->Form->radio('trackPage', $snippet['trackPage'], vnad_TRACK_PAGE_SPECIFIC);

        //, 'style'=>'margin-top:10px;'
        $args=array('id'=>'vnad-include-div');
        $vnad->Form->divStarts($args);
        {
            $vnad->Form->p('Include tracking code in which pages?');
            vnad_formOptions('include', $snippet);
        }
        $vnad->Form->divEnds();

        $args=array('id'=>'vnad-except-div');
        $vnad->Form->divStarts($args);
        {
            $vnad->Form->p('Do you want to exclude some specific pages?');
            vnad_formOptions('except', $snippet);
        }
        $vnad->Form->divEnds();
    }
    $vnad->Form->divEnds();

    $vnad->Form->nonce('vnad_nonce', 'vnad_nonce');
    vnad_notice_pro_features();
    $vnad->Form->submit('Save');
    $vnad->Form->formEnds();
}

function vnad_formOptions($prefix, $snippet) {
    global $vnad;

    $types=$vnad->Utils->query(vnad_QUERY_POST_TYPES);
    foreach($types as $v) {
        $args=array('post_type'=>$v['id'], 'all'=>TRUE);
        $values=$vnad->Utils->query(vnad_QUERY_POSTS_OF_TYPE, $args);
        //$vnad->Form->premium=!in_array($v['name'], array('post', 'page'));

        $keyActive=$prefix.'PostsOfType_'.$v['id'].'_Active';
        $keyArray=$prefix.'PostsOfType_'.$v['id'];
        if($snippet[$keyActive]==0 && count($snippet[$keyArray])==0 && $prefix!='except') {
            //when enabled default selected -1
            $snippet[$keyArray]=array(-1);
        }
        $vnad->Form->checkSelect($keyActive, $keyArray, $snippet, $values);
    }
}
