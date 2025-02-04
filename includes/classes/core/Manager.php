<?php
if (!defined('ABSPATH')) exit;

class vnad_Manager {
    public function __construct() {
    }
    public function init() {
        add_action('wp_ajax_vnad_changeOrder', array(&$this, 'changeOrder'));
    }
    public function isLimitReached($notice=TRUE) {
        global $vnad;
        //$cnt=$this->codesCount();
        //$result=($cnt>=vnad_SNIPPETS_LIMIT);
        // if($notice && $cnt>0) {
        //     $vnad->Options->pushSuccessMessage('SnippetsLimitNotice', $cnt, vnad_SNIPPETS_LIMIT, vnad_PAGE_PREMIUM);
        // }
        return $result;
    }
    public function changeOrder() {
        global $vnad;
        if(!isset($_POST['order'])) {
            return;
        }

        $data=array();
        parse_str($_POST['order'], $data);

        if (isset($data['row'])) {
            $snippets=$this->values();
            foreach($snippets as $id=>$v) {
                $v['order']=0;
                $snippets[$id]=$v;
            }

            $index=1;
            foreach($data['row'] as $order=>$id) {
                $v=$snippets[$id];
                $v['order']=$index;
                $snippets[$id]=$v;
                ++$index;
            }

            foreach($snippets as $id=>$v) {
                $this->put($id, $v);
            }
        }
        echo 'OK';
        wp_die();
    }

    public function matchDeviceType($snippet) {
        global $vnad;
        $deviceType=$vnad->Utils->get($snippet, 'deviceType', FALSE);
        $deviceType=$vnad->Utils->toArray($deviceType);
        if($deviceType===FALSE || count($deviceType)==0) {
            return TRUE;
        }

        $detect=new vnad_Mobile_Detect();
        if ($detect->isMobile()) {
            $type=vnad_DEVICE_TYPE_MOBILE;
        } elseif($detect->isTablet()){
            $type=vnad_DEVICE_TYPE_TABLET;
        } else { //if(!$detect->isMobile() && !$detect->isTablet()) {
            $type=vnad_DEVICE_TYPE_DESKTOP;
        }

        $result=FALSE;
        if(in_array(vnad_DEVICE_TYPE_ALL, $deviceType) || in_array($type, $deviceType)) {
            $result=TRUE;
        }
        return $result;
    }
    public function isModeScript($snippet) {
        global $vnad;
        $result=$vnad->Utils->iget($snippet, 'trackMode', 0);
        return ($result==0);
    }
    public function isModeConversion($snippet) {
        global $vnad;
        $result=$vnad->Utils->iget($snippet, 'trackMode', 0);
        return ($result!=0);
    }
    public function isPageEverywhere($snippet) {
        global $vnad;
        if(!$this->isModeScript($snippet)) {
            return FALSE;
        }

        $result=$vnad->Utils->iget($snippet, 'trackPage', 0);
        return ($result==vnad_TRACK_PAGE_ALL);
    }
    public function isPageSpecific($snippet) {
        global $vnad;
        if(!$this->isModeScript($snippet)) {
            return FALSE;
        }

        $result=$vnad->Utils->iget($snippet, 'trackPage', 0);
        return ($result==vnad_TRACK_PAGE_SPECIFIC);
    }

    public function exists($name) {
        $snippets=$this->values();
        $result=NULL;
        $name=strtoupper($name);
        if (isset($snippets[$name])) {
            $result=$snippets[$name];
        }
        return $result;
    }

    //get a code snippet
    public function get($id, $new=FALSE) {
        global $vnad;

        $snippet=$vnad->Options->getSnippet($id);
        if (!$snippet && $new) {
            $snippet=array();
            $snippet['active']=1;
            $snippet['trackMode']=-1;
            $snippet['trackPage']=-1;
        }

        $snippet=$this->sanitize($id, $snippet);
        return $snippet;
    }

