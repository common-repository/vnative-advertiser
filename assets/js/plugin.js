//vnative
jQuery('.wrap .updated.fade').remove();
jQuery('.woocommerce-message').remove();
jQuery('.error').remove();
jQuery('.info').remove();
jQuery('.update-nag').remove();

jQuery(function() {
    "use strict";
    //WooCommerce errors
    var removeWooUpdateTheme = setInterval(function () {
        if (jQuery('.wrap .updated.fade').length > 0) {
            jQuery('.wrap .updated.fade').remove();
            clearInterval(removeWooUpdateTheme);
        }
    }, 100);
    var removeWooMessage = setInterval(function () {
        if (jQuery('.woocommerce-message').length > 0) {
            jQuery('.woocommerce-message').remove();
            clearInterval(removeWooMessage);
        }
    }, 100);

    jQuery('.wrap .updated.fade').remove();
    jQuery('.woocommerce-message').remove();
    jQuery('.error').remove();
    jQuery('.info').remove();
    jQuery('.update-nag').remove();
});

jQuery(function() {
    if(jQuery('.wrap .updated.fade').length>0) {
        jQuery('.wrap .updated.fade').remove();
    }
    if(jQuery('.woocommerce-message').length>0) {
        jQuery('.woocommerce-message').remove();
    }
    jQuery('.update-nag,.updated,.error').each(function() {
        var $self=jQuery(this);
        if(!$self.hasClass('iwp')) {
            $self.remove();
        }
    });
});

jQuery(function() {
    "use strict";

    //WooCommerce errors
    var removeWooUpdateTheme=setInterval(function () {
        if (jQuery('.wrap .updated.fade').length > 0) {
            jQuery('.wrap .updated.fade').remove();
            clearInterval(removeWooUpdateTheme);
        }
    }, 100);
    var removeWooMessage=setInterval(function () {
        if (jQuery('.woocommerce-message').length > 0) {
            jQuery('.woocommerce-message').remove();
            clearInterval(removeWooMessage);
        }
    }, 100);
});

jQuery(function() {
    jQuery('.vnad-select-onfocus').click(function() {
        var $self=jQuery(this);
        $self.select();
    });

    jQuery(".vnad-hideShow").click(function() {
        vnad_hideShow(this);
    });
    jQuery(".vnad-hideShow").each(function() {
        vnad_hideShow(this);
    });

    function vnad_hideShow(v) {
        var $source=jQuery(v);
        if($source.attr('vnad-hideIfTrue') && $source.attr('vnad-hideShow')) {
            var $destination=jQuery('[name='+$source.attr('vnad-hideShow')+']');
            if($destination.length==0) {
                $destination=jQuery('#'+$source.attr('vnad-hideShow'));
            }
            if($destination.length>0) {
                var isChecked=$source.is(":checked");
                var hideIfTrue=($source.attr('vnad-hideIfTrue').toLowerCase()=='true');

                if(isChecked) {
                    if(hideIfTrue) {
                        $destination.hide();
                    } else {
                        $destination.show();
                    }
                } else {
                    if(hideIfTrue) {
                        $destination.show();
                    } else {
                        $destination.hide();
                    }
                }
            }
        }
    }
});