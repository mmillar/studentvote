<?php
//PHP REST helper function that does not use CURL
function rest_helper($url, $params = null, $verb = 'POST', $format = 'xml')
{
  $cparams = array(
    'http' => array(
      'method' => $verb,
      'ignore_errors' => true
    )
  );
  if ($params !== null) {
    $params = http_build_query($params);
    if ($verb == 'POST') {
      $cparams['http']['content'] = $params;
    } else {
      $url .= '?' . $params;
    }
  }

  $context = stream_context_create($cparams);
  $fp = fopen($url, 'rb', false, $context);
  if (!$fp) {
    $res = false;
  } else {
    // If you're trying to troubleshoot problems, try uncommenting the
    // next two lines; it will show you the HTTP response headers across
    // all the redirects:
    // $meta = stream_get_meta_data($fp);
    // var_dump($meta['wrapper_data']);
    $res = stream_get_contents($fp);
  }

  if ($res === false) {
    throw new Exception("$verb $url failed: $php_errormsg");
  }

  switch ($format) {
    case 'json':
      $r = json_decode($res);
      if ($r === null) {
        throw new Exception("failed to decode $res as json");
      }
      return $r;

    case 'xml':
      $r = simplexml_load_string($res);
      if ($r === null) {
        throw new Exception("failed to decode $res as xml");
      }
      return $r;
  }
  return $res;
}

$x7server = $_POST['x7server'];
$ks = $_POST['ks'];
$eid = $_POST['eid'];
$name = $_POST['name'];
$plcontent = $_POST['plcontent'];
$x7bloghome = $_POST['x7bloghome'];

if ( eregi ( "$x7bloghome", $_SERVER['HTTP_REFERER'] ) )
{
$updateresult = rest_helper("$x7server/api_v3/?service=playlist&action=update",
					 array(
						'ks' => $ks,
						'id' => $eid,
                                                'playlist:name' => $name,
                                                'playlist:playlistContent' => $plcontent
					 ), 'POST'
					 );
$newid = (string) $updateresult->result->id;
echo "$newid";
} else {
    exit;
}
?>