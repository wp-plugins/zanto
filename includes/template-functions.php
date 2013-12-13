<?php
/**
 * Get all available switcher themes on the presence of *.zwt.php files in a given directory. The default directory is The Wordpress site theme directory.
 *
 * @since 3.0.0
 *
 * @param string $dir A directory in which to search for lang switcher theme files. The default directory is the theme's directory from get_template_directory()
 * @return array Array of language switcher themes or an empty array if no themes are present. Language switcher theme names are formed by stripping the .zwt.php extension from the theme file names.
 */
function zwt_get_ls_themes( $dir = null ) {
	$themes = array();

	foreach( (array)glob( ( is_null( $dir) ? GTP_lS_THEME_PATH : $dir ) . '/*.zwt.php' ) as $theme_file ) {
		$theme_file = basename($theme_file, '.zwt.php');
		if ( 0 !== strpos( $theme_file, 'continents-cities' ) && 0 !== strpos( $theme_file, 'ms-' ) &&
			0 !== strpos( $theme_file, 'admin-' ))
			$themes[] = $theme_file;
	}

	return $themes;
}


function zwt_disp_language($native_name, $translated_name,
        $lang_native_hidden = false, $lang_translated_hidden = false) {
    if (!$native_name && !$translated_name) {
        $ret = '';
    } elseif ($native_name && $translated_name) {
        $hidden1 = $hidden2 = $hidden3 = '';
        if ($lang_native_hidden) {
            $hidden1 = 'style="display:none;"';
        }
        if ($lang_translated_hidden) {
            $hidden2 = 'style="display:none;"';
        }
        if ($lang_native_hidden && $lang_translated_hidden) {
            $hidden3 = 'style="display:none;"';
        }

        if ($native_name != $translated_name) {
            $ret = '<span ' . $hidden1 . ' class="zwt_lang_sel_native">' . $native_name .'</span> 
			<span ' . $hidden2 . ' class="zwt_lang_sel_translated"> 
			  ('. $translated_name .') 
		   </span>';
        } else {
            $ret = '<span ' . $hidden3 . ' class="zwt_lang_sel_current">' . $native_name . '</span>';
        }
    } elseif ($native_name) {
        $ret = $native_name;
    } elseif ($translated_name) {
        $ret = $translated_name;
    }

    return $ret;
}
function zwt_register_switcher_types($types){
    foreach($types as $type=>$description){
        if(preg_match('/[^a-z_\-0-9]/i', $type))
            {
                return false;
            }
    }
	global $zwt_ls_types;
    $zwt_ls_types = $types;
}

// args:
// skip_missing (0|1|true|false)

function zwt_get_languages($a='') {
    if ($a) {
        parse_str($a, $args);
    } else {
        $args = '';
    }
    global $zwt_language_switcher;
    $langs = $zwt_language_switcher->get_current_ls($args);
    return $langs;
}