<?php
use Illuminate\Database\Capsule\Manager as Capsule;
// *************************************************************************
// *                                                                       *
// * WHMCS CMS Plus Module						   						   *
// * Copyright (c) No Half Pixels. All Rights Reserved,                    *
// * Release Date: 29th January 2017                                       *
// * Version 2.0.0 Stable                                                  *
// *                                                                       *
// *************************************************************************
// *                                                                       *
// * Email: contact@nohalfpixels.com                                       *
// * Website: http://nohalfpixels.com                                      *
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
	

if(!class_exists('nhp_addon_license_check') && file_exists(dirname(dirname(__FILE__)).'/nhp_license_check/checkfile.php')){
	require_once(dirname(dirname(__FILE__)).'/nhp_license_check/checkfile.php');
}
global $whmcs_cms_plus;
if(class_exists('nhp_addon_license_check') && !is_object($whmcs_cms_plus)){
	$whmcs_cms_plus = new nhp_addon_license_check(array('module' => 'whmcs_cms_plus', 'secret' => 't6rg83gt937f34g'));
}

if(!is_object($whmcs_cms_plus)){
	logActivity("Cannot activate module due to license class not found, or functioning correctly.");
	return;
}

require_once(dirname(__FILE__) . '/functions/content_types.php');
require_once(dirname(__FILE__) . '/functions/settings.php');
require_once(dirname(__FILE__) . '/functions/comments.php');
require_once(dirname(__FILE__) . '/functions/help.php');


function whmcs_cms_config(){
	$ret = array();
	$settings = Capsule::table('tbladdonmodules')->where('module', 'whmcs_cms_plus')->get();
	foreach($settings as $setting){
		$ret[$setting->setting]= $setting->value;
	}
	$settings = Capsule::table('tbladdonmodules')->where('module', 'cms_settings')->get();
	foreach($settings as $setting){
		$ret[$setting->setting]= $setting->value;
	}
	return $ret;
}