    public function sanitize($id, $snippet) {
        global $vnad;
        if($snippet==NULL || !is_array($snippet)) return NULL;

        $page=0;
        if(isset($snippet['includeEverywhereActive'])) {
            $page=(intval($snippet['includeEverywhereActive']==1) ? 0 : 1);
        }
        $defaults=array(
            'id'=>$id
            , 'active'=>0
            , 'name'=>''
            , 'code'=>''
            , 'order'=>1000
            , 'position'=>vnad_POSITION_HEAD
            , 'trackMode'=>vnad_TRACK_MODE_CODE
            , 'trackPage'=>$page
            , 'includeEverywhereActive'=>0
            , 'includeCategoriesActive'=>0
            , 'includeCategories'=>array()
            , 'includeTagsActive'=>0
            , 'includeTags'=>array()
            , 'exceptCategoriesActive'=>0
            , 'exceptCategories'=>array()
            , 'exceptTagsActive'=>0
            , 'exceptTags'=>array()
            , 'deviceType'=>vnad_DEVICE_TYPE_ALL
        );

        $types=$vnad->Utils->query(vnad_QUERY_POST_TYPES);
        foreach($types as $v) {
            $defaults['includePostsOfType_'.$v['id'].'_Active']=0;
            $defaults['includePostsOfType_'.$v['id']]=array();
            $defaults['exceptPostsOfType_'.$v['id'].'_Active']=0;
            $defaults['exceptPostsOfType_'.$v['id']]=array();
        }

        $types=$vnad->Utils->query(vnad_QUERY_CONVERSION_PLUGINS);
        foreach($types as $v) {
            //CP stands for ConversionTrackingCode
            //$defaults['CTC_'.$v['id'].'_Active']=0;
            $defaults['CTC_'.$v['id'].'_ProductsIds']=array();
            $defaults['CTC_'.$v['id'].'_CategoriesIds']=array();
            $defaults['CTC_'.$v['id'].'_TagsIds']=array();
        }
        $snippet=$vnad->Utils->parseArgs($snippet, $defaults);

        foreach ($snippet as $k => $v) {
            if (stripos($k, 'active') !== FALSE) {
                $snippet[$k]=intval($v);
            } elseif (is_array($v)) {
                switch ($k) {
                    /*
                    case 'includePostsTypes':
                    case 'excludePostsTypes':
                        //keys are string and not number
                        $result=$this->uarray($snippet, $k, FALSE);
                        break;
                    */
                    default:
                        //keys are number
                        $result=$this->uarray($snippet, $k, TRUE);
                        break;
                }
            }
        }
        $snippet['code']=trim($snippet['code']);
        $snippet['position']=intval($snippet['position']);
	    if($snippet['trackMode']==='') {
            $snippet['trackMode']=vnad_TRACK_MODE_CODE;
        } else {
            $snippet['trackMode']=intval($snippet['trackMode']);
        }
        if($snippet['trackPage']==='') {
            $snippet['trackPage']=$page;
        } else {
            $snippet['trackPage']=intval($snippet['trackPage']);
        }

        $snippet['includeEverywhereActive']=0;
        if($snippet['trackPage']==vnad_TRACK_PAGE_ALL) {
            $snippet['includeEverywhereActive']=1;
        }

        $code=strtolower($snippet['code']);
        $cnt=substr_count($code, '<iframe')+substr_count($code, '<script');
        if($cnt<=0) {
            $cnt=1;
        }
        $snippet['codesCount']=$cnt;
        return $snippet;
    }
    private function uarray($snippet, $key, $isInteger=TRUE) {
        $array=$snippet[$key];
        if (!is_array($array)) {
            $array=explode(',', $array);
        }

        if ($isInteger) {
            for ($i=0; $i < count($array); $i++) {
                $array[$i]=intval($array[$i]);
            }
        }

        $array=array_unique($array);
        $snippet[$key]=$array;
        return $snippet;
    }

    //add or update a snippet (html tracking code)
    public function put($id, $snippet) {
        global $vnad;

        if ($id == '' || intval($id) <= 0) {
            //if is a new code create a new unique id
            $id=$this->getLastId() + 1;
            $snippet['id']=$id;
        }
        $snippet=$this->sanitize($id, $snippet);
        $vnad->Options->setSnippet($id, $snippet);

        $keys=$this->keys();
        if (is_array($keys) && !in_array($id, $keys)) {
            $keys[]=$id;
            $this->keys($keys);
        }
        return $snippet;
    }

