<?php
add_hook('cms/admin/page', 300, 'cms_help_admin_page');
function cms_help_admin_page($vars){
	return array(
		'help' => $vars['_lang']['help'],
	);
}

add_hook('cms/admin/page/help', 1, 'cms_help_admin_page_html');
function cms_help_admin_page_html($vars){
	return $vars['_lang']['help_text'];
}

add_hook('cms/admin/page/js', 1, 'cms_help_js');
function cms_help_js($vars){
	?>
	$('.toggle-hidden').hide();
	$('.toggle-hidden').first().show();
	$('h3.toggle').css('cursor', 'pointer').click(function(){
		$('.toggle-hidden').slideUp();
		$(this).next('.toggle-hidden').slideDown();
	});
    <?php
}