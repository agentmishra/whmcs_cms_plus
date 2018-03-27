<?php

use Illuminate\Database\Capsule\Manager as Capsule;

add_hook('cms/deactivate', 1, 'cms_settings_deactivate');
function cms_settings_deactivate($vars){
	if($vars['delete_tables'] == 1){
		logActivity('Settings Deleted');
		delete_query('tbladdonmodules', array('module' => 'cms_settings'));
	}//if
	return true;
}

add_hook('cms/admin/page/js', 1, 'cms_settings_js');
function cms_settings_js($vars){
	?>
	if($('#settings #field-comments').val() != 'disqus'){
        $('#tr-field-disqus_comments').hide();
    }

    if($('#settings #field-comments').val() != 'facebook'){
        $('#tr-field-facebook_comments_limit, #tr-field-facebook_comments_style').hide();
    }


    $('#settings #field-comments').change(function(){
        if($(this).val() == 'disqus'){
            $('#tr-field-disqus_comments').fadeIn();
            $('#tr-field-facebook_comments_limit, #tr-field-facebook_comments_style').fadeOut();
        }else if($(this).val() == 'facebook'){
            $('#tr-field-disqus_comments').fadeOut();
            $('#tr-field-facebook_comments_limit, #tr-field-facebook_comments_style').fadeIn();
        }else{
            $('#tr-field-disqus_comments,#tr-field-facebook_comments_limit, #tr-field-facebook_comments_style').fadeOut();
        }
    });
    <?php
}
add_hook('cms/admin/page', 200, 'cms_settings_admin_page');
function cms_settings_admin_page($vars){
	return array(
		'settings' => $vars['_lang']['view_settings'],
	);
}

add_hook('cms/admin/page/settings', 1, 'cms_settings_admin_page_html');
function cms_settings_admin_page_html($vars){


	ob_start();

	$values = array();
	$settings = Capsule::table('tbladdonmodules')->where('module', 'cms_settings')->get();
	foreach($settings as $setting){
        $setting = json_decode(json_encode($setting), true);
		$values[$setting['setting']] = $setting['value'];
	}

	$pages = array();
	$query = Capsule::table('mod_whmcs_cms_plus_content')->where('type', 'page')->get();
	foreach($query as $page){
		$pages[$page->id] = $page->title;
	}

	$fields = array(
		'404_page' => array(
			'type' => 'select',
			'title' => $vars['_lang']['settings_404_page'],
			'desc' => $vars['_lang']['settings_404_page_desc'],
			'options' => $pages
		),
		'comments' => array(
			'type' => 'select',
			'title' => $vars['_lang']['settings_comments'],
			'desc' => $vars['_lang']['settings_comments_desc'],
			'options' => array(
				'none' => $vars['_lang']['settings_comments_none'],
				'disqus' => $vars['_lang']['settings_comments_disqus'],
				'facebook' => $vars['_lang']['settings_comments_facebook']
			),
		),
		'disqus_comments' => array(
			'type' => 'text',
			'title' => $vars['_lang']['settings_disqus_comments'],
		),
		'facebook_comments_limit' => array(
			'type' => 'text',
			'title' => $vars['_lang']['settings_facebook_comments_limit'],
		),
		'facebook_comments_style' => array(
			'type' => 'select',
			'title' => $vars['_lang']['settings_facebook_comments_style'],
			'desc' => $vars['_lang']['settings_facebook_comments_style_desc'],
			'options' => array(
				'light' => $vars['_lang']['settings_facebook_comments_style_light'],
				'dark' => $vars['_lang']['settings_facebook_comments_style_dark']
			),
		),
		'css' => array(
			'type' => 'textarea',
			'ace' => 'css',
			'title' => $vars['_lang']['settings_css'],
		),
		'delete_tables' => array(
			'type' => 'checkbox',
			'title' => $vars['_lang']['settings_delete_tables'],
			'checkbox_desc' => $vars['_lang']['settings_delete_tables_desc']
		),
	);

	if(!empty($_POST['postaction']) && $_POST['postaction'] == 'save_settings'){
		foreach($fields as $key => $field){
			switch($field['type']){
				case 'checkbox':
					$var = (isset($_POST['settings'][$key])) ? 1 : 0;
					$values[$key] = $var;
				break;
				default:
					$var = $_POST['settings'][$key];
					$values[$key] = $_POST['settings'][$key];
			}
			delete_query('tbladdonmodules', array('module' => 'cms_settings', 'setting' => $key));
			insert_query('tbladdonmodules', array('module' => 'cms_settings', 'setting' => $key, 'value' => $var));
		}

		echo '<div class="successbox"><strong><span class="title">'.$vars['_lang']['settings_saved'].'</span></strong></div>';
	}

	echo cms_display_form($fields, $values, $vars['modulelink'].'&tab=settings', $vars, array('postaction' => 'save_settings'));

	return ob_get_clean();

}