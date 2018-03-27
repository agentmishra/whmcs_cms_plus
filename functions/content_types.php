<?php


use Illuminate\Database\Capsule\Manager as Capsule;



add_hook('cms/activate', 1, 'cms_content_types_activate');
function cms_content_types_activate(){

	$query = "CREATE TABLE IF NOT EXISTS `mod_whmcs_cms_plus_content_types` (
    `id` INT( 1 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , 
    `type` VARCHAR(255) NOT NULL, 
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
	`fimage` VARCHAR(255) NOT NULL,
    `singular_name` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL, 
    `public` INT( 1 ) NOT NULL,
    `hierarchical` INT( 1 ) NOT NULL,
    `has_archive` INT( 1 ) NOT NULL,
    `ssl` INT( 1 ) NOT NULL
    ) DEFAULT CHARSET=utf8 ENGINE=MyISAM";


	if(full_query($query) == false){
		return 'Failed to create the Content Types table.';
	}

	if(Capsule::table('mod_whmcs_cms_plus_content_types')->where('type', 'page')->count() == 0){
		insert_query('mod_whmcs_cms_plus_content_types', array(
			'type' => 'page',
			'name' => 'Pages',
			'slug' => '*',
			'fimage' =>'',
			'singular_name' => 'Page',
			'description' => 'Static Pages for your site.',
			'public' => true,
			'hierarchical' => true,
			'has_archive' => false
		));
	}

	$query = "CREATE TABLE IF NOT EXISTS `mod_whmcs_cms_plus_content` (
    `id` INT( 1 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , 
    `type` VARCHAR(255) NOT NULL,
    `parent` INT( 1 ) NOT NULL, 
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
	`fimage` VARCHAR(255) NOT NULL,
    `content` LONGTEXT NOT NULL, 
    `head_content` LONGTEXT NOT NULL, 
    `comments` INT( 1 ) NOT NULL,
    `fb` INT( 1 ) NOT NULL,
    `twitter` INT( 1 ) NOT NULL,
    `gplus` INT( 1 ) NOT NULL,
    `published` INT( 1 ) NOT NULL,
    `created` DATETIME NOT NULL,
  	`updated` DATETIME NOT NULL,
  	`ssl` INT( 1 ) NOT NULL,
  	`private` INT( 1 ) NOT NULL,
  	`restrictions` LONGTEXT NOT NULL,
  	`breadcrumbs` INT( 1 ) NOT NULL,
  	`meta` LONGTEXT NOT NULL
    ) DEFAULT CHARSET=utf8 ENGINE=MyISAM";


	if(full_query($query) == false){
		return 'Failed to create the Content Types table.';
	}

	logActivity('Content Types Activated');
	return true;
}

add_hook('cms/deactivate', 1, 'cms_content_types_deactivate');
function cms_content_types_deactivate($vars){
	if($vars['delete_tables'] == 1){
		logActivity('Content Types Tables Deleted');
		full_query("DROP TABLE `mod_whmcs_cms_plus_content_types`,`mod_whmcs_cms_plus_content`");
	}//if
	return true;
}

add_hook('cms/upgrade', 1, 'cms_content_types_upgrade');
function cms_content_types_upgrade($vars){
	if($vars['version'] < '1.0.1'){
		logActivity('Content Types Tables Upgrade');
		full_query("ALTER TABLE `mod_whmcs_cms_plus_content` ADD `restrictions` LONGTEXT NOT NULL");
	}//if
	return true;
}

add_hook('cms/admin/page', 2, 'cms_content_types_admin_page');
function cms_content_types_admin_page($vars){

	$return = array();

	//view tab
	if(isset($_GET['tab']) && $_GET['tab'] == 'content_types_view' && isset($_GET['id'])){
		$return['content_types_view'] = get_query_val('mod_whmcs_cms_plus_content_types', 'name', array('id' => $_GET['id']));
	}

	//add tab
	if(isset($_GET['tab']) && $_GET['tab'] == 'add_content_type'){
		$return['add_content_type'] = $vars['_lang']['add_types'];
	}

	//edit tab
	if(isset($_GET['tab']) && $_GET['tab'] == 'edit'){
		$return['edit'] = $vars['_lang']['edit'] . ' ' . get_query_val('mod_whmcs_cms_plus_content_types', 'singular_name', array('type' => get_query_val('mod_whmcs_cms_plus_content', 'type', array('id' => $_GET['id']))));
		$return['content_types_view'] = get_query_val('mod_whmcs_cms_plus_content_types', 'name', array('type' => get_query_val('mod_whmcs_cms_plus_content', 'type', array('id' => $_GET['id']))));
	}

	//default tab
	$return['content_types'] = $vars['_lang']['view_types'];

	return $return;
}

