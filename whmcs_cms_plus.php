<?php
use Illuminate\Database\Capsule\Manager as Capsule;
// *************************************************************************
// *                                                                       *
// * WHMCS CMS Plus Module					   						       *
// * Copyright (c) Black Nova designs. All Rights Reserved,                *
// * Release Date: 26th March 2018                                         *
// * Version 3.0.0 Stable                                                  *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * Email: sales@blacknovadesigns.co.uk                                   *
// * Website: https://www.blacknovadesigns.co.uk                           *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * This software is furnished under a license and may be used and copied *
// * only  in  accordance  with  the  terms  of such  license and with the *
// * inclusion of the above copyright notice.  This software  or any other *
// * copies thereof may not be provided or otherwise made available to any *
// * other person.  No title to and  ownership of the  software is  hereby *
// * transferred.                                                          *
// *                                                                       *
// * You may not reverse  engineer, decompile, defeat  license  encryption *
// * mechanisms, or  disassemble this software product or software product *
// * license.  No Half Pixels may terminate this license if you don't      *
// * comply with any of the terms and conditions set forth in our end user *
// * license agreement (EULA).  In such event,  licensee  agrees to return *
// * licensor  or destroy  all copies of software  upon termination of the *
// * license.                                                              *
// *                                                                       *
// * Please see the EULA file for the full End User License Agreement.     *
// *                                                                       *
// *************************************************************************
if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

require_once(dirname(__FILE__) . '/hooks.php');

function whmcs_cms_plus_config(){

	$configarray["name"] = "WHMCS CMS Plus";
    $configarray["description"] = "The all in one content management solution for WHMCS.";
    $configarray["version"] = "3.0.0";
    $configarray["author"] = '<a href="https://wwww.blacknovadesigns.co.uk" target="_blank" title="Black Nova Designs"><img src="../modules/addons/'.basename(__FILE__, '.php').'/logo.png" alt="Black Nova Designs"/></a>';
    $configarray["language"] = "english";
    $configarray["premium"] = true;
 
    return $configarray;
}

function whmcs_cms_plus_activate($vars){
    logActivity('Activating WHMCS CMS Plus');

    $hookresults = run_hook( "cms/activate" );
    foreach( $hookresults as $status ){
        if($status !== true){
            return array('status'=>'error','description'=>'Module Activate Error: '.$status);
        }
    }

    logActivity('Activated WHMCS CMS Plus');
    //all good
    return array('status'=>'success','description'=>'Module Activated Successfully');
}

function whmcs_cms_plus_deactivate($vars){
    logActivity('Deactivating WHMCS CMS Plus');

    $hookresults = run_hook( "cms/deactivate", whmcs_cms_config() );
    foreach( $hookresults as $status ){
        if($status !== true){
            return array('status'=>'error','description'=>'Module Deactivation Error: '.$status);
        }
    }

    logActivity('Deactivated WHMCS CMS Plus');
    //all good
    return array('status'=>'success','description'=>'Module Deactivated Successfully');
}

function whmcs_cms_plus_upgrade($vars){
    logActivity('Upgrading WHMCS CMS Plus');
    
    $hookresults = run_hook( "cms/upgrade", $vars );
    foreach( $hookresults as $status ){
        if($status !== true){
            return array('status'=>'error','description'=>'Module Upgrade Error: '.$status);
        }
    }

    logActivity('Upgraded WHMCS CMS Plus');
    //all good
    return array('status'=>'success','description'=>'Module Upgraded Successfully');
}

function whmcs_cms_plus_sidebar($vars) {
    $LANG = $vars['_lang'];

    $sidebar = '<span class="header"><img src="images/icons/addonmodules.png" class="absmiddle" width="16" height="16" /> '.$LANG['sidebar_name'].'</span>
    <ul class="menu">
        <li><a href="configaddonmods.php#whmcs_cms_plus">'.$LANG['sidebar_config'].'</a></li>
        <li><a href="https://www.blacknovadesigns.co.ukforums/forum/41-whmcs-cms-plus/" target="_blank">'.$LANG['sidebar_version'].': '.$vars['version'].'</a></li>
        <li><a href="https://www.blacknovadesigns.co.uk/forums/forum/41-whmcs-cms-plus/" target="_blank">'.$LANG['sidebar_support'].'</a></li>
    </ul>';
    
    return $sidebar;
}

