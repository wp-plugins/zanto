<?php
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__)
    die('Access denied.');

global $lang_locales, $zwt_site_obj, $zwt_icon_url;
$trans_network = $zwt_site_obj->modules['trans_network'];


$current_locale_front = get_option('WPLANG');
if (empty($current_locale_front)) {
    $current_locale_front = 'en_US';
}

$current_locale_back = get_locale();
if ($WordPress_language->current_scope == 'front-end') {
    $current_locale = $current_locale_front;
} else {
    $current_locale = $current_locale_back;
}


$current_lang_code_front = $trans_network->get_lang_code($current_locale_front);
$current_lang_code_back = $trans_network->get_lang_code($current_locale_back);

$current_lang_code = $trans_network->get_lang_code($current_locale);
$current_lang = $trans_network->get_display_language_name($current_locale);
?>

<div class="wrap">
    <div class="icon32" style='background:url("<?php echo $zwt_icon_url; ?>") no-repeat;'><br /></div>
    <h2><?php echo __('Zanto Locale Management', 'Zanto'); ?></h2>
    <p><?php _e('Zanto allows individual users to display their admin interface in the language of their choice that is different from the front end language of the blog.', 'Zanto') ?></p>
    <p><?php _e('An internet connection is required for sites being developed on localhost to download the languages chosen.', 'Zanto') ?></p>