add_hook('cms/admin/page/css', 1, 'cms_content_types_css');
function cms_content_types_css($vars){
	?>
	#content_types .contentbox{
		width: 22.9%;
		margin: 1%;
		float: left;
		text-align: center;
		padding: 0px;
	}
	#content_types .contentbox:hover{
		background: #f2f2f2;
	}
	#content_types .contentbox a{
		display: block;
		padding: 30px;
		text-decoration: none;
	}
	#content_types .contentbox a .count{
		font-size: 10px;
		color: #aaa;
		display: inline-block;
		margin-top: 4px;
	}
	#edit-type{
		position: absolute;
		top: 14px;
		right: 30px;
	}
	#delete-type{
		position: absolute;
		top: 14px;
		right: 10px;
	}
	#add-new{
		position: absolute;
		top: 14px;
		right: 50px;
	}
	#content-list{
		margin-top: 20px;
	}

	#edit-form,#new-form{
		border-bottom: 1px solid #ccc;
		padding-bottom: 10px;
	}

	.actions-td .actions{
		margin-top: 4px;
	}

	.actions a{
		text-decoration: none;
	}
	.actions .delete{
		color: red;
	}
	<?php
}

add_hook('cms/admin/page/js', 1, 'cms_content_types_js');
function cms_content_types_js($vars){
	?>
	$('.confirm').click(function(){
		return confirm('<?php echo $vars['_lang']['delete_confirm'];?>');
	});
	$('#edit-form').hide();
	$('#edit-type').click(function(){
		$('#new-form').slideUp();
		$('#edit-form').slideToggle();
		return false;
	});
	$('#new-form').hide();
	$('#add-new').click(function(){
		$('#edit-form').slideUp();
		$('#new-form').slideToggle();
		return false;
	});
	$('#content-list table').dataTable({"order": []});

	$('td .actions').hide();
	$('.actions-td').hover(function(){
		$(this).find('.actions').show();
	},function(){
		$(this).find('.actions').hide();
	});

	<?php
}

add_hook('cms/admin/page/top', 1, 'cms_content_types_scripts_styles');
function cms_content_types_scripts_styles($vars){
	echo '<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.2/css/jquery.dataTables.min.css"/>';
	echo '<script src="//cdn.datatables.net/1.10.2/js/jquery.dataTables.min.js"></script>';
	echo '<script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/ace.js"></script>';
	echo '<script src="/modules/addons/ckeditor/ckeditor.js"></script>';
	echo '<script src="/modules/addons/ckeditor/config.js"></script>';
}


