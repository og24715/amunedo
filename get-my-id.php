<?php
	//TwistOauthの読み込み
	require dirname(__FILE__) . '/library/TwistOAuth.phar';
	//セッション開始
	@session_start();
	
	if( isset( $_SESSION['to'] ) ) {
		if ( !isset( $_SESSION['myID'] ) ) {
			$params = array(
				'include_entities' => false,
				'skip_status' => true, 
			);
			$credentials = $_SESSION['to']->get('account/verify_credentials', $params);
			$_SESSION['myID'] = $credentials->screen_name;
		}
	} else { $_SESSION['myID'] = NULL; }
?>