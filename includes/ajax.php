<?php
/**
 * the ajax SWTICHBOARD that fires specific functions
 * according to the value of Query Var 'admin_fn' for admin and 'public_fn' for the public side
 * @package ZWT_Base
 * @author Zanto Translate
 */
if ($_SERVER['SCRIPT_FILENAME'] == __FILE__)
    die('Access denied.');
global $zwt_site_obj, $blog_id;
$WordPress_language = ZWT_MO::getSingleton();

if (is_admin() && isset($_REQUEST['admin_fn'])) {
    switch ($_REQUEST['admin_fn']) {
        case 'zwt_copy_from_original_ajx':
            $p_id = intval($_REQUEST['p_id']);
            $b_id = intval($_REQUEST['b_id']);
            switch_to_blog($b_id);
            $post = get_post($p_id);
            restore_current_blog();
            $error = false;
            $json = array();
            if (!empty($post)) {
			    $json['title'] = $post->post_title;
                if ($_REQUEST['editor_type'] == 'rich') {
                    $json['body'] = htmlspecialchars_decode(wp_richedit_pre($post->post_content));
                } else {
                    $json['body'] = htmlspecialchars_decode(wp_htmledit_pre($post->post_content));
                }
            } else {
                $json['error'] = __('Post not found', 'Zanto');
            }
            do_action('zwt_copy_from_original_ajx', $p_id);

            echo json_encode($json);
            break;

        case 'zwt_fetch_trans_posts':
            $required_posts = array();
            $required_posts['blog_id'] = intval($_REQUEST['blog_id']);
            $required_posts['post_type'] = sanitize_key($_REQUEST['post_type']);
            switch_to_blog($required_posts['blog_id']);
            query_posts(array('post_type' => ($required_posts['post_type']), 'posts_per_page' => -1));
            if (have_posts()) : while (have_posts()) : the_post();
                    echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
                endwhile;
            else:
                echo "<option value=''>No Posts Found</option>";
            endif;
            wp_reset_query();
            restore_current_blog();
            break;

        case 'get_lang_info':

            $nonce = $_POST['_wpnonce'];

            if (wp_verify_nonce($nonce, 'wp_lang_get_lang_info')) {

                $ZWT_Download_MO = new ZWT_Download_MO();
                $lang_code_locale = $_POST['lang'];
                $display_lang_name = $zwt_site_obj->modules['trans_network']->get_display_language_name($lang_code_locale, get_locale());

                try {
                    $mo_available_flag = false;
                    $locales = $ZWT_Download_MO->get_locales($lang_code_locale);
                    
                    $link = admin_url('admin.php?page=zwt_manage_locales&switch_to=1&scope=' . $_POST['scope']);
                    ?>
                    <div style="margin: 30px;">
                        <form id="zwt_mo_actions" method="post" action="<?php echo $link; ?>">
                            <?php wp_nonce_field('zwt_mo_actions_nonce_1', 'zwt_mo_interface_1'); ?>
                            <h2>Choose Locale Actions</h2>
                            <?php
                            if (sizeof($locales) > 1) {
                                echo sprintf(__('We found several alternatives for %s translation. Choose which one you want to use:', 'wordpress-language'), $zwt_site_obj->modules['trans_network']->get_display_language_name($lang_code_locale, get_locale()));

                                $default_locale = $lang_code_locale;
                                ?>
                                <br />
                                <ul style="padding:10px">
                                    <?php
                                    foreach ($locales as $locale) {
                                        $checked = $locale == $default_locale ? ' checked="checked"' : '';

                                        echo '<li><label><input type="radio" name="wp_lang_locale[]" value="' . $locale . '"' . $checked . ' > ' . $locale . '</label>';
                                    }
                                    ?>
                                </ul>
                                <?php
                            }
                            else
                                echo '<input type="hidden"  name="switch_to_locale" value="' . $lang_code_locale . '">';
                            ?>


                            <br>
                            <label><input name="zwt_switch_lang" type="checkbox">&nbsp;<?php _e('Switch language to','Zanto');  echo ' ', $display_lang_name; ?> </label>
                            <br>
                            <label><input name="zwt_download_mo" checked="checked" type="checkbox">&nbsp;<?php _e('Download','Zanto'); echo ' ', $display_lang_name, ' '; _e('Admin wordpress .mo file','Zanto')?></label>
                            <br>
          
                            <input type="hidden"  name="current_scope" value="<?php echo $_POST['scope'] ?>">
                            <br/><br/><br/>

                            <?php
                        } catch (Exception $e) {
                            ?>
                            <span style="color:#f00" ><?php echo $e->getMessage() ?></span>					
                            <a class="button-secondary" href="#" onclick="tb_remove();jQuery('#wp_lang_switch_form').html(original_lang_switch_form);return false;"><?php echo __('Cancel', 'Zanto'); ?></a>
                            <?php
                            die();
                        }
                        ?>

                        <input class="button-primary"  name="interface_1_mo" value="<?php echo __('Submit', 'Zanto') ?>" type="submit"  />

                        <a class="button-secondary" href="#" onclick="tb_remove();jQuery('#wp_lang_switch_form').html(original_lang_switch_form);return false;"><?php echo __('Cancel', 'Zanto'); ?></a>
                    </form>
                </div>

                <?php
            }
            die();

            break;
        //@todo delete
        case 'ajax_install_language':
            $nonce = $_POST['_wpnonce'];
            if (wp_verify_nonce($nonce, 'wp_lang_get_lang_info')) {
                $_POST['action'] = 'wp_handle_sideload';
                $ZWT_Download_MO = new ZWT_Download_MO();
                $ZWT_Download_MO->get_translation_files();

                if (isset($_POST['scope']) && $_POST['scope'] == 'front-end') {
                    $current_locale = get_option('WPLANG');
                } else {
                    $current_locale = get_locale();
                }

                if ($current_locale == 'en_US') {
                    echo '1';
                    exit;
                }

                $current_lang_code = $zwt_site_obj->modules['trans_network']->get_lang_code($current_locale);
                $translations = $ZWT_Download_MO->get_translations($current_locale);

                if ($translations !== false) {
                    echo '1';
                } else {
                    echo '0';
                }
            }
            die();
            break;
			
        case 'flag_url_change':
            $nonce = $_POST['_wpnonce'];
            if (wp_verify_nonce($nonce, 'zwt_custom_flag')) {
			if (strpos($_POST['flag_url'],get_bloginfo('template_url')) !== false  && in_array($_POST['flag_ext'], array('png','jpg','gif'))) {
			     $flag_url= $_POST['flag_url'];
				 $flag_ext= $_POST['flag_ext'];
		       	ZWT_Settings::save_setting('settings', array('lang_switcher' => 
                array(
                    'custom_flag_url' => $flag_url
                    )));
					
				ZWT_Settings::save_setting('settings', array('lang_switcher' => 
                array(
                    'custom_flag_ext' => $flag_ext
                    )));
					
                _e('Success','Zanto');
              }elseif($_POST['flag_url']==-1){
			         ZWT_Settings::save_setting('settings', array('lang_switcher' => 
                array(
                    'custom_flag_url' =>0
                    )));
                _e('Success! Default flags will be used','Zanto');
			  }else
			    _e('Operation was not successfull','Zanto');
            }

            die();

            break;		
			

        case 'mo_check_for_updates':
            $nonce = $_POST['_wpnonce'];
            if (wp_verify_nonce($nonce, 'wp_lang_get_lang_info')) {
                $ZWT_Download_MO = new ZWT_Download_MO();
                $ZWT_Download_MO->updates_check();
                $wptranslations = $ZWT_Download_MO->get_option('translations');

                if ($_POST['scope'] == 'front-end') {
                    $current_locale = get_option('WPLANG');
                } else {
                    $current_locale = get_locale();
                }

                $current_lang_code = $zwt_site_obj->modules['trans_network']->get_lang_code($current_locale);
                $contents = ob_get_contents();
            }

            die();

            break;

        case 'ajax_show_hide_language_selector':

            $nonce = $_POST['_wpnonce'];
            if (wp_verify_nonce($nonce, 'wp_lang_get_lang_info')) {
                if ($_POST['state'] == 'on') {
                    update_option('wp_language_show_switcher', 'on');
                } else {
                    update_option('wp_language_show_switcher', 'off');
                }
            }

            die();

            break;

        case 'zwt_reset_cache':
            check_ajax_referer('zwt-advanced-tools', '_wpnonce');

            if (isset($_POST['cacheType']) && in_array($_POST['cacheType'], array(1, 2, 3))) {
                if ($_POST['cacheType'] == 1) {
                    $blog_trans_cache = new zwt_cache('translation_network', true);
                    $blog_trans_cache->clear();
                } elseif ($_POST['cacheType'] == 2) {
                    $locale_cache = new zwt_cache('locale_code', true);
                    $lang_name_cache = new zwt_cache('lang_name', true);
                    $locale_cache->clear();
                    $lang_name_cache->clear();
                } elseif ($_POST['cacheType'] == 3) {
                    delete_option('_zwt_cache');
                }
                $zwt_site_obj->clearCachingPlugins();

                echo '<span class="success">' . __('Cache reset', 'Zanto') . ' </span>';
            } else {
                echo '<span class="fail">' . __(' Invalid value supplied!', 'Zanto') . '</span>';
            }
            die();
            break;

        case 'zwt_copy_taxonomy':
            check_ajax_referer('zwt-advanced-tools', '_wpnonce');
            if (isset($_POST['fromBlog']) && intval($_POST['fromBlog']) && isset($_POST['taxonomy'])) {
                $transnet_blogs = $zwt_site_obj->modules['trans_network']->transnet_blogs;
                $clean = false;
                foreach ($transnet_blogs as $transblog) {
                    if ($_POST['fromBlog'] == $transblog['blog_id']) {
                        $clean = true;
                        break;
                    }
                }
                if (!$clean) {
                    echo '<span class="fail">' . __('Invalid value Suplied!', 'Zanto') . '</span>';
                    die();
                }

                if ($blog_id != $_POST['fromBlog']) {
                    $from_blog = $_POST['fromBlog'];
                    $source_tax_meta = get_blog_option($_POST['fromBlog'], 'zwt_taxonomy_meta');
                    $tax_name = $_POST['taxonomy'];
                    if (isset($source_tax_meta[$tax_name])) {
                        $tax_array = $source_tax_meta[$tax_name];
                        $imported_tax = array();
                        foreach ($tax_array as $source_term_id => $term_translations) {

                            foreach ($term_translations as $c_blog_id => $term_id) {
                                if ($c_blog_id == $blog_id) { // get blog and term id from translations of source blog
                                    $c_term_id = $term_id;
                                } else {   //the rest of the term translations make the new term translations
                                    $new_translations[$c_blog_id] = $term_id;
                                }
                            }

                            if (isset($c_term_id)) {
                                $new_translations[$from_blog] = $source_term_id; // add this source blog term to translations
                                $imported_tax[$c_term_id] = $new_translations;
                                unset($c_term_id);
                                unset($new_translations);
                            }
                        }
                    }

                    if (!empty($imported_tax)) {
                        $blog_tax_meta = get_option('zwt_taxonomy_meta');
                        if (isset($blog_tax_meta[$tax_name])) {
                            $blog_tax_array = $blog_tax_meta[$tax_name];
                        } else { // a case where no term exists as translated
                            $blog_tax_array = array();
                        }
                        $new_tax_array = empty($blog_tax_array) ? $imported_tax : array_replace_recursive($blog_tax_array, $imported_tax);
                        $blog_tax_meta[$tax_name] = $new_tax_array;
                        update_option('zwt_taxonomy_meta', $blog_tax_meta, 99);
                        echo '<span class="success">' . __(' Import successful!', 'Zanto') . '</span>';
                    } else {
                        echo '<span class="fail">' . __('There was nothing to import!', 'Zanto') . '</span>';
                    }
                }
            }
            die();
            break;

        case 'zwt_reset_zanto':
            check_ajax_referer('zwt-advanced-tools', '_wpnonce');
            $defaults = ZWT_Settings::getDefaultSettings();
            delete_option(ZWT_Base::PREFIX . 'zanto_settings');
            $update = update_option(ZWT_Base::PREFIX . 'zanto_settings', $defaults);
            ZWT_Settings::save_setting('settings', array('setup_status' =>
                array(
                    'setup_wizard' => 'complete',
                    'setup_interface' => 'four'
                    )));
            if ($update) {
                echo '<span class="success">' . __('Recet successful!', 'Zanto') . '</span>';
            } else {
                echo '<span class="fail">' . __('Recet failed!', 'Zanto') . '</span>';
            }
            die();
            break;

        case 'remove_trans_site':
            check_ajax_referer('zwt_update_transnetwork_nonce', '_wpnonce');
            if (isset($_POST['blog_id']) && $d_blog_id = intval($_POST['blog_id'])) {// used = to assign d_blog $_POST value
                global $wpdb, $site_id;


                if (get_current_blog_id() == $d_blog_id) {

                    ZWT_Base::$notices->enqueue('You are not allowed to change the current site', 'error');
                    die();
                }
                switch_to_blog($d_blog_id);
                $transnet_id = $zwt_site_obj->modules['trans_network']->get_trans_id(true);
                $transnet_blogs = $zwt_site_obj->modules['trans_network']->get_transnet_blogs(true);

                if ($wpdb->delete($wpdb->base_prefix . 'zwt_trans_network', array('blog_id' => $d_blog_id), array('%d'))) {
                    ZWT_Base::$notices->enqueue('Zanto Trans Network was successfuly updated');
					ZWT_Settings::save_setting('settings', array('setup_status' =>
                                                             array(
                                                                'setup_wizard' => 'incomplete',
                                                                'setup_interface' => 'two'
                                                           )));
					
                } else { //@todo make it persistent
                    ZWT_Base::$notices->enqueue('There was an error updating the Trans Network table', 'error');
					die();
                }

				if (count( $transnet_blogs) < 2) {
				     if(!$wpdb->delete($wpdb->base_prefix . 'usermeta', array('meta_key' => 'zwt_installed_transnetwork', 'meta_value' => $transnet_id ), array('%s','%d'))){
			             ZWT_Base::$notices->enqueue('There was an error deleting the zwt_trans_network value from usermeta table','error');
			          }
					  
					 // delete zwt_network_vars from site meta
				 }
				
                if ($zwt_site_obj->modules['trans_network']->get_primary_lang(true) == $d_blog_id) {
					delete_metadata('site', $site_id, 'zwt_'.$transnet_id.'_network_vars');
                }

                $zwt_global_cache = get_metadata('site', $site_id, 'zwt_'.$transnet_id.'_site_cache', true);
                if (isset($zwt_global_cache[$d_blog_id])) {
                    unset($zwt_global_cache[$d_blog_id]);
                    update_metadata('site', $site_id, 'zwt_'.$transnet_id.'_site_cache', $zwt_global_cache);
                }
				
                zwt_clean_blog_tax($d_blog_id);
				
                restore_current_blog();

                foreach ($transnet_blogs as $trans_blog) {
                    if ($trans_blog == $d_blog_id)
                        continue;
                    switch_to_blog($trans_blog['blog_id']);
                    $c_trans_net_cache = new zwt_cache('translation_network', true);
                    $c_trans_net_cache->clear();
					zwt_clean_blog_tax($d_blog_id);
                    restore_current_blog();
                }
                echo 'success';
            }
            die();
            break;

        default:
            $output = 'No function specified, check your jQuery.ajax() call';
            break;
    }
} elseif (isset($_REQUEST['public_fn'])) {

    switch ($_REQUEST['public_fn']) {
        case 'get_browser_language':

            $browser_langs = explode(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $browser_lang = explode("-", $browser_langs[0]);
            echo isset($browser_lang[1]) ? $browser_lang[0] . '_' . strtoupper($browser_lang[1]) : $browser_lang[0];
            die();
            break;

        default:
            $output = 'No function specified, check your jQuery.ajax() call';
            break;
    }
}

die();