add_hook('cms/admin/page/content_types', 1, 'cms_content_types_admin_page_html');
function cms_content_types_admin_page_html($vars){
	
	
	ob_start();

	if(isset($_GET['delete']) && $_GET['delete'] != '' && get_query_val('mod_whmcs_cms_plus_content_types', 'type', array('id' => $_GET['delete'])) != 'page'){
		delete_query('mod_whmcs_cms_plus_content_types', array('id' => $_GET['delete']));
		echo '<div class="successbox"><strong><span class="title">'.$vars['_lang']['type_deleted'].'</span></strong></div>';
	}

	if(!empty($_POST) && isset($_POST['postaction']) && $_POST['postaction'] == 'new_type'){
		$type = cms_sanitize_string($_POST['settings']['slug']);
		$exists = Capsule::table('mod_whmcs_cms_plus_content_types')->where('slug', $type)->count();
		while($exists > 0){
			$type .= '-';
			$exists = Capsule::table('mod_whmcs_cms_plus_content_types')->where('slug', $type)->count();
		}
		insert_query('mod_whmcs_cms_plus_content_types', array(
			'type' => $type,
			'name' => trim($_POST['settings']['name']),
			'slug' => $type,
			'fimage' => $target_path.$filename,
			'singular_name' => trim($_POST['settings']['singular_name']),
			'description' => trim($_POST['settings']['description']),
			'public' => $_POST['settings']['public'],
			'hierarchical' => $_POST['settings']['hierarchical'],
			'has_archive' => $_POST['settings']['has_archive'],
			'ssl' => $_POST['settings']['ssl'],
		));

		echo '<div class="successbox"><strong><span class="title">'.$vars['_lang']['type_added'].'</span></strong></div>';
	}

	$content_types = Capsule::table('mod_whmcs_cms_plus_content_types')->get();
	foreach($content_types as $type){
		$type = json_decode(json_encode($type), true);
		$count = Capsule::table('mod_whmcs_cms_plus_content')->where('type', $type['type'])->count();
		echo '<div class="contentbox">';
			echo '<a href="'.$vars['modulelink'].'&tab=content_types_view&id='.$type['id'].'" title="'.$type['name'].'">'.$type['name'].'<br/><span class="count">'.$vars['_lang']['total'].': '.$count.'</span></a>';
		echo '</div>';
	}

	echo '<div class="contentbox">';
		echo '<a href="'.$vars['modulelink'].'&tab=add_content_type" title="'.$vars['_lang']['add_types'].'">'.$vars['_lang']['add_types'].'<br/><span class="count">&nbsp;</span></a>';
	echo '</div>';

	echo '<div class="clear"></div>';

	return ob_get_clean();
}

add_hook('cms/admin/page/add_content_type', 1, 'cms_add_content_type_admin_page_html');
function cms_add_content_type_admin_page_html($vars){

	ob_start();

	echo cms_display_form(cms_content_type_fields($vars), array(), $vars['modulelink'].'&tab=content_types', $vars, array('postaction' => 'new_type'));

	return ob_get_clean();
}

add_hook('cms/admin/page/content_types_view/title', 1, 'cms_content_types_view_admin_page_title');
function cms_content_types_view_admin_page_title($vars){
	if(!isset($_GET['id'])){
		return;
	}
	if(get_query_val('mod_whmcs_cms_plus_content_types', 'has_archive', array('id' => $_GET['id'])) == 1){
		return ' <small><a href="../'.get_query_val('mod_whmcs_cms_plus_content_types', 'slug', array('id' => $_GET['id'])).'" target="_blank">'.$vars['_lang']['view'].' '.$vars['_lang']['archive'].'</a></small>';
	}
}

