<?php

function get_trans_network_admin($trans_id) {
    global $wpdb;
    $trans_owner_array = $wpdb->get_results("SELECT user_id,  meta_value FROM wp_usermeta WHERE meta_key =  'zwt_installed_transnetwork'", ARRAY_A);
    foreach ($trans_owner_array as $trans_owner) {
        if ($trans_owner['meta_value'] == $trans_id) {
            $admin_id = $trans_owner['user_id'];
            break;
        }
    }
    return $user_details = get_userdata($admin_id);
}

function noscript_notice() {
    ?>
    <noscript>
    <div class="error">
        <?php echo __('This Zanto admin screen requires JavaScript in order to display properly. JavaScript is currently off in your browser!', 'Zanto') ?>
    </div>
    </noscript>
    <?php
}
function check_internet_connection($sCheckHost = 'www.google.com') 
{
    return (bool) @fsockopen($sCheckHost, 80, $iErrno, $sErrStr, 5);
}

function zwt_add_metadata($meta_type, $object_id, $meta_key, $meta_value, $c_blog_id, $unique = false) {
    if (!$meta_type || !$meta_key)
        return false;

    if (!$object_id = absint($object_id))
        return false;

    if (!$table = _zwt_get_meta_table($meta_type, $c_blog_id))
        return false;

    global $wpdb;

    $column = esc_sql($meta_type . '_id');

// expected_slashed ($meta_key)
    $meta_key = stripslashes($meta_key);
    $meta_value = stripslashes_deep($meta_value);
    $meta_value = sanitize_meta($meta_key, $meta_value, $meta_type);

    $check = apply_filters("zwt_add_{$meta_type}_metadata", null, $object_id, $meta_key, $meta_value, $unique);
    if (null !== $check)
        return $check;

    if ($unique && $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM $table WHERE meta_key = %s AND $column = %d", $meta_key, $object_id)))
        return false;

    $_meta_value = $meta_value;
    $meta_value = maybe_serialize($meta_value);

    do_action("zwt_add_{$meta_type}_meta", $object_id, $meta_key, $_meta_value);

    $result = $wpdb->insert($table, array(
        $column => $object_id,
        'meta_key' => $meta_key,
        'meta_value' => $meta_value
            ));


    if (!$result)
        return false;

    $mid = (int) $wpdb->insert_id;

    wp_cache_delete($object_id, $meta_type . '_meta');

    do_action("zwt_added_{$meta_type}_meta", $mid, $object_id, $meta_key, $_meta_value);

    return $mid;
}

function zwt_get_site_links($trans_id) {
    if (did_action('admin_init') !== 1)
        return;
    global $site_id, $blog_id;
    $update_flag = 0;
	$home_url = zwt_home_url();
    $zwt_global_cache = get_metadata('site', $site_id, 'zwt_' . $trans_id . '_site_cache', true);
    if (isset($zwt_global_cache[$blog_id])) {
        if ($zwt_global_cache[$blog_id]['site_url'] != $home_url) {
            $zwt_global_cache[$blog_id]['site_url'] = $home_url;
            $update_flag = 1;
        }
        if ($zwt_global_cache[$blog_id]['admin_url'] != admin_url()) {
            $zwt_global_cache[$blog_id]['admin_url'] = admin_url();
            $update_flag = 1;
        }
        if ($update_flag) {
            $zwt_global_cache[$blog_id]['site_url'] = $home_url;
            $zwt_global_cache[$blog_id]['admin_url'] = admin_url();
            update_metadata('site', $site_id, 'zwt_' . $trans_id . '_site_cache', $zwt_global_cache);
            ZWT_Base::$notices->enqueue('blog meta options created and updated in global catche');
        }
    } else { //first time creation of global cache for this blog
        $zwt_global_cache[$blog_id]['site_url'] = $home_url;
        $zwt_global_cache[$blog_id]['admin_url'] = admin_url();
		$zwt_global_cache[$blog_id]['lang_url_format'] = 0;
        update_metadata('site', $site_id, 'zwt_' . $trans_id . '_site_cache', $zwt_global_cache);
    }
}
function zwt_home_url(){
    if(function_exists('domain_mapping_siteurl')){// support for domain mapping
	     return domain_mapping_siteurl( false );
	}else{
        return home_url();
	}
}
function zwt_add_links($blog_id, $trans_id, $lang_url_format) {
    if (!absint($blog_id) || !absint($trans_id))
        return;
    global $site_id;

    $home_url=zwt_home_url();
    $zwt_global_cache = get_metadata('site', $site_id, 'zwt_' . $trans_id . '_site_cache', true);
    $zwt_global_cache[$blog_id]['site_url'] = $home_url;
    $zwt_global_cache[$blog_id]['admin_url'] = admin_url();
	$zwt_global_cache[$blog_id]['lang_url_format'] = $lang_url_format;
    update_metadata('site', $site_id, 'zwt_' . $trans_id . '_site_cache', $zwt_global_cache);
}

function zwt_update_metadata($meta_type, $object_id, $meta_key, $meta_value, $c_blog_id, $prev_value = '') {
    if (!$meta_type || !$meta_key)
        return false;

    if (!$object_id = absint($object_id))
        return false;

    if (!$table = _zwt_get_meta_table($meta_type, $c_blog_id))
        return false;

    global $wpdb;

    $column = esc_sql($meta_type . '_id');
    $id_column = 'user' == $meta_type ? 'umeta_id' : 'meta_id';

// expected_slashed ($meta_key)
    $meta_key = stripslashes($meta_key);
    $passed_value = $meta_value;
    $meta_value = stripslashes_deep($meta_value);
    $meta_value = sanitize_meta($meta_key, $meta_value, $meta_type);

    $check = apply_filters("zwt_update_{$meta_type}_metadata", null, $object_id, $meta_key, $meta_value, $prev_value);
    if (null !== $check)
        return (bool) $check;


// Compare existing value to new value if no prev value given and the key exists only once.
    if (empty($prev_value)) {
        $old_value = get_metadata($meta_type, $object_id, $meta_key);
        if (count($old_value) == 1) {
            if ($old_value[0] === $meta_value)
                return false;
        }
    }


    if (!$meta_id = $wpdb->get_var($wpdb->prepare("SELECT $id_column FROM $table WHERE meta_key = %s AND $column = %d", $meta_key, $object_id)))
        return zwt_add_metadata($meta_type, $object_id, $meta_key, $passed_value, $c_blog_id);

    $_meta_value = $meta_value;
    $meta_value = maybe_serialize($meta_value);

    $data = compact('meta_value');
    $where = array($column => $object_id, 'meta_key' => $meta_key);

    if (!empty($prev_value)) {
        $prev_value = maybe_serialize($prev_value);
        $where['meta_value'] = $prev_value;
    }

    do_action("zwt_update_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value);

    if ('post' == $meta_type)
        do_action('zwt_update_postmeta', $meta_id, $object_id, $meta_key, $meta_value);

    $wpdb->update($table, $data, $where);

    wp_cache_delete($object_id, $meta_type . '_meta');

    do_action("zwt_updated_{$meta_type}_meta", $meta_id, $object_id, $meta_key, $_meta_value);

    if ('post' == $meta_type)
        do_action('zwt_updated_postmeta', $meta_id, $object_id, $meta_key, $meta_value);

    return true;
}

function _zwt_get_meta_table($type, $c_blog_id) {
    global $wpdb;
    $blog_prefix = $wpdb->get_blog_prefix($c_blog_id);
    $table_name = $blog_prefix . $type . 'meta';

    if (empty($table_name))
        return false;

    return $table_name;
}

function zwt_get_post_select_options($attr) {
    global $blog_id;
    $defaults = array(
        'c_blog' => $blog_id, // blog id to check in
        'post_type' => '',
        'order' => 'ASC',
        'orderby' => 'title',
        'posts_per_page' => -1,
        'selected' => '', // the id of post marked as checked
        'include' => '',
        'exclude' => '',
        'empty_option' => true     // if an empty option should start the select
    );

    $options = shortcode_atts($defaults, $attr);
    switch_to_blog($options['c_blog']);
    $loop = new WP_Query($options);
    if ($options['empty_option'])
        $output = "<option value=''></option>";

    if ($loop->have_posts()) {
        while ($loop->have_posts()) {
            $loop->the_post();
			$translations = get_post_meta(get_the_ID(), ZWT_Base::PREFIX . 'post_network', true);
			$trans_class="";
			if(is_array($translations))
			foreach($translations as $translation){
			 if(isset($translation['blog_id']) && $translation['blog_id']==$blog_id)
			 $trans_class="translated";
			}
			
            $output .= the_title('<option class="'.$trans_class.'" value="' . get_the_ID() . '"' . selected($options['selected'], get_the_ID()) . ' > ', ' </option>', false);
		}
    } else {
        $output = '<option value="">' . __('No Posts Found', 'Zanto') . '</option>';
    }
    restore_current_blog();
    return $output;
}

function zwt_broadcast_post_network($post_transnetwork) {
    foreach ($post_transnetwork as $index => $pnet_details) {
        if (isset($pnet_details['post_id']))
            zwt_update_metadata('post', $pnet_details['post_id'], ZWT_Base::PREFIX . 'post_network', $post_transnetwork, $pnet_details['blog_id']);
    }
}

/* Removes an individual post from the post network */

function zwt_detach_post($blog_id, $post_transnetwork) {
    if (!is_numeric($blog_id) || !is_array($post_transnetwork)) {
        ZWT_Base::$notices->enqueue('Wrong data received by zwt_detach_post() ', 'error');
        return;
    }
    foreach ($post_transnetwork as $index => $pnet_details) {
        if ($pnet_details['blog_id'] == $blog_id) {
            unset($post_transnetwork[$index]);
            if (isset($pnet_details['post_id']))
                zwt_update_metadata('post', $pnet_details['post_id'], ZWT_Base::PREFIX . 'post_network', '', $blog_id);
        }
    }
    if (count($post_transnetwork) < 2) {
        foreach ($post_transnetwork as $index => $pnet_details)
            zwt_update_metadata('post', $pnet_details['post_id'], ZWT_Base::PREFIX . 'post_network', '', $pnet_details['blog_id']);
    } else {
        zwt_broadcast_post_network($post_transnetwork);
    }
}

/*
  $obj_type is the object type e.g post, taxonomy, $obj_blog is the blog id of the object type,
  $obj_id is the object id e.g post id for posts
 */

function zwt_get_global_urls_info($req_info, $blog_id) {
    global $site_id, $zwt_site_obj;
    $transnet_id = $zwt_site_obj->modules['trans_network']->transnet_id;
    $blog_parameters = get_metadata('site', $site_id, 'zwt_' . $transnet_id . '_site_cache', true);
    if (!isset($blog_parameters[$blog_id]))
        return;
    switch ($req_info) {
        case 'site_url':
            $info = $blog_parameters[$blog_id]['site_url'];
            if (!$info || $info == '') {
                switch_to_blog($blog_id);
                $info = get_option('siteurl');
                zwt_get_site_links($transnet_id);
                restore_current_blog();
            }
            break;
        case 'admin_url':
            $info = $blog_parameters[$blog_id]['admin_url'];
            if (!$info || $info == '') {
                switch_to_blog($blog_id);
                $info = admin_url();
                zwt_get_site_links($transnet_id);
                restore_current_blog();
            }
            break;
		case 'lang_url_format':
            $info = $blog_parameters[$blog_id]['lang_url_format'];
            break;	
        default:
            return false;
    }
    return $info;
}

function zwt_get_trans_url($obj_type, $obj_blog, $obj_id) {
    global $site_id, $wpdb;
    if (isset($obj_type))
        switch ($obj_type) {
            case 'post':
                $trans_link = zwt_get_global_urls_info('site_url', $obj_blog) . '?p=' . $obj_id;
                return $trans_link;
                break;
            case 'category':
                $trans_link = zwt_get_global_urls_info('site_url', $obj_blog) . '?cat=' . $obj_id;
                return $trans_link;
            case 'post_tag':
                $b_prefix = $wpdb->get_blog_prefix($obj_blog);
                $term_slug = $wpdb->get_var($wpdb->prepare("SELECT slug FROM {$b_prefix }terms WHERE term_id = %d", $obj_id));
                $trans_link = zwt_get_global_urls_info('site_url', $obj_blog) . '?tag=' . $term_slug;
                return $trans_link;
                break;
            default:
                $b_prefix = $wpdb->get_blog_prefix($obj_blog);
                $term_slug = $wpdb->get_var($wpdb->prepare("SELECT slug FROM {$b_prefix }terms  WHERE term_id = %d", $obj_id));
                $trans_link = zwt_get_global_urls_info('site_url', $obj_blog) . '?' . $obj_type . '=' . $term_slug;
                return $trans_link;
                break;
        }
}

function zwt_merge_atts($pairs, $atts) {
    $atts = (array) $atts;
    $out = array();
    foreach ($pairs as $name => $default) {
        if (array_key_exists($name, $atts)) {
            $atts = array_shift($atts);
            foreach ($atts as $attr_key => $attr_value)
                foreach ($default as $key => $value) {
                    if ($attr_key == $key)
                        $default[$key] = $attr_value;
                }
        }

        $out[$name] = $default;
    }
    return $out;
}

/*This function is used to construct flag image html elements for the backend end*/

function zwt_get_flag($locale) {
    $flag = '<img src="' . GTP_PLUGIN_URL . 'images/flags/' . $locale . '.png" width="16" height="11" alt="' . $locale . '" />';
    do_action('zwt_get_locale_flag', $locale, $flag);
    return apply_filters('zwt_get_flag', $flag, $locale);
}

/*This function is used to retrieve flag urls for the front end*/
function zwt_get_site_flags($locale){
    global $zwt_site_obj;
    $custom_url=$zwt_site_obj->modules['settings']->settings['lang_switcher']['custom_flag_url'];
	$flag_ext=$zwt_site_obj->modules['settings']->settings['lang_switcher']['custom_flag_ext'];
    if($custom_url !==0){
	    $flag_url= $custom_url.'/' . $locale .'.'. $flag_ext;
    }
    else{
         $flag_url= GTP_PLUGIN_URL . 'images/flags/' . $locale . '.png';
    }
	return apply_filters('zwt_front_flag', $flag_url, $locale);
}

/* removes taxonomies of deleted blogs
 * takes deleted blog ID as a parameter */

function zwt_clean_blog_tax($deleted_blog) {
    $tax_meta = get_option('zwt_taxonomy_meta');

    foreach ($tax_meta as $tax => $t_array) {
        foreach ($t_array as $term => $blog_tax) {
            if (isset($blog_tax[$deleted_blog])) {
                unset($tax_meta[$tax][$term][$deleted_blog]);
            }
            if (empty($tax_meta[$tax][$term])) {
                unset($tax_meta[$tax][$term]);
            }
        }
    }
    update_option('zwt_taxonomy_meta', $tax_meta);
    return;
}

function zwt_network_vars($trans_net_id, $action, $element, $value=null) {
    global $blog_id, $switched, $site_id;
    switch ($action) {
        case 'get':
            $network_vars = get_metadata('site', $site_id, 'zwt_' . $trans_net_id . '_network_vars', true);
            if (isset($network_vars[$element])) {
                return $network_vars[$element];
            } else {
                return false;
            }

            break;

        case 'update':
            if ($value == null) {
                ZWT_Base::$notices->enqueue('No value received by zwt_network_vars() ', 'error');
                return;
            }
            $network_vars = get_metadata('site', $site_id, 'zwt_' . $trans_net_id . '_network_vars', true);
            $network_vars[$element] = $value;
            update_metadata('site', $site_id, 'zwt_' . $trans_net_id . '_network_vars', $network_vars);
            break;
    }
}

/**
 * gzdecode implementation
 *
 * @see http://hu.php.net/manual/en/function.gzencode.php#44470
 * 
 * @param string $data
 * @param string $filename
 * @param string $error
 * @param int $maxlength
 * @return string
 */
function zwt_gzdecode($data, &$filename = '', &$error = '', $maxlength = null) {
    $len = strlen($data);
    if ($len < 18 || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
        $error = "Not in GZIP format.";
        return null; // Not GZIP format (See RFC 1952)
    }
    $method = ord(substr($data, 2, 1)); // Compression method
    $flags = ord(substr($data, 3, 1)); // Flags
    if ($flags & 31 != $flags) {
        $error = "Reserved bits not allowed.";
        return null;
    }
    // NOTE: $mtime may be negative (PHP integer limitations)
    $mtime = unpack("V", substr($data, 4, 4));
    $mtime = $mtime [1];
    $xfl = substr($data, 8, 1);
    $os = substr($data, 8, 1);
    $headerlen = 10;
    $extralen = 0;
    $extra = "";
    if ($flags & 4) {
        // 2-byte length prefixed EXTRA data in header
        if ($len - $headerlen - 2 < 8) {
            return false; // invalid
        }
        $extralen = unpack("v", substr($data, 8, 2));
        $extralen = $extralen [1];
        if ($len - $headerlen - 2 - $extralen < 8) {
            return false; // invalid
        }
        $extra = substr($data, 10, $extralen);
        $headerlen += 2 + $extralen;
    }
    $filenamelen = 0;
    $filename = "";
    if ($flags & 8) {
        // C-style string
        if ($len - $headerlen - 1 < 8) {
            return false; // invalid
        }
        $filenamelen = strpos(substr($data, $headerlen), chr(0));
        if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
            return false; // invalid
        }
        $filename = substr($data, $headerlen, $filenamelen);
        $headerlen += $filenamelen + 1;
    }
    $commentlen = 0;
    $comment = "";
    if ($flags & 16) {
        // C-style string COMMENT data in header
        if ($len - $headerlen - 1 < 8) {
            return false; // invalid
        }
        $commentlen = strpos(substr($data, $headerlen), chr(0));
        if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
            return false; // Invalid header format
        }
        $comment = substr($data, $headerlen, $commentlen);
        $headerlen += $commentlen + 1;
    }
    $headercrc = "";
    if ($flags & 2) {
        // 2-bytes (lowest order) of CRC32 on header present
        if ($len - $headerlen - 2 < 8) {
            return false; // invalid
        }
        $calccrc = crc32(substr($data, 0, $headerlen)) & 0xffff;
        $headercrc = unpack("v", substr($data, $headerlen, 2));
        $headercrc = $headercrc [1];
        if ($headercrc != $calccrc) {
            $error = "Header checksum failed.";
            return false; // Bad header CRC
        }
        $headerlen += 2;
    }
    // GZIP FOOTER
    $datacrc = unpack("V", substr($data, - 8, 4));
    $datacrc = sprintf('%u', $datacrc [1] & 0xFFFFFFFF);
    $isize = unpack("V", substr($data, - 4));
    $isize = $isize [1];
    // decompression:
    $bodylen = $len - $headerlen - 8;
    if ($bodylen < 1) {
        // IMPLEMENTATION BUG!
        return null;
    }
    $body = substr($data, $headerlen, $bodylen);
    $data = "";
    if ($bodylen > 0) {
        switch ($method) {
            case 8 :
                // Currently the only supported compression method:
                $data = gzinflate($body, $maxlength);
                break;
            default :
                $error = "Unknown compression method.";
                return false;
        }
    } // zero-byte body content is allowed
    // Verifiy CRC32
    $crc = sprintf("%u", crc32($data));
    $crcOK = $crc == $datacrc;
    $lenOK = $isize == strlen($data);
    if (!$lenOK || !$crcOK) {
        $error = ($lenOK ? '' : 'Length check FAILED. ') . ($crcOK ? '' : 'Checksum FAILED.');
        return false;
    }
    return $data;
}