    //remove the id snippet
    public function remove($id) {
        global $vnad;
        $vnad->Options->removeSnippet($id);
        $keys=$this->keys();
        $result=FALSE;
        if (is_array($keys) && in_array($id, $keys)) {
            $keys=array_diff($keys, array($id));
            $this->keys($keys);
            $result=TRUE;
        }
        return $result;
    }

    //verify if match with this snippet
    private function matchSnippet($postId, $postType, $categoriesIds, $tagsIds, $prefix, $snippet) {
        global $vnad;
        if(!$this->matchDeviceType($snippet)) {
            return FALSE;
        }

        $include=FALSE;
        $postId=intval($postId);
        if($postId>0) {
            $what=$prefix.'PostsOfType_'.$postType;
            if(!$include && isset($snippet[$what.'_Active']) && isset($snippet[$what])&& $snippet[$what.'_Active'] && $vnad->Utils->inAllArray($postId, $snippet[$what])) {
                $vnad->Log->debug('MATCH=%s SNIPPET=%s[%s] DUE TO POST=%s OF TYPE=%s IN [%s]'
                    , $prefix, $snippet['id'], $snippet['name'], $postId, $postType, $snippet[$what]);
                $include=TRUE;
            }
        }

        return $include;
    }

    public function writeCodes($position) {
        global $vnad;

        $text='';
        switch ($position) {
            case vnad_POSITION_HEAD:
                $text='HEAD';
                break;
            case vnad_POSITION_BODY:
                $text='BODY';
                break;
            case vnad_POSITION_FOOTER:
                $text='FOOTER';
                break;
            case vnad_POSITION_CONVERSION:
                $text='CONVERSION';
                break;
        }

        $post=$vnad->Options->getPostShown();
        $args=array('field'=>'code');
        $codes=$vnad->Manager->getCodes($position, $post, $args);
        if(is_array($codes) && count($codes)>0) {
            ob_start();
            echo "\n<!--BEGIN: vNative Advertiser BY vnative.COM IN $text//-->";
            foreach($codes as $v) {
                echo "\n$v";
            }
            echo "\n<!--END: https://wordpress.org/plugins/vnative-advertiser IN $text//-->";
            $text=ob_get_contents();
            ob_end_clean();

            $purchase=$vnad->Options->getEcommercePurchase();
            if($purchase!==FALSE && intval($vnad->Options->getLicenseSiteCount())>0) {
                //retrieve user data
                $purchase->userId=intval($purchase->userId);
                if($purchase->userId>0) {
                    $user=get_user_by('id', $purchase->userId);
                    if(!is_null($user) && $user!==FALSE && get_class($user)=='WP_User') {
                        /* @var $user WP_User */
                        $purchase->email=$user->user_email;
                        $purchase->fullname=$user->user_firstname;
                        if($user->user_lastname!='') {
                            $purchase->fullname.=' '.$user->user_lastname;
                        }
                    }
                }

                $purchase->total=floatval($purchase->total);
                $purchase->amount=floatval($purchase->amount);
                $purchase->tax=floatval($purchase->tax);

                $fields=array(
                    'ORDERID'=>$purchase->orderId
                    , 'CURRENCY'=>$purchase->currency
                    , 'FULLNAME'=>$purchase->fullname
                    , 'EMAIL'=>$purchase->email
                    , 'PRODUCTS'=>$purchase->products
                    , 'AMOUNT'=>$purchase->amount
                    , 'TOTAL'=>$purchase->total
                    , 'TAX'=>$purchase->tax
                );

                $sep='@@';
                $buffer='';
                $previous=0;
                $start=strpos($text, $sep);
                if($start===FALSE) {
                    $buffer=$text;
                } else {
                    while($start!==FALSE) {
                        $buffer.=$vnad->Utils->substr($text, $previous, $start);
                        $end=strpos($text, $sep, $start+strlen($sep));
                        if($end!==FALSE) {
                            $code=$vnad->Utils->substr($text, $start+strlen($sep), $end);
                            $code=$vnad->Utils->toArray($code);
                            if(count($code)==1) {
                                $code[]='';
                            }

                            $v=FALSE;
                            if(isset($fields[$code[0]])) {
                                $v=$fields[$code[0]];
                            }
                            if(is_null($v) || $v===FALSE) {
                                $v=$code[1];
                            }
                            if(is_numeric($v)) {
                                $v=floatval($v);
                                $v=round($v, 2);
                                switch ($code[0]) {
                                    case 'TOTAL':
                                    case 'AMOUNT':
                                    case 'TAX':
                                        $v=number_format($v, 2, '.', '');
                                        break;
                                    default:
                                        $v=intval($v);
                                        break;
                                }
                            } elseif(is_array($v)) {
                                $a='';
                                foreach($v as $t) {
                                    $t=str_replace(',', '', $t);
                                    if($a!='') {
                                        $a.=',';
                                    }
                                    $a.=$t;
                                }
                                $v=$a;
                            }
                            $v=str_replace("'", '', $v);
                            $v=str_replace('"', '', $v);
                            $buffer.=$v;

                            $previous=$end+strlen($sep);
                            $start=strpos($text, $sep, $previous);
                        } else {
                            $buffer.=$vnad->Utils->substr($text, $start);
                            $previous=FALSE;
                            $start=FALSE;
                        }
                    }
                }
                if($previous!==FALSE && $previous<strlen($text)) {
                    $code=$vnad->Utils->substr($text, $previous);
                    $buffer.=$code;
                }
                $text=$buffer;
            }
            echo $text;
        }
    }