add_hook('cms/admin/page/content_types_view', 1, 'cms_content_types_view_admin_page_html');
function cms_content_types_view_admin_page_html($vars){
		//Image Upload for Featured Image
	
	$target_path .= $_SERVER['DOCUMENT_ROOT'].'/images/'. date("Y") . '/' . date("m") .'/';	
	
	if (!file_exists($target_path)) {
    mkdir($target_path, 0777, true);
}
		
	//Valid Extensions
	$validextensions =  array(    
	  'image/pjpeg' => 'jpg',   
	  'image/jpeg' => 'jpg',   
	  'image/gif' => 'gif',   
	  'image/bmp' => 'bmp',   
	  'image/x-png' => 'png',
	  'image/png' => 'png'  
	); 
	// Get File Type
	$filetype = $_FILES['fimage']['type'];
	$file_extension = $validextensions[$filetype];
	
	//File Names
	$simpleName = $_FILES['fimage']['name'];   
	$filename = rand().$_FILES['fimage']['name'];



	if (in_array($file_extension, $validextensions) && is_writable($target_path)) {

	
	if (move_uploaded_file($_FILES["fimage"]['tmp_name'], $target_path. $filename) ) {
		
	// If file moved to uploads folder.
	$message .= 'Featured Image '.$simpleName.' uploaded successfully!';
	} 
	else {     
	//  If File Was Not Moved.
	$message .= 'Featured Image '.$simpleName.' Failed to Upload - please try again!';
	}}
	else {
	//   If File Size And File Type Was Incorrect.
	$message .= '***Invalid file Size or Type***';
	}			  	
//END OF IMAGE UPLOAD

	if(!isset($_GET['id'])){
		return;
	}

	if(isset($_GET['tab']) && $_GET['tab'] == 'edit'){
		$id = get_query_val('mod_whmcs_cms_plus_content_types', 'id', array('type' => get_query_val('mod_whmcs_cms_plus_content', 'type', array('id' => $_GET['id']))));
	}else{
		$id = $_GET['id'];
	}

	if(isset($_GET['delete']) && $_GET['delete'] != ''){
		delete_query('mod_whmcs_cms_plus_content', array('id' => $_GET['delete']));
		echo '<div class="successbox"><strong><span class="title">'.$vars['_lang']['content_deleted'].'</span></strong></div>';
	}


	$type = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content_types')->where('id', $id)->first()), true);
	ob_start();
	
	echo '<a href="'.$vars['modulelink'].'&tab=content_types&delete='.$id.'" id="delete-type" class="confirm" title="'.$vars['_lang']['delete'].'"><img width="16" height="16" border="0" alt="'.$vars['_lang']['delete'].'" src="images/delete.gif"></a>';
	if($type['type'] != 'page'){
		echo '<a href="#" id="edit-type" title="'.$vars['_lang']['edit'].'"><img width="16" height="16" border="0" alt="'.$vars['_lang']['edit'].'" src="images/edit.gif"></a>';
	}else{
		echo '<a href="#" id="edit-type" title="'.$vars['_lang']['edit'].'" onclick="alert(\'You cannot edit the core content type!\');return false;"><img width="16" height="16" border="0" alt="'.$vars['_lang']['edit'].'" src="images/edit.gif"></a>';
	}
	echo '<a href="#" id="add-new" title="'.$vars['_lang']['add'].' '.$type['singular_name'].'"><img width="16" height="16" border="0" alt="'.$vars['_lang']['add'].' '.$type['singular_name'].'" src="images/icons/add.png"></a>';
	
	if(!empty($_POST['postaction']) && $_POST['postaction'] == 'edit_type' && isset($id)){
		$type = cms_sanitize_string($_POST['settings']['slug']);
		update_query('mod_whmcs_cms_plus_content_types', array(
			'name' => trim($_POST['settings']['name']),
			'slug' => $type,
			'fimage' => $_POST['settings']['fimage'],
			'singular_name' => trim($_POST['settings']['singular_name']),
			'description' => trim($_POST['settings']['description']),
			'public' => $_POST['settings']['public'],
			'hierarchical' => $_POST['settings']['hierarchical'],
			'has_archive' => $_POST['settings']['has_archive'],
			'ssl' => $_POST['settings']['ssl'],
		), array(
			'id' => $id
		));
		$type = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content_types')->where('id', $id)->first()), true);
		run_hook('cms/content_types/update', array_merge($vars, array('id' => $type['id'], 'type' => $type)));
		echo '<div class="successbox"><strong><span class="title">'.$vars['_lang']['type_updated'].'</span></strong></div>';
	}
	$old_id = $id;
	if(!empty($_POST['postaction']) && $_POST['postaction'] == 'new_content'){
		$slug = cms_sanitize_string($_POST['settings']['slug']);
		$meta = array();

		foreach($_POST['settings']['meta'] as $key => $value){
			if($value['key'] != ''){
				$meta[$value['key']] = $value['value'];
			}
		}

		$id = insert_query('mod_whmcs_cms_plus_content', array(
			'type' => $type['type'],
			'parent' => cms_insert_int(trim($_POST['settings']['parent'])),
			'title' => trim($_POST['settings']['title']),
			'slug' => $slug,
			'fimage' => $target_path.$filename,
			'content' => trim($_POST['settings']['content']),
			'head_content' => trim($_POST['settings']['head_content']),
			'published' => cms_insert_int($_POST['settings']['published']),
			'comments' => cms_insert_int($_POST['settings']['comments']),
			'fb' => cms_insert_int($_POST['settings']['fb']),
			'twitter' => cms_insert_int($_POST['settings']['twitter']),
			'gplus' => cms_insert_int($_POST['settings']['gplus']),
			'created' => date("Y-m-d H:i:s"),
			'updated' => date("Y-m-d H:i:s"),
			'ssl' => cms_insert_int($_POST['settings']['ssl']),
			'private' => cms_insert_int($_POST['settings']['private']),
			'restrictions' => serialize($_POST['settings']['restrictions']),
			'breadcrumbs' => cms_insert_int($_POST['settings']['breadcrumbs']),
			'meta' => serialize($meta)
		));
		run_hook('cms/content/new', array_merge($vars, array('id' => $id, 'type' => $type)));
		echo '<div class="successbox"><strong><span class="title">'.$vars['_lang']['content_added'].'</span></strong></div>';
	}
	

	
	$id = $old_id;

	echo '<p>'.$type['description'].'</p>';	

	if($type['type'] != 'page'){
		echo '<div id="edit-form">';
			echo '<h3>'.$vars['_lang']['edit'].' '.$type['name'].'</h3>';
			echo cms_display_form(cms_content_type_fields($vars), $type, $vars['modulelink'].'&tab=content_types_view&id='.$id, $vars, array('postaction' => 'edit_type'));
		echo '</div>';
	}

	echo '<div id="new-form">';
		echo '<h3>'.$vars['_lang']['add'].' '.$type['singular_name'].'</h3>';
		echo cms_display_form(cms_content_fields($vars, $type, 0), array(), $vars['modulelink'].'&tab=content_types_view&id='.$id, $vars, array('postaction' => 'new_content'));
	echo '</div>';

	echo '<div id="content-list">';
		echo '<table class="hover">';
			echo '<thead><tr><th>Title</th><th width="100">Url</th><th width="100" class="center">Featured Image</th><th width="100" class="center">Created</th><th width="100" class="center">Updated</th><th width="20" class="center">Published</th></tr></thead>';
			echo '<tbody>';
				$content = Capsule::table('mod_whmcs_cms_plus_content')->where('type', $type['type'])->get();
				foreach($content as $c){
					$c = json_decode(json_encode($c), true);
					echo '<tr>';
						echo '<td class="actions-td">'.$c['title'].'<div class="actions">';
						$actions_returned = run_hook('cms/admin/page/content/table', array_merge($vars, array('content' => $c, 'type' => $type)));
						$links = array();
						foreach($actions_returned as $ret){
							foreach($ret as $key => $action){
								$links[] = '<a href="'.$action['href'].'" class="action-'.$key.' '.$action['class'].'" title="'.$action['title'].'">'.$action['text'].'</a>';
							}
						}
						echo implode(' | ', $links);
						echo '</div></td>';
						echo '<td>'.$c['slug'].'</td>';
						echo '<td class="center">'.$c['fimage'].'</td>';
						echo '<td class="center">'.$c['created'].'</td>';
						echo '<td class="center">'.$c['updated'].'</td>';
						echo '<td class="center">'.(($c['published']) ? '<img width="16" height="16" border="0" alt="Published" src="images/icons/tick.png">' : '<img width="16" height="16" border="0" alt="Not Private" src="images/icons/disabled.png">').'</td>';
					echo '</tr>';
				}
			echo '</tbody>';
		echo '</table>';
	echo '</div>';

	return ob_get_clean();
}


