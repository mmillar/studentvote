<?php

$ks = $_POST["ks"];
$x7editortype = $_POST["x7editortype"];
$eid = $_POST["eid"];
$x7name = $_POST["x7name"];
$x7kalpartnerid = $_POST["x7kalpartnerid"];
$user_login = $_POST["user_login"];
$x7server = $_POST["x7server"];
$x7bloghome = $_POST['x7bloghome'];

if ( eregi ( "$x7bloghome", $_SERVER['HTTP_REFERER'] ) )
{
$service_url = "$x7server/index.php/kmc/createmix";
       $curl = curl_init($service_url);
       $curl_post_data = array(
            'ks' => $ks,
	    'editor_type' => $x7editortype,
            'entry_id' => $eid,
            'entry_name' => $x7name,
            'partner_id' => $x7kalpartnerid,
            'user_id' => $user_login
        );
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($curl, CURLOPT_POST, true);
       curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
       $curl_response = curl_exec($curl);
       curl_close($curl);
echo "$curl_response";
} else {
    exit;
}
?>