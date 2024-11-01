<?php
define('vnad_WHATSNEW_VERSION', 8);
function vnad_ui_whats_new() {
    global $vnad;
    $vnad->Options->setShowWhatsNew(FALSE);
    $vnad->Options->setShowWhatsNewSeenVersion(vnad_WHATSNEW_VERSION);
    ?>
    <style>
        .vnad-grid {
            margin-left: auto;
            margin-right: auto;
            border-spacing: 10px;
            max-width: 1120px;
        }
        .vnad-grid td, .vnad-grid td p {
            font-size:16px;
            vertical-align: top;
        }
        .vnad-grid td ul {
            list-style-type: disc;
            margin-left: 30px!important;
        }
        .vnad-grid td {
            padding: 20px!important;
        }
        .vnad-headline {
            font-size:40px;
            font-weight:bold;
            text-align:center;
            margin: 10px!important;
        }
        .vnad-subheadline {
            font-size:25px!important;
            font-weight:bold;
            text-align:left;
            margin: 0px!important;
        }
    </style>

    <p class="vnad-headline">What's new in vNative Advertiser?</p>
    <table border="0" class="vnad-grid">
        <tr valign="top">
            <td valign="top" width="50%">
                Now the vNative Advertiser let you:
                <ul>
                    <li>Use tracking codes by device types</li>
                    <li>Sort tracking codes using drag & drop</li>
                    <li>Shortcode support</li>
                    <li>Fixed 6 small issues</li>
                    <li>Quick support links added</li>
                </ul>
                <br>

                <p class="vnad-subheadline">Dynamic Conversion Values</p>
                <p>Finally, Dynamic Conversion Values are now available for WooCommerce and Easy Digital Download. Now you can track the values of your conversions on <b>Google Adwords</b> and <b>Facebook Ads</b> (with the <b>New Pixel</b> and relative events like "Purchase" and others), and many other channels.</p>
                <img src="<?php echo vnad_PLUGIN_ASSETS_URI ?>landing/vnad-fb.png" />
                <br>
                <br>
            </td>
            <td valign="top" width="50%" style="border-left: 1px solid #44444E;">
                <p class="vnad-subheadline" style="margin-top: 0px!important;">Introducing the vNative Advertiser brother!</p>
                <p>We are proud to introduce Posts' Footer Manager, a free plugin that let you clean and organize the stuff you have in the footer of your blogpost.</p>
                <p>If you are tired of the MESSY stuff that appears after the content of your pages and articles, you should give it a go.</p>
                <div style="float: right;">
                    <a class="button button-secondary" href="http://wordpress.org/plugins/intelly-posts-footer-manager" target="_blank">
                        Download Posts' Footer Manager from Wordpress.org ››
                    </a>
                </div>
                <br>
                <br>
                <br>

                <p class="vnad-subheadline">Our new awesome Plugins:</p>
                <p>Built by Marketers, for Marketers.</p>
            </td>
        </tr>
    </table>
<?php }