function whmcs_cms_plus_output($vars){

    global $whmcs_cms_plus;

   run_hook('cms/admin/page/top', $vars);

    echo '<div id="cmsmenu" class="contentbox">';
        $links = array();
        $hookresults = run_hook( "cms/admin/page", $vars );
        foreach( $hookresults as $arr ){
            foreach($arr as $key => $value){
                $links[] = '<a href="#'.$key.'" title="'.$value.'"'.((isset($_GET['tab']) && $_GET['tab'] == $key) ? ' class="active"' : '').'>'.$value.'</a>';
            }
        }
        echo implode(' | ', $links);
    echo '</div>';

    echo '<div class="cmscontentarea">';
        foreach( $hookresults as $arr ){
            foreach($arr as $key => $value){
                echo '<div id="'.$key.'"'.((isset($_GET['tab']) && $_GET['tab'] == $key) ? ' class="active"' : '').'><h3>'.$value.implode(run_hook('cms/admin/page/'.$key.'/title', $vars)).'</h3>'.implode(run_hook('cms/admin/page/'.$key, $vars)).'</div>';
            }
        }
    echo '</div>';

}



function whmcs_cms_plus_clientarea($vars){
    global $CONFIG;

    //get scheme independant urls
    //whmcs url
    $base_url = str_replace(array('http://', 'https://'), '//', $CONFIG['SystemURL']);
    //actual queried url
    $query_url = str_replace(array('http://', 'https://'), '//', cms_requested_url());
    //remove whmcs url form queried url to get "our" url path (removing slashes in the proccess) - nice hack done better than old pages module
    $query = trim(str_replace($base_url, '', $query_url), '/');
	

    //reset the vars for smarty
    $_SERVER['SCRIPT_NAME'] = '/'.$query;
    $_SERVER['SCRIPT_FILENAME'] = ROOTDIR.'/'.$query;
    $_SERVER['PHP_SELF'] = '/'.$query;

    //check pages - these are builtin to the module and take priority over any other content type
    $page = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content')->where('slug', $query)->where('published', '1')->where('type', 'page')->first()), true);
    if(!empty($page)){
    	return cms_found_content($page, $vars, $query);
    }

    //no page found, lets see if there is an archive page for a content type based on the url - we do this before the actual content urls as if we make a match we have run significantly less sql queries. We also DONT run this if the query contains a "/" as content types cant have a "/" in the slug field.
    if(strpos($query, '/') === false){
    	$type = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content_types')->where('slug', $query)->where('public', 1)->where('has_archive', 1)->first()), true);
	    if(!empty($type)){
	    	return cms_found_content_type($type, $vars, $query);
	    }
    }

    //no archive found, lets look for type/slug urls in the database
    //split query into type and query
    $parts = explode('/', $query);
    $type_slug = array_shift($parts);
    $query = implode('/', $parts);
    //get type details so we dont load any none public types
    $type = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content_types')->where('slug', $type_slug)->where('public', '1')->first()), true);
    if(!empty($type)){
    	//now if found lets find the content
    	$content = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content')->where('slug', $query)->where('published', 1)->where('type', $type['type'])->first()), true);
    	if(!empty($content)){
	    	return cms_found_content($content, $vars, $query);
	    }
    }

    //if we got here theres nothing else we can do, lets throw a 404 error
    //header('HTTP/1.0 404 not found');
	//header('Status: 404 Not Found');
	$conf = whmcs_cms_config();
	$page = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content')->where('id', $conf['404_page'])->first()), true);
    //die(print_r($conf, true));
    if(!empty($page)){
    	return cms_found_content($page, $vars, $query);
    }
}


