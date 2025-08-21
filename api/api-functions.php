<?php

function parseHeaders( $headers ) {
	$head = array();
	foreach ( $headers as $k=>$v ) {
		$t = explode( ':', $v, 2 );
		if ( isset($t[1]) ) {
			$head[ trim($t[0]) ] = trim( $t[1] );
		} else {
			$head[] = $v;
			if ( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) ) {
				$head['reponse_code'] = intval($out[1]);
			}
	}
	}
	return $head;
}

function response($code,$msg,$data='',$debug='') {

	global $C;
	
	$response['code'] = (int)$code;
	$response['message'] = $msg;
	if ( $data <> '' ) {
		$response['data'] = $data;
	}
	if ( $debug <> '' and $C['debug'] > 0 ) {
		$debug .= "GET:".var_export($_GET,true)."\n";
		$debug .= "POST:".var_export($_POST,true)."\n";
		$debug .= "COOKIE:".var_export($_COOKIE,true)."\n";
		$response['debug'] = $debug;
	}

	echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	exit;
}

?>