function cms_display_form($fields = array(), $values = array(), $action = '', $vars, $hidden_fields = array()){
	ob_start();

	echo '<form action="'.$action.'" method="post" enctype="multipart/form-data">';
		foreach($hidden_fields as $key => $value){
			echo '<input type="hidden" name="'.$key.'" value="'.$value.'"/>';
		}
		echo '<table>';
		foreach($fields as $key => $field){
			echo '<tr class="form-field" id="tr-field-'.$key.'">';
				echo '<th>';
					echo $field['title'];
				echo '</th><td>';
					switch($field['type']){
						case 'checkbox':
							echo '<label><input type="'.$field['type'].'" id="field-'.$key.'" name="settings['.$key.']" value="1"'.(($values[$key] == 1) ? ' checked="checked"' : '').'/> ' . $field['checkbox_desc'] . '</label>';
						break;

						case 'select':
							echo '<select id="field-'.$key.'" name="settings['.$key.']">';
							foreach($field['options'] as $option => $label){
								echo '<option value="'.$option.'"'.(($values[$key] == $option) ? ' selected="selected"' : '').'> ' . $label . '</option>';
							}
							echo '</select>';
						break;
						case 'file':
							echo '<input type="file" name="'.$key.'" id="field-'.$key.'">';
						break;								
						case 'textarea':
						
							echo '<textarea id="field-'.$key.'" style="width: 99%;" name="settings['.$key.']" cols="60" rows="6" class="'.$field['class'].'"'.((isset($field['ace'])) ? ' data-editor="'.$field['ace'].'"' : '').'>'.$values[$key].'</textarea>';
						break;
						case 'restrictions':
							$values[$key] = unserialize($values[$key]);
							echo '<label><input type="checkbox" id="field-'.$key.'-enable" name="settings['.$key.'][enable]" value="1"'.(($values[$key]['enable'] == 1) ? ' checked="checked"' : '').'/> ' . $field['enable_desc'] . '</label>';
							echo '<div id="restrictions">';
								echo '<p>';
								echo $field['match'];
								echo ' <select id="field-'.$key.'-match" name="settings['.$key.'][match]"><option value="all"'.(($values[$key]['match'] == 'all') ? ' selected="selected"' : '').'>'.$field['all'].'</option><option value="any"'.(($values[$key]['match'] == 'any') ? ' selected="selected"' : '').'>'.$field['any'].'</option></select> ';
								echo $field['conditions'];
								echo ' <select id="field-'.$key.'-action" name="settings['.$key.'][action]"><option value="404"'.(($values[$key]['action'] == '404') ? ' selected="selected"' : '').'>'.$field['404'].'</option><option value="display"'.(($values[$key]['action'] == 'display') ? ' selected="selected"' : '').'>'.$field['display'].'</option></select> ';
								echo '</p>';

								echo '<table width="100%" class="form" border="0" cellspacing="2" cellpadding="3">';
									echo '<tbody>';
										echo '<tr><th width="33%">'.$field['groups'].'</th><th width="33%">'.$field['products'].'</th><th>'.$field['domains'].'</th></tr>';
										echo '<tr>';
											echo '<td class="fieldarea"><select name="settings['.$key.'][groups][]" multiple="multiple" style="width:100%;height:140px;"><option value="disabled"'.((in_array('disabled', $values[$key]['groups'])) ? ' selected="selected"' : '').'>'.$field['disabled'].'</option>';
											$groups = Capsule::table('tblclientgroups')->get();
											foreach($groups as $group){
												$group = json_decode(json_encode($group), true);
												echo '<option value="'.$group['id'].'"'.((in_array($group['id'], $values[$key]['groups'])) ? ' selected="selected"' : '').'>'.$group['groupname'].'</option>';
											}
											echo '</select></td>';
											echo '<td class="fieldarea"><select name="settings['.$key.'][products][]" multiple="multiple" style="width:100%;height:140px;"><option value="disabled"'.((in_array('disabled', $values[$key]['groups'])) ? ' selected="selected"' : '').'>'.$field['disabled'].'</option>';
											//<optgroup label="Shared Hosting"><option value="1" selected="selected">Basic</option></optgroup><optgroup label="Reseller Hosting"><option value="2">Reseller Basic</option></optgroup><optgroup label="VOIP"><option value="3">VOIP 1</option></optgroup>

											$groups = Capsule::table('tblproductgroups')->orderBy('order', 'asc')->get();
											foreach($groups as $group){
												$group = json_decode(json_encode($group), true);
												echo '<optgroup label="'.$group['name'].'">';
												$products = Capsule::table('tblproducts')->where('gid', $group['id'])->get();
												foreach($products as $product){
													$product = json_decode(json_encode($product), true);
													echo '<option value="'.$product['id'].'"'.((in_array($product['id'], $values[$key]['products'])) ? ' selected="selected"' : '').'>'.$product['name'].'</option>';
												}
												echo '</optgroup>';
											}

											echo '</select></td>';
											echo '<td class="fieldarea"><input type="text" name="settings['.$key.'][domains]" value="'.$values[$key]['domains'].'"><br>'.$field['domain_desc'].'</td>';
										echo '</tr>';
									echo '</tbody>';
								echo '</table>';

								echo '<br/><div id="restrictions-display"><textarea id="field-'.$key.'-display" style="width: 99%;" name="settings['.$key.'][display]" cols="60" rows="6" class="ckeditor">'.$values[$key]['display'].'</textarea></div>';

							echo '</div>';
							break;
						case 'meta':
							echo '<table width="100%" cellspacing="0" cellpadding="0" class="meta">';
							echo '<thead><tr><th width="20px" style="width: 20px !important;">&nbsp;</th><th>Name</th><th>Value</th></tr></thead>';
							echo '<tbody>';
								$values[$key] = unserialize($values[$key]);
								foreach($values[$key] as $_key => $value){
									$uid = uniqid();
									echo '<tr>';
										echo '<td width="20px" onclick="$(this).closest(\'tr\').remove();" style="cursor: pointer;width: 20px !important;"><img src="images/delete.gif"/></td>';
										echo '<td width="50%"><input type="text" style="width: 99%;" name="settings['.$key.']['.$uid.'][key]" value="'.$_key.'"/></td>';
										echo '<td width="50%"><input type="text" style="width: 99%;" name="settings['.$key.']['.$uid.'][value]" value="'.$value.'"/></td>';
									echo '</tr>';
								}
							echo '</tbody>';
							echo '<tfoot class="clone"><tr>';
								echo '<td width="20px" onclick="$(this).closest(\'tr\').remove();" style="cursor: pointer;width: 20px !important;"><img src="images/delete.gif"/></td>';
								echo '<td width="50%"><input type="text" style="width: 99%;" name="settings['.$key.'][##clone##][key]" value=""/></td>';
								echo '<td width="50%"><input type="text" style="width: 99%;" name="settings['.$key.'][##clone##][value]" value=""/></td>';
							echo '</tr></tfoot>';
							echo '<tfoot><tr><td colspan="3"><a href="#" class="btn">'.$vars['_lang']['add'].'</a></td></tr></tfoot></table>';
						break;
						default:
							echo '<input type="'.$field['type'].'" id="field-'.$key.'" style="width: 99%;" name="settings['.$key.']" value="'.$values[$key].'"/>';
					}
					if($field['desc'] != ''){
						echo '<p>'.$field['desc'].'</p>';
					}
				echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '<p><input type="submit" value="'.$vars['_lang']['settings_save'].'"/></p>';
	echo '</form>';
	return ob_get_clean();
}



add_hook('cms/admin/page/top', 5, 'cms_add_admin_css');
function cms_add_admin_css($vars){
	echo '<style type="text/css">';
		echo '.contentbox{text-align: left;border-style: solid;}';
		echo '.contentbox a{font-weight:bold;text-decoration: none;}';
		echo '.cmscontentarea{border: 1px solid #cccccc;border-top: none;padding: 10px;position: relative;}';
		echo '.cmscontentarea h3{border-bottom: 1px solid #ccc;padding-bottom: 10px;}';
		echo '.cmscontentarea table{width: 100%;cellpadding:0px;cellspacing: 0px;border-collapse: collapse;}';
		echo '.cmscontentarea table th,.cmscontentarea table td{padding: 10px;border: 1px solid #ccc;}';
		echo '.cmscontentarea table th{background: #f7f7f7;text-align: left;width: 25%;vertical-align: top;}';
		echo '.cmscontentarea table .center{text-align: center;}';
		echo 'table tfoot.clone{display: none;}';
		run_hook('cms/admin/page/css', $vars);
	echo '</style>';
}

add_hook('cms/admin/page/top', 10, 'cms_add_admin_js');
function cms_add_admin_js($vars){
	?>
	<script>
	var guid = (function() {
	  function s4() {
	    return Math.floor((1 + Math.random()) * 0x10000)
	               .toString(16)
	               .substring(1);
	  }
	  return function() {
	    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
	           s4() + '-' + s4() + s4() + s4();
	  };
	})();
    $(document).ready(function(){

        if($('.contentbox a.active').length == 0){
            $('.contentbox a:first-child').addClass('active');
            $('.cmscontentarea > div').hide();
            $('.cmscontentarea > div:first-child').show().addClass('active');
        }else{
            $('.cmscontentarea > div:not(.active)').hide();
        }

        $('.meta tfoot a.btn').click(function(){
        	var clone = $(this).closest('table').find('tfoot.clone').html();
        	var reg = new RegExp("##clone##","g");
        	var key = guid();
        	$(this).closest('table').find('tbody').append(clone.replace(reg, key));
        	return false;
        });

        $('#cmsmenu a').click(function(){

            if($(this).hasClass('active')){
                return false;
            }
            var link = $(this);
            $('.contentbox a').removeClass('active');
            $('.cmscontentarea > .active').fadeOut('fast', function(){
                $(this).removeClass('active');
                $(link).addClass('active');
                $($(link).attr('href')).fadeIn('fast', function(){
                    $(this).addClass('active');
                });
            });
            return false;
        });

        $('textarea[data-editor]').each(function () {
            var textarea = $(this);
 
            var mode = textarea.data('editor');
 
            var editDiv = $('<div>', {
                position: 'absolute',
                width: textarea.width(),
                height: textarea.height() + 80,
                'class': textarea.attr('class')
            }).insertBefore(textarea);
 
            textarea.css('visibility', 'hidden').css('height', 0);
 
            var editor = ace.edit(editDiv[0]);
            editor.renderer.setShowGutter(true);
            editor.getSession().setValue(textarea.val());
            editor.getSession().setMode("ace/mode/" + mode);
            editor.setTheme("ace/theme/monokai");
            
            // copy back to textarea on form submit...
            textarea.closest('form').submit(function () {
                textarea.val(editor.getSession().getValue());
            })
 
        });

		if($('#tr-field-private input:checked').length > 0){
			$('#tr-field-restrictions').show();
		}else{
			$('#tr-field-restrictions').hide();
		}

		$('#tr-field-private input').change(function(){
			if($(this).is(':checked')){
				$('#tr-field-restrictions').fadeIn();
			}else{
				$('#tr-field-restrictions').fadeOut();
			}
		});

		if($('#field-restrictions-enable:checked').length > 0){
			$('#restrictions').show();
		}else{
			$('#restrictions').hide();
		}

		$('#field-restrictions-enable').change(function(){
			if($(this).is(':checked')){
				$('#restrictions').fadeIn();
			}else{
				$('#restrictions').fadeOut();
			}
		});



		if($('#field-restrictions-action').val() == 'display'){
			$('#restrictions-display').show();
		}else{
			$('#restrictions-display').hide();
		}

		$('#field-restrictions-action').change(function(){
			if($(this).val() == 'display'){
				$('#restrictions-display').fadeIn();
			}else{
				$('#restrictions-display').fadeOut();
			}
		});

        <?php run_hook('cms/admin/page/js', $vars);?>
    });
    </script>
    <?php
}




function cms_sanitize_string( $filename ) {
	$filename = strtolower($filename);
	$special_chars = array("?", "[", "]", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", chr(0));
	$filename = str_replace($special_chars, '', $filename);
	$filename = preg_replace('/[\s-]+/', '-', $filename);
	$filename = trim($filename, '.-_');
	return cms_remove_accents($filename);
}//function

function cms_remove_accents($string) {
	if ( !preg_match('/[\x80-\xff]/', $string) )
		return $string;

	$chars = array(
		// Decompositions for Latin-1 Supplement
		chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
		chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
		chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
		chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
		chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
		chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
		chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
		chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
		chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
		chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
		chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
		chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
		chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
		chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
		chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
		chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
		chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
		chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
		chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
		chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
		chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
		chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
		chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
		chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
		chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
		chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
		chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
		chr(195).chr(191) => 'y',
		// Decompositions for Latin Extended-A
		chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
		chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
		chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
		chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
		chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
		chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
		chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
		chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
		chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
		chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
		chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
		chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
		chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
		chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
		chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
		chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
		chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
		chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
		chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
		chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
		chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
		chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
		chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
		chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
		chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
		chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
		chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
		chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
		chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
		chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
		chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
		chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
		chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
		chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
		chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
		chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
		chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
		chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
		chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
		chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
		chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
		chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
		chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
		chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
		chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
		chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
		chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
		chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
		chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
		chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
		chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
		chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
		chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
		chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
		chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
		chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
		chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
		chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
		chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
		chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
		chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
		chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
		chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
		chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
	);

	$string = strtr($string, $chars);

	return $string;
}





function cms_requested_url(){
    $s = &$_SERVER;
    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
    $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    $uri = $protocol . '://' . $host . $s['REQUEST_URI'];
    $segments = explode('?', $uri, 2);
    $url = $segments[0];
    return $url;
}




function cms_get_excerpt($id, &$tpl_source, &$smarty_obj){
	//assign meta values so excerpt gets the same treatment
	$meta = unserialize(get_query_val('mod_whmcs_cms_plus_content', 'meta', array('id' => $id)));
	$smarty_obj->assign('meta', $meta);
	//normal proccessing

	$content = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content')->where('id', $id)->get()), true);
	if($content['private'] == true){
		if(!cms_can_access_content($content)){
			$tpl_source = 'Restricted Content!';
			return true;
		}
	}

	$tpl_source = html_entity_decode(get_query_val('mod_whmcs_cms_plus_content', 'content', array('id' => $id)));
	$tpl_source = implode(' ', array_slice(explode(' ', $tpl_source), 0, 70));
	return true;
}
function cms_get_template($id, &$tpl_source, &$smarty_obj){

	$content = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content')->where('id', $id)->get()), true);
	if($content['private'] == true){
		if(!cms_can_access_content($content)){
			$content['restrictions'] = unserialize($content['restrictions']);
			$tpl_source = html_entity_decode($content['restrictions']['display']);
			return true;
		}
	}

	$tpl_source = html_entity_decode(get_query_val('mod_whmcs_cms_plus_content', 'content', array('id' => $id)));
	return true;
}
function cms_get_timestamp($id, &$tpl_timestamp, &$smarty_obj){$tpl_timestamp = time();return true;}
function cms_get_secure($id, &$smarty_obj){return true;}
function cms_get_trusted($id, &$smarty_obj){}


function cms_head_output($vars){
	global $CONFIG;
	if(isset($vars['content']['head_content']) && !empty($vars['content']['head_content'])){
		return html_entity_decode($vars['content']['head_content']);
	
	}
	
		$content = json_decode(json_encode(Capsule::table('mod_whmcs_cms_plus_content')->where('id', $id)->get()), true);
	if($content['private'] == true){
		if(!cms_can_access_content($content)){
			$content['restrictions'] = unserialize($content['restrictions']);
			$tpl_source = html_entity_decode($content['restrictions']['display']);
			return true;
		}
	}

}
add_hook('ClientAreaHeadOutput', 1000, 'cms_head_output');




function cms_insert_int($val){
	return intval($val);
}



function cms_can_access_content($content){
	$data = unserialize($content['restrictions']);
	if(isset($_SESSION['adminid'])){
		return true;
	}
	if($data['enable'] != '1'){
		return true;
	}
	if(!isset($_SESSION['uid'])){
		return false;
	}
	$checks = array();
	//groups
	if(!in_array('disabled', $data['groups']) && !empty($data['groups'])){
		$clientgroup = get_query_val('tblclients', 'groupid', array('id' => $_SESSION['uid']));
		if(in_array($clientgroup, $data['groups'])){
			$checks[] = true;
		}else{
			$checks[] = false;
		}
	}
	//products
	if(!in_array('disabled', $data['products']) && !empty($data['products'])){
		foreach($data['products'] as $pid){
			$count = Capsule::select("SELECT COUNT(*) as count FROM `tblhosting` WHERE `userid`='".$_SESSION['uid']."' AND `domainstatus` = 'Active' AND `packageid` = '".$pid."';");
			if($count['count'] > 0){
				$checks[] = true;
			}else{
				$checks[] = false;
			}
		}
	}
	//domains
	if($data['domains'] != '' && $data['domains'] != '0'){
		$count = Capsule::select("SELECT COUNT(*) as count FROM `tbldomains` WHERE `userid`='".$_SESSION['uid']."' AND `status` = 'Active';");
		if($count['count'] >= $data['domains']){
			$checks[] = true;
		}else{
			$checks[] = false;
		}
	}

	if($data['match'] == 'any'){
		if(in_array(true, $checks) || empty($checks)){
			return true;
		}else{
			return false;
		}
	}elseif($data['match'] == 'all'){
		if(in_array(false, $checks)){
			return false;
		}else{
			return true;
		}
	}
	return true;
}