function cms_found_content($content, $vars, $query){
	run_hook('cms/client/view/content', $content, $vars);
	$breadcrumbs = array();

    //add content type crumb
	if($content['type'] != 'page'){
		$type = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content_types')->where('type', $content['type'])->first()), true);
		$breadcrumbs['/'.$type['slug']] = $type['name'];
        $query = $type['slug'] . '/' . $query;
	}


	global $CONFIG;
	if($content['ssl'] == 1){
		if(!$_SERVER['HTTPS'] || $_SERVER['HTTPS'] == 'off'){
			//redirect
			header("Location: ". rtrim($CONFIG['SystemSSLURL'], '/').'/'.$query);
			exit();
		}
	}else{
		if($_SERVER['HTTPS'] && $_SERVER['HTTPS'] == 'on'){
			//redirect
			header("Location: ". str_replace(array('http://', 'https://'), 'http://', rtrim($CONFIG['SystemURL'], '/')).'/'.$query);
			exit();
		}
	}


    //get parent crumbs
    if($content['parent'] != 0){
        $parent_crumbs = array();
        $parent = $content['parent'];
        while($parent != 0){
            $par = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content')->where('id', $parent)->first()), true);
            $parent = $par['parent'];
            $parent_crumbs[$par['slug']] = $par['title'];
        }
        $breadcrumbs += array_reverse($parent_crumbs);
    }

    //add main crumb
	$breadcrumbs[$content['slug']] = $content['title'];

    //set vars
    $_vars = array(
        'cmstpl' => cms_locate_template(array('single_'.$content['id'], 'single_'.$content['type'], 'single')),
        'content' => $content,
        'filename' => $query,
        'id' => $content['id'],
        'meta' => array(),
        'css' => get_query_val('tbladdonmodules', 'value', array('module' => 'cms_settings', 'setting' => 'css')),
        'textcontent' => html_entity_decode($content['content'])
    );

    //add meta values
    foreach(unserialize($content['meta']) as $key => $value){
    	$_vars['meta'][$key] = $value;
    }

    //get custom vars
    $results = run_hook('cms/client/view/vars', array('content' => $content, 'func_vars' => $vars, 'vars' => $_vars));
    foreach($results as $result){
        foreach($result as $key => $value){
            $_vars[$key] = $value;
        }
    }

    $_vars['_lang'] = $vars['_lang'];

    if($content['private'] == true){
        if(!cms_can_access_content($content)){
            $content['restrictions'] = unserialize($content['restrictions']);
            if($content['restrictions']['action'] == '404'){
                header("Location: ".rtrim($CONFIG['SystemURL'], '/') . '/' . get_query_val('mod_whmcs_cms_plus_content', 'slug', array('id' => get_query_val('tbladdonmodules', 'value', array('module' => 'cms_settings', 'setting' => '404_page')))));
                exit();
            }
        }
    }

    //return results
	return array(
        'pagetitle' => $content['title'],
        'breadcrumb' => $breadcrumbs,
        'templatefile' => 'template',
        'requirelogin' => $content['private'],
        'vars' => $_vars
    );
}

function cms_found_content_type($content, $vars, $query){
	run_hook('cms/client/view/type', $content, $vars);


	global $CONFIG;
	if($content['ssl'] == 1){
		if(!$_SERVER['HTTPS'] || $_SERVER['HTTPS'] == 'off'){
			//redirect
			header("Location: ". rtrim($CONFIG['SystemSSLURL'], '/').'/'.$query);
			exit();
		}
	}else{
		if($_SERVER['HTTPS'] && $_SERVER['HTTPS'] == 'on'){
			//redirect
			header("Location: ". str_replace(array('http://', 'https://'), 'http://', rtrim($CONFIG['SystemURL'], '/')).'/'.$query);
			exit();
		}
	}

    //add main crumb
    $breadcrumbs = array();
	$breadcrumbs[$content['slug']] = $content['name'];

    //set vars
    $_vars = array(
        'cmstpl' => cms_locate_template(array('archive_'.$content['type'], 'archive')),
        'type' => $content,
        'filename' => $query,
        'id' => $content['id'],
        'css' => get_query_val('tbladdonmodules', 'value', array('module' => 'cms_settings', 'setting' => 'css'))
    );

    //get custom vars
    $results = run_hook('cms/client/view/type/vars', array('content' => $content, 'func_vars' => $vars, 'vars' => $_vars));
    foreach($results as $result){
        foreach($result as $key => $value){
            $_vars[$key] = $value;
        }
    }

    $_vars['_lang'] = $vars['_lang'];

    //return results
	return array(
        'pagetitle' => $content['name'],
        'breadcrumb' => $breadcrumbs,
        'templatefile' => 'template',
        'requirelogin' => false,
        'vars' => $_vars
    );
}

function cms_locate_template($file_names = array()){
	global $CONFIG;
	foreach($file_names as $filename){
		if(file_exists(ROOTDIR.'/templates/'.$CONFIG['Template'].'/'.$filename.'.tpl')){
			return ROOTDIR.'/templates/'.$CONFIG['Template'].'/'.$filename.'.tpl';
		}
	}
	foreach($file_names as $filename){
		if(file_exists(dirname(__FILE__).'/templates/'.$filename.'.tpl')){
			return dirname(__FILE__).'/templates/'.$filename.'.tpl';
		}
	}
}