add_hook('cms/admin/page/edit', 1, 'cms_edit_admin_page_html');
function cms_edit_admin_page_html($vars, $target_path){
	ob_start();
	
	//Image Upload for Featured Image
	
	$target_path .= $_SERVER['DOCUMENT_ROOT'].'/images/'. date("Y") . '/' . date("m") .'/';	
	
	if (!file_exists($target_path)) {
    mkdir($target_path, 0777, true);
}
		
	//Valid Extensions
	$validextensions =  array(    
	  'image/pjpeg' => 'jpg',   
	  'image/jpeg' => 'jpg',   
	  'image/gif' => 'gif',   
	  'image/bmp' => 'bmp',   
	  'image/x-png' => 'png',
	  'image/png' => 'png'  
	); 
	// Get File Type
	$filetype = $_FILES['fimage']['type'];
	$file_extension = $validextensions[$filetype];
	
	//File Names
	$simpleName = $_FILES['fimage']['name'];   
	$filename = rand().$_FILES['fimage']['name'];



	if (in_array($file_extension, $validextensions) && is_writable($target_path)) {

	
	if (move_uploaded_file($_FILES["fimage"]['tmp_name'], $target_path. $filename) ) {
		
	// If file moved to uploads folder.
	$message .= 'Featured Image '.$simpleName.' uploaded successfully!';
	} 
	else {     
	//  If File Was Not Moved.
	$message .= 'Featured Image '.$simpleName.' Failed to Upload - please try again!';
	}}
	else {
	//   If File Size And File Type Was Incorrect.
	$message .= '***Invalid file Size or Type***';
	}			  	
//END OF IMAGE UPLOAD
		
	$values = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content')->where('id', $_GET['id'])->first()), true);
	$type = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content_types')->where('type', $values['type'])->first()), true);

	if(!empty($_POST['postaction']) && $_POST['postaction'] == 'edit_content'){
		$slug = cms_sanitize_string($_POST['settings']['slug']);
		$meta = array();

		foreach($_POST['settings']['meta'] as $key => $value){
			if($value['key'] != ''){
				$meta[$value['key']] = $value['value'];
			}
		}

		update_query('mod_whmcs_cms_plus_content', array(
			'parent' => cms_insert_int(trim($_POST['settings']['parent'])),
			'title' => trim($_POST['settings']['title']),
			'slug' => $slug,
			'fimage' => $target_path.$filename,
			'content' => trim($_POST['settings']['content']),
			'head_content' => trim($_POST['settings']['head_content']),
			'comments' => cms_insert_int($_POST['settings']['comments']),
			'fb' => cms_insert_int($_POST['settings']['fb']),
			'twitter' => cms_insert_int($_POST['settings']['twitter']),
			'gplus' => cms_insert_int($_POST['settings']['gplus']),
			'published' => cms_insert_int($_POST['settings']['published']),
			'updated' => date("Y-m-d H:i:s"),
			'ssl' => cms_insert_int($_POST['settings']['ssl']),
			'private' => cms_insert_int($_POST['settings']['private']),
			'restrictions' => serialize($_POST['settings']['restrictions']),
			'breadcrumbs' => cms_insert_int($_POST['settings']['breadcrumbs']),
			'meta' => serialize($meta)
		), array(
			'id' => $_GET['id']
		));
		$type = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content_types')->where('type', $values['type'])->first()), true);
		run_hook('cms/content/edit', array_merge($vars, array('id' => $_GET['id'], 'type' => $type)));
		$values = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content')->where('id', $_GET['id'])->first()), true);
		
		echo '<div class="successbox"><strong><span class="title">'.$vars['_lang']['content_updated'].'<br />'.$message.'</span></strong></div>';
	}

	echo cms_display_form(cms_content_fields($vars, $type, $values['id']), $values, $vars['modulelink'].'&tab=edit&id='.$_GET['id'].'', $vars, array('postaction' => 'edit_content'));
	return ob_get_clean();
}