<script type="text/javascript">
var zwt_pluginUrl = '<?php  echo GTP_PLUGIN_URL; ?>'
</script>
    <br clear="all" />

    <div id="menu-management-liquid">
	            <h2 class="nav-tab-wrapper">
                <a class="nav-tab <?php if (isset($_GET['scope']) && $_GET['scope'] == 'back-end' || !isset($_GET['scope'])): ?> nav-tab-active<?php endif ?>" href="<?php echo admin_url('admin.php?page=zwt_manage_locales') ?>"><?php _e('Admin Language', 'Zanto') ?></a>
                <a class="nav-tab <?php if (isset($_GET['scope']) && $_GET['scope'] == 'front-end'): ?> nav-tab-active<?php endif ?>" href="<?php echo admin_url('admin.php?page=zwt_manage_locales&scope=front-end') ?>"><?php _e('Front-End Language', 'Zanto') ?></a>
                <a class="nav-tab <?php if (isset($_GET['scope']) && $_GET['scope'] =='flag-mng'): ?> nav-tab-active<?php endif ?>" href="<?php echo admin_url('admin.php?page=zwt_manage_locales&scope=flag-mng') ?>"><?php _e('Flag Management', 'Zanto') ?></a>

            </h2>
        <div id="menu-management" style="margin-right:10px;width:auto;"> 

            <div class="menu-edit" <?php if (version_compare($GLOBALS['wp_version'], '3.2.1', '<=')): ?>style="border-style:solid;border-radius:3px;border-width:1px;border-color:#DFDFDF;<?php endif; ?>">
                <div id="nav-menu-header">
                    &nbsp;                        
                </div>
                <div id="post-body" style="border-style: solid;border-width: 1px 0;padding: 10px;">
                    <div id="post-body-content">
                        <?php
                        if (isset($_GET['download_complete'])) {
                            $WordPress_language->download_complete_div($current_lang, $current_locale, true);
                        }
                        if (isset($_GET['no_translation_available'])) {
                            $WordPress_language->no_translation_available_div($current_lang, $current_locale);
                        }

                        

                        $mo_downloader_obj = new ZWT_Download_MO();
                        $wptranslations = $mo_downloader_obj->get_option('translations');
                        //@todo check if we need to update translations.
                        $installing_translations = false;

                        if (isset($wptranslations[$current_locale]['installed'])) {
                            echo '<p>' . sprintf(__('Current .mo file translation was downloaded on %s', 'Zanto'), date("F j, Y @H:i", $wptranslations[$current_locale]['time'])) . '</p>';
                            ?>
                            <div id="wp_language_translation_state">
                                <?php echo $WordPress_language->current_translation_state($current_lang_code, $current_locale, $wptranslations); ?>
                            </div>
                            <?php
                        }

                        if ($WordPress_language->download_lang) {
                            $WordPress_language->download_complete_div(true);
                        }

                        if (!isset($_GET['scope']) || (isset($_GET['scope']) && $_GET['scope'] == 'back-end')) {

                            $langs = $WordPress_language->get_languages();

                            $more_langs_on = isset($_GET['more_langs']) && $_GET['more_langs'] == 1;
                            ?>
                            <p><?php echo sprintf(__('Your personal <b>Admin</b> language is %s. Current locale is %s.', 'Zanto'), zwt_get_flag($current_locale) . '&nbsp;' . $current_lang, $current_locale) ?></p>


                            <a id="wp_lang_change_lang_button" href="#" <?php if ($more_langs_on): ?> style="display:none"<?php endif; ?>><?php _e('Change language', 'Zanto'); ?></a>
                            <div id="wp_lang_change_lang"<?php if (!$more_langs_on): ?> style="display:none"<?php endif; ?>>


                                <br /><strong><?php echo __('Select a language', 'Zanto'); ?></strong>

                                <div style="padding:10px;">
                                    <div class="wp_lang_thickbox" style="padding-bottom:10px">
                                        <table cellpadding="3">
                                            <tr>
                                                <?php
                                                $count = 0;
                                                foreach ($langs as $lang) {
                                                    if ($count != 0 && !($count % 4)) {
                                                        echo '</tr><tr>';
                                                    }
                                                    $link = '#TB_inline?height=255&width=750&inlineId=wp_lang_switch_popup&modal=true';
                                                    $link .= '&switch_to=' . $lang['default_locale'];
                                                    $link .= '&scope=' . $WordPress_language->current_scope;

                                                    echo '<td>' . zwt_get_flag($lang['default_locale']) . '&nbsp;<a href="' . $link . '" class="thickbox">' . $trans_network->get_display_language_name($lang['default_locale'], $current_locale_back) . ' (' . $trans_network->get_display_language_name($lang['default_locale'], $lang['default_locale']) . ')</a></td>';
                                                    $count++;
                                                }
                                                ?>
                                            </tr>
                                        </table>
                                    </div>
                                    <a id="wp_lang_change_lang_cancel" href="#" class="button-secondary"><?php echo __('Cancel', 'Zanto'); ?></a>
                                </div>
                            </div>
                        <?php
                        } elseif (isset($_GET['scope']) && $_GET['scope'] == 'front-end') {
                            global $zwt_site_obj;
                            $langs = $zwt_site_obj->modules['trans_network']->get_languages();
                            ?>
                            <p><?php echo sprintf(__('The <b>Front-end</b> language is set to %s. Front end locale is %s.', 'Zanto'), zwt_get_flag($current_locale) . '&nbsp;' . $current_lang, $current_locale) ?></p>

                            <a id="wp_lang_change_lang_button" href="#" ><?php _e('Change language', 'Zanto'); ?></a>
                            <div id="wp_lang_change_lang" style="display:none">


                                <br /><strong><?php echo __('Select a language', 'Zanto'); ?></strong>

                                <div class="wp_lang_thickbox" style="padding:10px;">

                                    <select id="front_mo_download"><option value="null">- <?php _e('Select', 'Zanto'); ?> -</option>
                                        <?php
                                        foreach ($langs as $c_language)
                                            echo '<option value="' . $c_language['default_locale'] . '">' . $c_language['display_name'] . ' (' . $c_language['english_name'] . ')</option>';
                                        ?>

                                    </select>	
                                    <a id="wp_front_lang_change" href="#" class="button-primary"><?php _e('Change Language', 'Zanto') ?></a>
                                    <a id="wp_lang_change_lang_cancel" href="#" class="button-secondary"><?php echo __('Cancel', 'Zanto'); ?></a>
                                    <p class="submit"><a href="<?php echo admin_url('admin.php?page=zwt_manage_locales&edit_langs=1') ?>"><?php _e('Edit Languages', 'Zanto') ?></a></p>
                                </div>	
                            </div>			

                            <?php } else { ?>
							<h3><?php _e('Custom Flags','Zanto') ?></h3>
                            <div> <?php _e('<p>Here you can define a different directory containing the flags you want to use
													instead of the default flags.</p><p>
													The flag names should be the same as the locale name e.g the English flag Name should be en_EN, while the french flag should be named fr_FR.
													If no directory is selected, the default Zanto flags will be used.
													</p>', 'Zanto') ?><br/></div>

							<p><strong><?php _e('Select a flag directory from your theme','Zanto') ?></strong></p>
                            <i class="fa fa-folder-open" style="font-size: 1.5em; margin-right: 4px; color: #bbb; vertical-align: middle;"></i>
							<?php $dir = $theme_folder = get_template_directory();
                                  $theme_url= get_bloginfo('template_url');							
								  $theme_base_folder =  basename($dir);?>
							
							<select id="zwt_flag_url">
                           <option value="-1"><?php _e('Default', 'Zanto') ?></option>
                                <?php
                                global $zwt_site_obj;
                                $custom_url=$zwt_site_obj->modules['settings']->settings['lang_switcher']['custom_flag_url']; 
								$flag_ext = $zwt_site_obj->modules['settings']->settings['lang_switcher']['custom_flag_ext']; 
                                if (is_dir($dir)) {// Open the themes directory, and proceed to read its contents
                                    while ($dirs = glob($dir . '/*', GLOB_ONLYDIR)) {
                                        $dir .= '/*';
                                        if (!$d) {
                                            $d = $dirs;
                                        } else {
                                            $d = array_merge($d, $dirs);
                                        }
                                    }
                                    foreach ($d as $dirct_folder) {
                                        $folder = str_replace($theme_folder . '/', "", $dirct_folder);
										$value= $theme_url.'/'.$folder;
                                        echo '<option '.selected($value,$custom_url).' value="'.$value.'"> ' . $theme_base_folder.'/'.$folder . '</option>';
                                    }
                                }
                                ?>

                            </select>	
							<span id="zwt_flag_ext_span"><label style="margin-left:5px">
							<select id="zwt_flag_ext">
							<option <?php selected($flag_ext,'png') ?> value="png">.PNG</option>
							<option <?php selected($flag_ext,'gif') ?> value="gif">.GIF</option>
							<option <?php selected($flag_ext,'jpg') ?> value="jpg">.JPG</option>
							</select> Flag images extention</label>
							</span>

                             <?php wp_nonce_field('zwt_custom_flag', 'zwt_custom_flags');?>

                            <p><br/>
                            <a id="zwt_flag_url_change" href="#" class="button-primary"><?php _e('Update', 'Zanto') ?></a>
							<br/>
                            </p>
                            <?php } ?>

                    </div>
                    <br clear="all" />
                </div>
                <div id="nav-menu-footer">
                    &nbsp;     
                </div>                        
            </div>
        </div>
    </div>
<?php do_action('zwt_menu_footer'); ?>
</div>