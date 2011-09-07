<?php

$ks = $_POST["ks"];  //kaltura session
$x7entrytype = $_POST["x7entrytype"]; //will be either mix, playlist or video
$eid = $_POST["eid"];
$x7server = $_POST["x7server"];
$x7bloghome = $_POST['x7bloghome'];

if ( eregi ( "$x7bloghome", $_SERVER['HTTP_REFERER'] ) )
{
//if is single entry
if ($x7entrytype == "media"){
$service_url = "$x7server/api_v3/?service=media&action=delete";
       $curl = curl_init($service_url);
       $curl_post_data = array(
            'ks' => $ks,
            'entryId' => $eid
        );
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($curl, CURLOPT_POST, true);
       curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
       $curl_response = curl_exec($curl);
       curl_close($curl);
echo "$curl_response";
}

//if is mix
if ($x7entrytype == "mix"){
$service_url = "$x7server/api_v3/?service=mixing&action=delete";
       $curl = curl_init($service_url);
       $curl_post_data = array(
            'ks' => $ks,
            'entryId' => $eid
        );
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($curl, CURLOPT_POST, true);
       curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
       $curl_response = curl_exec($curl);
       curl_close($curl);
echo "$curl_response";
}

//if is playlist
if ($x7entrytype == "playlist"){
$service_url = "$x7server/api_v3/?service=playlist&action=delete";
       $curl = curl_init($service_url);
       $curl_post_data = array(
            'ks' => $ks,
            'id' => $eid
        );
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($curl, CURLOPT_POST, true);
       curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
       $curl_response = curl_exec($curl);
       curl_close($curl);
echo "$curl_response";
}
} else {
    exit;
}
?>