add_hook('cms/admin/page/content/table', 1, 'cms_content_types_table_actions');
function cms_content_types_table_actions($vars){
	if($vars['type']['public'] == 1){
		if($vars['type']['type'] != 'page'){
			$href = '/'.$vars['type']['slug'].'/'.$vars['content']['slug'].'" target="_blank';
		}else{
			$href = '/'.$vars['content']['slug'].'" target="_blank';
		}
	}else{
		$href = 'javascript:void(0);" onclick="alert(\'Not Available!\');return false;';
	}
	global $CONFIG;
	return array(
		'view' => array(
			'href' => rtrim($CONFIG['SystemURL'], '/').$href,
			'class' => '',
			'title' => $vars['_lang']['view'] . ' ' . $vars['type']['singular_name'],
			'text' => $vars['_lang']['view']
		),
		'edit' => array(
			'href' => $vars['modulelink'].'&tab=edit&id='.$vars['content']['id'],
			'class' => '',
			'title' => $vars['_lang']['edit'] . ' ' . $vars['type']['singular_name'],
			'text' => $vars['_lang']['edit']
		),
		'delete' => array(
			'href' => $vars['modulelink'].'&tab=content_types_view&id='.$_GET['id'].'&delete='.$vars['content']['id'],
			'class' => 'delete confirm',
			'title' => $vars['_lang']['delete'] . ' ' . $vars['type']['singular_name'],
			'text' => $vars['_lang']['delete']
		)
	);
}



