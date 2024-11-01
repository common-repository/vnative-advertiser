<?php
/**
 * Created by PhpStorm.
 * User: alessio
 * Date: 28/03/2015
 * Time: 10:20
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class vnad_Form {
    var $prefix='Form';
    var $labels=TRUE;
    var $leftLabels=TRUE;
    var $newline;

    var $tags=TRUE;
    var $onlyPremium=TRUE;
    var $leftTags=FALSE;
    var $premium=FALSE;
    var $tagNew=FALSE;

    public function __construct() {
    }

    //args can be a string or an associative array if you want
    private function getTextArgs($args, $defaults, $excludes=array()) {
        $result=$args;
        if(is_array($result) && count($result)>0) {
            $result='';
            foreach($args as $k=>$v) {
                if(count($excludes)==0 || !in_array($k, $excludes)) {
                    $result.=' '.$k.'="'.$v.'"';
                }
            }
        } elseif(!$args) {
            $result='';
        }
        if(is_array($defaults) && count($defaults)>0) {
            foreach($defaults as $k=>$v) {
                if(count($excludes)==0 || !in_array($k, $excludes)) {
                    if(stripos($result, $k.'=')===FALSE) {
                        $result.=' '.$k.'="'.$v.'"';
                    }
                }
            }
        }
        return $result;
    }

    public function tag($overridePremium=FALSE) {
        global $vnad;
        /*
            $premium=($overridePremium || $this->premium);
            if((!$overridePremium && !$this->tags) || $vnad->License->hasPremium() || ($this->onlyPremium && !$premium)) return;

            $tagClass='vnad-tag-free';
            $tagText='FREE';
            if($premium) {
                $tagClass='vnad-tag-premium';
                $tagText='<a href="'.vnad_PAGE_PREMIUM.'" target="_new">PRO</a>';
            }
        */
	    if(!$this->tags || !$this->tagNew) {
            return;
        }

        $tagClass='vnad-tag-free';
        $tagText='NEW!';
        ?>
        <div style="float:left;" class="vnad-tag <?php echo $tagClass?>"><?php echo $tagText?></div>
        <?php
    }

    public function label($name, $options='') {
        global $vnad;
        $defaults=array('class'=>'');
        $otherText=$this->getTextArgs($options, $defaults, array('label', 'id'));

        $k=$this->prefix.'.'.$name;
        if(!is_array($options)) {
            $options=array();
        }
        if(isset($options['label']) && $options['label']) {
            $k=$options['label'];
        }

        $label=$vnad->Lang->L($k);
        $for=(isset($options['id']) ? $options['id'] : $name);

        //check if is a mandatory field by checking the .txt language file
        $k=$this->prefix.'.'.$name.'.check';
        if($vnad->Lang->H($k)) {
            $label.=' (*)';
        }

        $aClass='';
	/*
        if($this->premium && !$vnad->License->hasPremium()) {
            $aClass='vnad-label-disabled';
        }
	*/
        ?>
        <label for="<?php echo $for?>" <?php echo $otherText?> >
            <?php if($this->leftTags) {
                $this->tag();
            }?>
            <span style="float:left; margin-right:5px;" class="<?php echo $aClass?>"><?php echo $label?></span>
            <?php if(!$this->leftTags) {
                $this->tag();
            }?>
        </label>
    <?php }

    public function leftInput($name, $options='') {
        if(!$this->labels) return;
        if($this->leftLabels) {
            $this->label($name, $options);
        }

        if($this->newline) {
            $this->newline();
        }
    }

    public function newline() {
        ?><div class="vnad-form-newline"></div><?php
    }

    public function rightInput($name, $args='') {
        if(!$this->labels) return;
        if (!$this->leftLabels) {
            $this->label($name, $args);
        }
        $this->newline();
    }

    public function formStarts($method='post', $action='', $args=NULL) {
        //$this->tags=FALSE;
        //$this->premium=FALSE;

        //$defaults=array('style'=>'margin:1em 0; padding:1px 1em; background:#fff; border:1px solid #ccc;'
        $defaults=array('class'=>'vnad-form');
        $other=$this->getTextArgs($args, $defaults);
        ?>
        <form method="<?php echo $method?>" action="<?php echo $action?>" <?php echo $other?> >
    <?php }

    public function formEnds() { ?>
        </form>
    <?php }

    public function divStarts($args=array()) {
        $defaults=array();
        $other=$this->getTextArgs($args, $defaults);
        ?>
        <div <?php echo $other?>>
    <?php }
    public function divEnds() { ?>
        </div>
        <div style="clear:both;"></div>
    <?php }

    public function p($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        global $vnad;
        ?>
        <p style="font-weight:bold;">
            <?php
            $vnad->Lang->P($message, $v1, $v2, $v3, $v4, $v5);
            if($vnad->Lang->H($message.'Subtitle')) { ?>
                <br/>
                <span style="font-weight:normal;">
                    <?php $vnad->Lang->P($message.'Subtitle', $v1, $v2, $v3, $v4, $v5)?>
                </span>
            <?php } ?>
        </p>
    <?php }
    public function i($message, $v1=NULL, $v2=NULL, $v3=NULL, $v4=NULL, $v5=NULL) {
        global $vnad;
        ?>
        <i><?php $vnad->Lang->P($message, $v1, $v2, $v3, $v4, $v5);?></i>
    <?php }

    var $_aceEditorUsed=FALSE;
    public function editor($name, $value='', $options=NULL) {
        global $vnad;

        $defaults=array(
            'editor'=>'html'
            , 'theme'=>'monokai'
            , 'ui-visible'=>''
            , 'height'=>350
            , 'width'=>700
        );
        $options=$vnad->Utils->parseArgs($options, $defaults);
        $value=$vnad->Utils->get($value, $name , $value);

        $args=array('class'=>'vnad-label', 'style'=>'width:auto;');
        $this->newline=TRUE;
        $this->leftInput($name, $args);

        $id=$name;
        switch ($options['editor']) {
            case 'wp':
            case 'wordpress':
                $settings=array(
                    'wpautop'=>TRUE
                    , 'media_buttons'=>TRUE
                    , 'drag_drop_upload'=>FALSE
                    , 'editor_height'=>$options['height']
                );
                wp_editor($value, $id, $settings);
                ?>
                <script>
                    jQuery('#<?php echo $id?>').attr('ui-visible', '<?php echo $options['ui-visible']?>');
                </script>
                <?php
                break;
            case 'html':
            case 'text':
            case 'javascript':
            case 'css':
                if(!$this->_aceEditorUsed) { ?>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.3/ace.js" type="text/javascript" charset="utf-8"></script>
                    <?php $this->_aceEditorUsed=TRUE;
                }

                $ace='ACE_'.$id;
                $text=$value;
                //$text=str_replace('&amp;', '&', $text);
                $text=str_replace('<', '&lt;', $text);
                $text=str_replace('>', '&gt;', $text);
                ?>
                <div id="<?php echo $id?>Ace" style="height:<?php echo $options['height']+50?>px; width: <?php echo $options['width']?>px;"><?php echo $text?></div>
                <textarea id="<?php echo $id?>" name="<?php echo $id?>" ui-visible="<?php echo $options['ui-visible']?>" style="display: none;"></textarea>
                <script>
                    var text=jQuery('#<?php echo $id?>Ace').html();
                    text=vnad.replace(text, '&lt;', '<');
                    text=vnad.replace(text, '&gt;', '>');
                    text=vnad.replace(text, '&amp;', '&');

                    var <?php echo $ace?>=ace.edit("<?php echo $id?>Ace");
                    <?php echo $ace?>.renderer.setShowGutter(false);
                    <?php echo $ace?>.setTheme("ace/theme/<?php echo $options['theme']?>");
                    <?php echo $ace?>.getSession().setMode("ace/mode/<?php echo $options['editor']?>");
                    <?php echo $ace?>.getSession().setUseSoftTabs(true);
                    <?php echo $ace?>.getSession().setUseWrapMode(true);
                    <?php echo $ace?>.session.setUseWorker(false)
                    <?php echo $ace?>.setValue(text);

                    jQuery('#<?php echo $id?>Ace').focusout(function() {
                        var $hidden=jQuery('#<?php echo $id?>');
                        var code=<?php echo $ace?>.getValue();
                        $hidden.val(code);
                    });
                    jQuery('#<?php echo $id?>Ace').trigger('focusout');
                </script>
                <?php
                break;
        }
        $this->newline=FALSE;
        $this->rightInput($name, $args);
    }

    public function textarea($name, $value='', $args=NULL) {
        if(is_array($value) && isset($value[$name])) {
            $value=$value[$name];
        }
        $defaults=array('rows'=>10, 'class'=>'vnad-textarea');
        $other=$this->getTextArgs($args, $defaults);

        $args=array('class'=>'vnad-label', 'style'=>'width:auto;');
        $this->newline=TRUE;
        $this->leftInput($name, $args);
        ?>
            <textarea dir="ltr" dirname="ltr" id="<?php echo $name ?>" name="<?php echo $name?>" <?php echo $other?> ><?php echo $value ?></textarea>
        <?php
        $this->newline=FALSE;
        $this->rightInput($name, $args);
    }

    public function text($name, $value='', $options=NULL) {
        if(is_array($value) && isset($value[$name])) {
            $value=$value[$name];
        }
        $defaults=array('class'=>'vnad-text');
        $other=$this->getTextArgs($options, $defaults);

        $options=array('class'=>'vnad-label');
        $this->leftInput($name, $options);
        ?>
            <input type="text" id="<?php echo $name ?>" name="<?php echo $name ?>" value="<?php echo $value ?>" <?php echo $other?> />
        <?php
        $this->rightInput($name, $options);
    }

    public function hidden($name, $value='', $args=NULL) {
        if(is_array($value) && isset($value[$name])) {
            $value=$value[$name];
        }
        $defaults=array();
        $other=$this->getTextArgs($args, $defaults);
        ?>
        <input type="hidden" id="<?php echo $name ?>" name="<?php echo $name ?>" value="<?php echo $value ?>" <?php echo $other?> />
    <?php }

    public function nonce($action=-1, $name='_wpnonce', $referer=true, $echo=true) {
        wp_nonce_field($action, $name, $referer, $echo);
    }

    public function dropdown($name, $value, $options, $multiple=FALSE, $args=NULL) {
        global $vnad;
        if(is_array($value) && isset($value[$name])) {
            $value=$value[$name];
        }
        $defaults=array('class'=>'vnad-select vnaTags vnad-dropdown');
        $other=$this->getTextArgs($args, $defaults);

        if(!is_array($value)) {
            $value=array($value);
        }
        if(is_string($options)) {
            $options=explode(',', $options);
        }
        if(is_array($options) && count($options)>0) {
            if(!isset($options[0]['id'])) {
                //this is a normal array so I use the values for "id" field and the "name" into the txt file
                $temp=array();
                foreach($options as $v) {
                    $temp[]=array('id'=>$v, 'name'=>$vnad->Lang->L($this->prefix.'.'.$name.'.'.$v));
                }
                $options=$temp;
            }
        }

        echo "<div id=\"$name-box\">";
        $args=array('class'=>'vnad-label');
        $this->leftInput($name, $args);
        ?>
            <select id="<?php echo $name ?>" name="<?php echo $name?><?php echo ($multiple ? '[]' : '')?>" <?php echo ($multiple ? 'multiple' : '')?> <?php echo $other?> >
                <?php
                foreach($options as $v) {
                    $selected='';
                    if(in_array($v['id'], $value)) {
                        $selected=' selected="selected"';
                    }
                    ?>
                    <option value="<?php echo $v['id']?>" <?php echo $selected?>><?php echo $v['name']?></option>
                <?php } ?>
            </select>
        <?php
        $this->rightInput($name, $args);
        echo '</div>';
    }

    public function br() { ?>
        <br/>
    <?php }
    
    public function submit($value='', $args=NULL) {
        global $vnad;
        $defaults=array();
        $other=$this->getTextArgs($args, $defaults);
        if($value=='') {
            $value='Send';
        }
        $this->newline();
        ?>
            <input type="submit" class="button-primary vnad-button vnad-submit" value="<?php $vnad->Lang->P($value)?>" <?php echo $other?>/>
    <?php }

    public function delete($id, $action='delete', $args=NULL) {
        global $vnad;
        $defaults=array();
        $other=$this->getTextArgs($args, $defaults);
        ?>
            <input type="button" class="button vnad-button" value="<?php $vnad->Lang->P('Delete?')?>" onclick="if (confirm('<?php $vnad->Lang->P('Question.DeleteQuestion')?>') ) window.location='<?php echo vnad_TAB_MANAGER_URI?>&action=<?php echo $action?>&id=<?php echo $id ?>&amp;vnad_nonce=<?php echo esc_attr(wp_create_nonce('vnad_delete')); ?>';" <?php echo $other?> />
            &nbsp;
        <?php
    }

    public function radio($name, $current=1, $value=1, $options=NULL) {
        if(!is_array($options)) {
            $options=array();
        }
        $options['radio']=TRUE;
        $options['id']=$name.'_'.$value;
        return $this->checkbox($name, $current, $value, $options);
    }
    public function checkbox($name, $current=1, $value=1, $options=NULL) {
        global $vnad;
        if(is_array($current) && isset($current[$name])) {
            $current=$current[$name];
        }

        /*
            $defaults=array('class'=>'vnad-checkbox', 'style'=>'margin:0px; margin-right:4px;');
            if($this->premium && !$vnad->License->hasPremium()) {
                $defaults['disabled']='disabled';
                $value='';
            }
        */
        if(!is_array($options)) {
            $options=array();
        }

        $label=$name;
        $type='checkbox';
        if(isset($options['radio']) && $options['radio']) {
            $type='radio';
            $label.='_'.$value;
        }

        $defaults=array(
            'class'=>'vnad-checkbox'
            , 'style'=>'margin:0px; margin-right:4px;'
            , 'id'=>$name
        );
        $other=$this->getTextArgs($options, $defaults, array('radio', 'label'));
        $prev=$this->leftLabels;
        $this->leftLabels=FALSE;

        $label=(isset($options['label']) ? $options['label'] : $this->prefix.'.'.$label);
        $id=(isset($options['id']) ? $options['id'] : $name);
        $options=array(
            'class'=>''
            , 'style'=>'margin-top:-1px;'
            , 'label'=>$label
            , 'id'=>$id
        );
        $this->leftInput($name, $options);
        ?>
            <input type="<?php echo $type ?>" name="<?php echo $name?>" value="<?php echo $value?>" <?php echo($current==$value ? 'checked="checked"' : '') ?> <?php echo $other?> >
    <?php
        $this->rightInput($name, $options);
        $this->leftLabels=$prev;
    }

    public function checkText($nameActive, $nameText, $value) {
        global $vnad;

        $args=array('class'=>'vnad-hideShow vnad-checkbox'
        , 'vnad-hideIfTrue'=>'false'
        , 'vnad-hideShow'=>$nameText.'Text');
        $this->checkbox($nameActive, $value, 1, $args);
        if($this->premium) {
            return;
        }
        ?>
        <div id="<?php echo $nameText?>Text" style="float:left;">
            <?php
            $prev=$this->labels;
            $this->labels=FALSE;
            $args=array();
            $this->text($nameText, $value, $args);
            $this->labels=$prev;
            ?>
        </div>
    <?php }

    //create a checkbox with a left select visible only when the checkbox is selected
    public function checkSelect($nameActive, $nameArray, $value, $values, $options=NULL) {
        global $vnad;
        ?>
        <div id="<?php echo $nameArray?>Box" style="float:left;">
            <?php
            $defaults=array(
                'class'=>'vnad-hideShow vnad-checkbox'
                , 'vnad-hideIfTrue'=>'false'
                , 'vnad-hideShow'=>$nameArray.'Tags'
            );
            $options=$vnad->Utils->parseArgs($options, $defaults);
            $this->checkbox($nameActive, $value, 1, $options);
            /*if(!$this->premium || $vnad->License->hasPremium()) { ?>*/
            if(TRUE) { ?>
                <div id="<?php echo $nameArray?>Tags" style="float:left;">
                    <?php
                    $prev=$this->labels;
                    $this->labels=FALSE;
                    $options=array('class'=>'vnad-select vnaLineTags');
                    $this->dropdown($nameArray, $value, $values, TRUE, $options);
                    $this->labels=$prev;
                    ?>
                </div>
            <?php } ?>
        </div>
    <?php
        $this->newline();
    }
}