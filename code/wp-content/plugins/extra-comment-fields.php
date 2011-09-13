<?php
/*
Plugin Name: Extra Comment Fields
Plugin URI: http://www.ideashower.com
Description: Add additional fields on the comment form
Version: 1.2
Author: Nate Weiner
Author URI: http://www.ideashower.com
*/
?>
<?php
/*  This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
?>
<?php

add_filter('comment_text', 'ecf_comment_text');
add_filter('manage_comments_nav', 'ecf_addOnComments');

add_filter('comment_edit_pre', 'ecf_getComment');
add_action('submitcomment_box', 'ecf_edit_comment_insert');
add_filter('comment_save_pre', 'ecf_edit_comment');
add_action('delete_comment', 'ecf_delete_comment');


function ecf_comment_text($commentTxt) { global $comment;
	if (preg_match('/edit-comments.php/', $_SERVER['SCRIPT_NAME'])) {
		
		$rider = "
		<p>
		";
		
		foreach($comment as $key => $value) {
			$subs = array();
			if (preg_match('/extra_(.+)/', $key, $subs)) {
				$rider .= '<strong>'.$subs[1].':</strong> '.$value.'<br />';
			}
		}
		
		$rider .="
		</p>
		";
		
		return $commentTxt.$rider;
	}
	return $commentTxt;
}

function ecf_edit_comment_insert() { global $comment;
	$fields = ecf_fieldList();
	
	if (!empty($fields)) {
		$output = '
		<div id="ecf_move_to_form" style="display: none">
		';
		
		$vars = get_object_vars($comment);
		
		foreach($fields as $var) {
			
			$id = 'extra_'.$var;
			
			$output .= '
			<div id="'.$id.'div" class="stuffbox">
			<h3>'.$var.'</h3>
			<div class="inside">
			<input type="text" id="newcomment_'.$id.'" name="newcomment_extra_'.$id.'" size="30" value="'.attribute_escape( $vars['extra_'.$var] ).'" />
			</div>
			</div>
			';
		}
		
		$output .= '
		</div>
		<script type="text/javascript">
			function ecf_move_extras() {
				document.getElementById("uridiv").parentNode.insertBefore(document.getElementById("ecf_move_to_form"), document.getElementById("uridiv").nextSibling);
				document.getElementById("ecf_move_to_form").style.display="block";
			}
			if (window.addEventListener){ 
			   window.addEventListener("load", ecf_move_extras, false); 
			 } else if (obj.attachEvent){ 
			   var r = obj.attachEvent("onload", ecf_move_extras); 
			 }
		</script>
		';
		print $output;
	}
}
function ecf_edit_comment($comment_content) { global $wpdb;
	if ($_POST['action']=='editedcomment') {
		$fields = ecf_fieldList();
		if (!empty($fields)) {
			$str = '';
			foreach($fields as $var) {
				$str .= "$var = '".$_POST['newcomment_extra_extra_'.$var]."',";
			}
	
			$wpdb->query(
			"UPDATE ".$wpdb->prefix."comments_extra SET
				".rtrim($str, ',')."
			WHERE comment_ID = '".$_POST['comment_ID']."'" );
			
		}
	}
	return $comment_content;
}
function ecf_delete_comment($id) { global $wpdb;
	$wpdb->query(
			"DELETE FROM ".$wpdb->prefix."comments_extra
			WHERE comment_id = '$id' LIMIT 1" );

}

add_action('preprocess_comment', 	'ecf_getFields');
add_action('comment_post', 			'ecf_saveFields');
add_action('comments_array',		'ecf_getComments', 10, 2);

add_action('activate_extra-comment-fields.php', 'ecf_install');

add_action('admin_menu', 'ecf_menu');


function ecf_getFields($comment) {
	global $ecf_extra_vars;
	$ecf_extra_vars = array();
	$fields = ecf_fieldList();
	if (!empty($fields)) {
		foreach($fields as $var) {
			$ecf_extra_vars[ $var ] = $_POST[$var];
		}
	}
	return $comment;
}
function ecf_saveFields($comment_id) {
	global $ecf_extra_vars, $wpdb;
	$qry = '';
	
	if ($comment_id && !empty($ecf_extra_vars)) {
		foreach($ecf_extra_vars as $var => $value) {
			$cols .= "`$var`,";
			$vals .= "'$value',";			
		}
			
		$result = $wpdb->query("INSERT INTO ".$wpdb->prefix."comments_extra 
		(comment_ID, ".rtrim($cols,',').") VALUES ('$comment_id', ".rtrim($vals,',').")
		");		
	}
}
function ecf_qryStr($fields) {
	$qry = '';
	foreach($fields as $var) {
		$qry .= 'xc.'.$var.',';
	} 	
	return rtrim($qry,',');
}
function ecf_getComment($commentTxt) { global $comment;
	$comments[0] = $comment;
	$comments = ecf_addOnComments($comments);
	$comment = $comments[0];
	return $comment->comment_content;
}
function ecf_addOnComments($overrideComments=0) { global $comments, $wpdb;
	$comments = (($overrideComments)?($overrideComments):($comments));
	if (!empty($comments)) {
		
		$compareComments = $comments;		
		$fields = ecf_fieldList();
		reset($comments);
		$firstComment = current($comments);
		
		$sql = "SELECT xc.comment_ID, ".ecf_qryStr($fields)." 
				FROM ".$wpdb->prefix."comments c, ".$wpdb->prefix."comments_extra xc
				WHERE c.comment_ID <= '".$firstComment->comment_ID."' AND c.comment_ID = xc.comment_ID
				ORDER BY c.comment_ID DESC
				LIMIT 45";
		$result = $wpdb->get_results($sql);	
		for($i=0; $i<count($result); $i++) {	
			$objectIndex = ecf_whatCommmentObject($result[$i]->comment_ID, $compareComments);
			if (isset($comments[$objectIndex])) {
				foreach($fields as $var) {
					$nvar = 'extra_'.$var;
					$comments[$objectIndex]->$nvar = $result[$i]->$var;
				}
				unset($compareComments[$objectIndex]);
			}
		}
	}
	return $comments;
}
function ecf_getComments($comments, $post_id) { 
	global $wpdb;
	$compareComments = $comments;
	$fields = ecf_fieldList();

	if (!empty($comments) && !empty($fields)) {
		$result = $wpdb->get_results("SELECT xc.comment_ID, ".ecf_qryStr($fields)."  
								FROM wp_comments c, wp_comments_extra xc
								WHERE c.comment_post_ID = '$post_id' AND c.comment_ID = xc.comment_ID");	
		for($i=0; $i<count($result); $i++) {
			$objectIndex = ecf_whatCommmentObject($result[$i]->comment_ID, $compareComments);
			foreach($fields as $var) {
				$nvar = 'extra_'.$var;
				$comments[$objectIndex]->$nvar = $result[$i]->$var;
			}
			unset($compareComments[$objectIndex]);
		}
	}
	return $comments;
}
function ecf_whatCommmentObject($comment_id, $comments) {
	if (!empty($comments)) {
	foreach($comments as $objectIndex => $comment) {
		if ($comment->comment_ID == $comment_id) {
			return $objectIndex;
		}
	}
	}
	return -1;
}
function ecf_fieldList() {
	global $wpdb;
	$fields = array();
	$result = $wpdb->get_results("SHOW COLUMNS FROM `".$wpdb->prefix."comments_extra`");
	if (!empty($result)) {
		foreach($result as $resultArr) {
			$fields[] = $resultArr->Field;
		}
	}
	unset($fields[0]);
	return $fields;
}

// ---- //

function ecf_install() {
	global $wpdb;
	
// 	$table_name = $wpdb->prefix . "".$wpdb->prefix."comments_extra";
	if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."comments_extra'") != $wpdb->prefix.'comments_extra') {
		$sql = "CREATE TABLE `".$wpdb->prefix."comments_extra` (
				`comment_id` BIGINT NOT NULL ,
				PRIMARY KEY ( `comment_id` )
				)";
		
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);
	}
	
}

// ---- //

function ecf_menu() {
	add_options_page('Extra Comment Fields', 'Extra Comment Fields', 8, 'ecf_options1', 'ecf_options_page');
}
function ecf_options_page() {
	global $wpdb;
		
	$fields = ecf_fieldList();
	if (empty($fields)) { $fields = array(); }
	
	 print '
	<br />
	<h2>Extra Comment Fields</h2>
	
	<div style="padding: 15px">';
	
	if (!empty($_POST['new_var'])) {
		if (ereg('^([a-zA-Z0-9_-])+$', $_POST['new_var'])) {
			if (!in_array($_POST['new_var'], $fields)) {
				$sql = "ALTER TABLE `".$wpdb->prefix."comments_extra` ADD `".$_POST['new_var']."` TEXT NOT NULL ;";
				$wpdb->query( $sql );	
				$fields[] = $_POST['new_var'];
			} else {
				print '<strong style="#FF0000">That variable is already taken!</strong>';
			}
		} else {
			print '<strong style="#FF0000">You entered an invalid variable name!  Only use letters, numbers, and/or dashes.  No spaces or other characters are allowed!</strong>';
		}
	}
	
	$alter = '';
	foreach($fields as $field) {
		if ($_POST['rmv_'.$field]) {
			$alter .= "DROP `$field` ,";
		}
	}
	if (!empty($alter)) { 
		$alter = "ALTER TABLE `".$wpdb->prefix."comments_extra` ".rtrim($alter,','); 
		$wpdb->query( $alter );	
		$fields = ecf_fieldList();
	}
	
   	print '
		<form action="" method="post">
		
		
		<h4>Add New Field</h4>
		<p>
		Variable: <em>(This needs to be unique and letters, numbers, and/or dashes.  In the comments array the index key will be extra_VARIABLENAMEYOUENTER.)</em><br />
		<input type="text" name="new_var" value="'.$_POST['new_var'].'" /><br />
		<input type="submit" value="Add Field" /><br />
		</p>
		
		<hr />
		
		<h4>Current Fields</h4>
		';
	
	if (!empty($fields)) {
		print '<strong>WARNING:</strong> Deleteing a variable will remove ANY data assigned to that variable in previous comments!!';
		print '<ul>';
		foreach($fields as $field) {
			print '<li>'.$field.' &nbsp;&nbsp;<input type="checkbox" name="rmv_'.$field.'" value="1" /><em>Delete</em></li>';
		}	
		print '</ul>';
	} else {
		print '<p><em>You have no extra fields defined yet.</em></p>';	
	}
	
	print '
		
		<input type="submit" value="Save Changes" />
			
		</form>	
		
	</div>
	';
}

?>