add_hook('cms/client/view/vars', 1, 'cms_prev_next_links');
function cms_prev_next_links($vars){
	$type = get_query_val('mod_whmcs_cms_plus_content', 'type', array('id' => $vars['vars']['id']));
	$type = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content_types')->where('type', $type)->first()), true);
	if($type['has_archive'] == 1){
		$array = array();
		$prev = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content')->where('id', '<', $vars['vars']['id'])->where('published', '1')->where('type', $type['type'])->orderBy('id', 'desc')->first()), true);
		if(!empty($prev)){
			$array['prev_link'] = '/'.$type['slug'].'/'.$prev['slug'];
			$array['prev_link_text'] = $prev['title'];
		}
		$next = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content')->where('id', '>', $vars['vars']['id'])->where('published', '1')->where('type', $type['type'])->orderBy('id', 'asc')->first()), true);
		if(!empty($next)){
			$array['next_link'] = '/' .$type['slug'].'/'.$next['slug'];
			$array['next_link_text'] = $next['title'];
		}
		return $array;
	}
}


add_hook('cms/client/view/type/vars', 1, 'cms_list_articles');
function cms_list_articles($vars){

	$ppp = 10;
	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
	$type = get_query_val('mod_whmcs_cms_plus_content_types', 'type', array('id' => $vars['vars']['id']));
	$count = Capsule::table('mod_whmcs_cms_plus_content')->where('type', $type)->where('published', '1')->count();
	$limit = $ppp;
	$offset = (($page - 1) * $ppp);


	$contents = Capsule::table('mod_whmcs_cms_plus_content')->where('type', $type)->where('published',  '1')->orderBy('id', 'desc')->limit($limit)->offset($offset)->get();
	$array = array();
	foreach($contents as $content){
		$content = json_decode(json_encode($content), true);
		$array['articles'][$content['id']] = $content;
	}
	$array['type'] = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content_types')->where('type', $type)->first()), true);


	//get page numbers
	$pages = ceil(($count / $ppp));
	$p = array();
	$i = 1;
	while($i <= $pages){
		$p[] = $i;
		$i++;
	}
	$array['pages'] = $p;
	$array['current_page'] = $page;
	if($page != 1){
		$array['prev_page'] = ($page - 1);
	}
	if($page != $pages){
		$array['next_page'] = ($page + 1);
	}
	return $array;
}

function cms_content_type_fields($vars){
	return array(
		'name' => array(
			'type' => 'text',
			'title' => $vars['_lang']['type_name'],
			'desc' => $vars['_lang']['type_name_desc']
		),
		'singular_name' => array(
			'type' => 'text',
			'title' => $vars['_lang']['type_singular_name'],
			'desc' => $vars['_lang']['type_singular_name_desc']
		),
		'slug' => array(
			'type' => 'text',
			'title' => $vars['_lang']['type_slug'],
			'desc' => $vars['_lang']['type_slug_desc']
		),
		'description' => array(
			'type' => 'textarea',
			'title' => $vars['_lang']['type_description'],
			'desc' => $vars['_lang']['type_description_desc']
		),
		'public' => array(
			'type' => 'checkbox',
			'title' => $vars['_lang']['type_public'],
			'checkbox_desc' => $vars['_lang']['type_public_desc']
		),
		'hierarchical' => array(
			'type' => 'checkbox',
			'title' => $vars['_lang']['type_hierarchical'],
			'checkbox_desc' => $vars['_lang']['type_hierarchical_desc']
		),
		'has_archive' => array(
			'type' => 'checkbox',
			'title' => $vars['_lang']['type_has_archive'],
			'checkbox_desc' => $vars['_lang']['type_has_archive_desc']
		),
		'ssl' => array(
			'type' => 'checkbox',
			'title' => $vars['_lang']['type_ssl'],
			'checkbox_desc' => $vars['_lang']['type_ssl_desc']
		),
	);
}