    //return snippets that match with options
    public function getConversionSnippets($options=NULL) {
        global $vnad;

        $defaults=array(
            'pluginId'=>0
            , 'categoriesIds'=>array()
            , 'productsIds'=>array()
            , 'tagsIds'=>array()
        );
        $options=$vnad->Utils->parseArgs($options, $defaults);

        $result=array();
        $pluginId=intval($options['pluginId']);
        $values=$this->values();

        foreach($values as $snippet) {
            $snippet['trackMode']=intval($snippet['trackMode']);
            if($snippet && $snippet['trackMode']>0 && $snippet['trackMode']==$pluginId) {
                $match=FALSE;

                $match=($match || $this->matchConversion($snippet, $pluginId, 'ProductsIds', $options['productsIds']));
                $match=($match && $this->matchDeviceType($snippet));
                if(!$match) {
                    //no selected so..all match! :)
                    if(count($snippet['CTC_'.$pluginId.'_ProductsIds'])==0
                        && count($snippet['CTC_'.$pluginId.'_CategoriesIds'])==0
                        && count($snippet['CTC_'.$pluginId.'_TagsIds'])==0) {
                        $match=TRUE;
                    }
                }

                if($match) {
                    $result[]=$snippet;
                }
            }
        }
        return $result;
    }
    private function matchConversion($snippet, $pluginId, $suffix, $currentIds) {
        global $vnad;

        $settingsIds='CTC_'.$pluginId.'_'.$suffix;
        if(isset($snippet[$settingsIds])) {
            $settingsIds=$snippet[$settingsIds];
        } else {
            $settingsIds=array();
        }

        $result=$vnad->Utils->inAllArray($currentIds, $settingsIds);
        return $result;
    }

    //from a post retrieve the html code that is needed to insert into the page code
    public function getCodes($position, $post, $args=array()) {
        global $vnad;

        $defaults=array('field'=>'code');
        $args=$vnad->Utils->parseArgs($args, $defaults);

        $postId=0;
        $postType='page';
        $tagsIds=array();
        $categoriesIds=array();
        if($post) {
            $postId=$vnad->Utils->get($post, 'ID', FALSE);
            if($postId===FALSE) {
                $postId=$vnad->Utils->get($post, 'post_ID');
            }
            $postType=$vnad->Utils->get($post, 'post_type');

            $options=array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'ids');
            if(isset($post->ID)) {
                $tagsIds=wp_get_post_tags($post->ID, $options);
                $categoriesIds=wp_get_post_categories($post->ID);
            } else {
                $tagsIds=array();
                $categoriesIds=array();
            }
        }

        $vnad->Options->clearSnippetsWritten();
	    if($position==vnad_POSITION_CONVERSION) {
            //write snippets previously appended
            $ids=$vnad->Options->getConversionSnippetIds();
            if($ids!==FALSE && count($ids)>0) {
            foreach($ids as $id) {
                $snippet=$vnad->Manager->get($id);
                if($snippet) {
                    $vnad->Options->pushSnippetWritten($snippet);
                    }
                }
            }
        } else {
	        $snippets=$this->values();
	        foreach ($snippets as $v) {
	            if(!$v || ($position>-1 && $v['position']!=$position) || $v['code']=='' || !$v['active']) {
	                continue;
	            }
                if ($v['trackMode']!=vnad_TRACK_MODE_CODE) {
                    continue;
                }
	            if($vnad->Options->hasSnippetWritten($v)) {
	                $vnad->Log->debug('SKIPPED SNIPPET=%s[%s] DUE TO ALREADY WRITTEN', $v['id'], $v['name']);
	                continue;
	            }

	            $match=FALSE;
                if (!$match && ($v['trackPage']==vnad_TRACK_PAGE_ALL || $v['includeEverywhereActive'])) {
                    $vnad->Log->debug('INCLUDED SNIPPET=%s[%s] DUE TO EVERYWHERE', $v['id'], $v['name']);
	                $match=TRUE;
	            }
	            if(!$match && $postId>0 && $this->matchSnippet($postId, $postType, $categoriesIds, $tagsIds, 'include', $v)) {
	                $match=TRUE;
	            }

	            if($match && $postId>0) {
	                if($this->matchSnippet($postId, $postType, $categoriesIds, $tagsIds, 'except', $v)) {
	                    $vnad->Log->debug('FOUND AT LEAST ON EXCEPT TO EXCLUDE SNIPPET=%s [%s]', $v['id'], $v['name']);
	                    $match=FALSE;
	                }
	            }

	            if ($match) {
	                $vnad->Options->pushSnippetWritten($v);
	            }
	        }
	}

        //obtain result as snippets or array of one field (tipically "id")
        $result=$vnad->Options->getSnippetsWritten();
        if ($args['field']!='all') {
            $array=array();
            foreach($result as $k=>$v) {
                $k=$args['field'];
                if(isset($v[$k])) {
                    $array[]=$v[$k];
                } else {
                    $vnad->Log->error('SNIPPET=%s [%s] WITHOUT FIELD=%s', $v['id'], $v['name'], $k);
                }
            }
            $result=$array;
        }
        return $result;
    }

    //ottiene o salva tutte le chiavi dei tracking code utilizzati ordinati per id
    public function keys($keys=NULL) {
        global $vnad;

        if (is_array($keys)) {
            $vnad->Options->setSnippetList($keys);
            $result=$keys;
        } else {
            $result=$vnad->Options->getSnippetList();
        }

        if (!is_array($result)) {
            $result=array();
        } else {
            sort($result);
        }
        return $result;
    }

    //ottiene il conteggio attuale dei tracking code
    public function count() {
        $result=count($this->keys());
        return $result;
    }
    public function codesCount() {
        $result=0;
        $ids=$this->keys();
        foreach($ids as $id) {
            $snippet=$this->get($id);
            if($snippet) {
                $result+=1;
                /*
                if($snippet['codesCount']>0) {
                    $result+=intval($snippet['codesCount']);
                } else {
                    $result+=1;
                }
                */
            }
        }
        return $result;
    }
    public function getLastId() {
        $result=0;
        $list=$this->keys();
        foreach ($list as $v) {
            $v=intval($v);
            if ($v>$result) {
                $result=$v;
            }
        }
        return $result;
    }
    
    public function values()  {
        $keys=$this->keys();
        $array=array();
        foreach ($keys as $k) {
            $v=$this->get($k);
            $array[]=$v;
        }
        usort($array, array($this, 'values_Compare'));

        $result=array();
        foreach($array as $v) {
            $id=$v['id'];
            $result[$id]=$v;
        }
        return $result;
    }
    public function values_Compare($o1, $o2) {
        global $vnad;
        $v1=$vnad->Utils->iget($o1, 'order', FALSE);
        $v2=$vnad->Utils->iget($o2, 'order', FALSE);
        $result=($v1-$v2);
        if($result==0) {
            $v1=$vnad->Utils->get($o1, 'name', FALSE);
            $v2=$vnad->Utils->get($o2, 'name', FALSE);
            $result=strcasecmp($v1, $v2);
        }
        return $result;
    }
}