function cms_content_fields($vars, $type, $id){
	$return = array(
		'title' => array(
			'type' => 'text',
			'title' => $vars['_lang']['content_title'],
			'desc' => $vars['_lang']['content_title_desc']
		),
		'slug' => array(
			'type' => 'text',
			'title' => $vars['_lang']['content_slug'],
			'desc' => $vars['_lang']['content_slug_desc']
		),
	);
	if($type['hierarchical'] == true){
		$parents = array(0 => $vars['_lang']['none']);
		$parents = $parents + cms_get_parents_select($type['type'], $id);
		$return['parent'] = array(
			'type' => 'select',
			'title' => $vars['_lang']['content_parent'],
			'desc' => $vars['_lang']['content_parent_desc'],
			'options' => $parents
		);
	}

	$return['head_content'] = array(
		'type' => 'textarea',
		'ace' => 'html',
		'title' => $vars['_lang']['content_head_content'],
		'desc' => $vars['_lang']['content_head_content_desc']
	);

	$return['breadcrumbs'] = array(
		'type' => 'checkbox',
		'title' => $vars['_lang']['content_breadcrumbs'],
		'checkbox_desc' => $vars['_lang']['content_breadcrumbs_desc']
	);
	
	$return['fimage'] = array(
		'type' => 'file',
		'title' => $vars['_lang']['content_featured_image'],
		'desc' => $vars['_lang']['content_featured_image_desc']
	
	);

	$return['content'] = array(
		'type' => 'textarea',
		'class' => 'ckeditor',
		'title' => $vars['_lang']['content_content'],
		'desc' => $vars['_lang']['content_content_desc']
	);

	$return['meta'] = array(
		'type' => 'meta',
		'title' => $vars['_lang']['content_meta'],
		'desc' => $vars['_lang']['content_meta_desc']
	);

	$conf = whmcs_cms_config();
	if($conf['comments'] != ''){
		$return['comments'] = array(
			'type' => 'checkbox',
			'title' => $vars['_lang']['content_comments'],
			'checkbox_desc' => $vars['_lang']['content_comments_desc']
		);
	}

	$return['fb'] = array(
		'type' => 'checkbox',
		'title' => $vars['_lang']['content_fb'],
		'checkbox_desc' => $vars['_lang']['content_fb_desc']
	);
	$return['twitter'] = array(
		'type' => 'checkbox',
		'title' => $vars['_lang']['content_twitter'],
		'checkbox_desc' => $vars['_lang']['content_twitter_desc']
	);
	$return['gplus'] = array(
		'type' => 'checkbox',
		'title' => $vars['_lang']['content_gplus'],
		'checkbox_desc' => $vars['_lang']['content_gplus_desc']
	);
	$return['ssl'] = array(
		'type' => 'checkbox',
		'title' => $vars['_lang']['content_ssl'],
		'checkbox_desc' => $vars['_lang']['content_ssl_desc']
	);
	$return['private'] = array(
		'type' => 'checkbox',
		'title' => $vars['_lang']['content_private'],
		'checkbox_desc' => $vars['_lang']['content_private_desc']
	);
	$return['restrictions'] = array(
		'type' => 'restrictions',
		'title' => $vars['_lang']['content_restrictions'],
		'enable_desc' => $vars['_lang']['content_restrictions_enable'],
		'match' => $vars['_lang']['content_restrictions_match'],
		'all' => $vars['_lang']['content_restrictions_match_all'],
		'any' => $vars['_lang']['content_restrictions_match_any'],
		'conditions' => $vars['_lang']['content_restrictions_match_conditions'],
		'404' => $vars['_lang']['content_restrictions_match_404'],
		'display' => $vars['_lang']['content_restrictions_match_display'],
		'groups' => $vars['_lang']['content_restrictions_groups'],
		'products' => $vars['_lang']['content_restrictions_products'],
		'domains' => $vars['_lang']['content_restrictions_domains'],
		'disabled' => $vars['_lang']['content_restrictions_disabled'],
		'domain_desc' => $vars['_lang']['content_restrictions_domain_desc']
	);
	$return['published'] = array(
		'type' => 'checkbox',
		'title' => $vars['_lang']['content_published'],
		'checkbox_desc' => $vars['_lang']['content_published_desc']
	);

	return $return;
}


function cms_get_parents_select($type, $exclude = 0, $parent = 0, $delimiter = ' '){
	$ret = array();
	$contents = Capsule::table('mod_whmcs_cms_plus_content')->where('type', $type)->where('parent', $parent);
	if($exclude != 0){
		$contents = $contents->where('parent', '!=', $exclude);
	}
	$contents = $contents->get();
	foreach($contents as $content){
		$content = json_decode(json_encode($content), true);
		if($content['id'] != $exclude){
			$ret[$content['id']] = $delimiter.$content['title'];
		}
		$sub = cms_get_parents_select($type, $exclude, $content['id'], '-'.$delimiter);
		$ret = $ret + $sub;
	}
	return $ret;
}





