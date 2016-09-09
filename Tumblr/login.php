<?php

	/*
		主に使ってるセッションは
		$_SESSION['tmblr']
		$_SESSION['logined_with_tumblr']
	*/
	session_start();
	require dirname(__FILE__) . '/lib/tumblrPHP.php';
	// エラーの詳細を表示する
	// ini_set("display_errors", On);
	// error_reporting(E_ALL);

	$ck = 'Oun56dhpKxH3WI2r3mrWSecW0YABphqnuR6TQET96I5KslGzn9';
	$cs	= 'nJdtaeaapKesUBHz1VBAwWMHH03RQ1lh4XCU95F8tX0kjLNPQs';

	function redirect_to_main_page() {
		$url = 'http://' . $_SERVER['SERVER_NAME'] . '/index.php' ;
		header('location:' . $url );
		exit;
	}
		
	
	if ( $_SESSION['logined_with_tumblr'] ) {
		redirect_to_main_page();
	}
	
	try {
		if ( !isset( $_SESSION['tmblr'] ) ) {
	
			$_SESSION['tmblr'] = new Tumblr($ck, $cs);
			$token = $_SESSION['tmblr']->getRequestToken();
			$_SESSION['ot'] = $token['oauth_token'];
			$_SESSION['os'] = $token['oauth_token_secret'];
	
			$data = $_SESSION['tmblr']->getAuthorizeURL( $token['oauth_token'] );
			//The user will be directed to the "Allow Access" screen on Tumblr
			header("Location: " . $data);
			exit;
		
		} else {

			$_SESSION['tmblr'] = new Tumblr( $ck, $cs, $_SESSION['ot'], $_SESSION['os'] );
			$token = $_SESSION['tmblr']->getAccessToken( filter_input(INPUT_GET, 'oauth_verifier') );
			$_SESSION['tmblr'] = new Tumblr( $ck, $cs, $token['oauth_token'], $token['oauth_token_secret'] );
			$_SESSION['logined_with_tumblr'] = true ;
			// Regenerate session id for security reasons.
			session_regenerate_id(true); 
			unset( $_SESSION['ot'] );
			unset( $_SESSION['os'] );
			redirect_to_main_page();
		}
	} catch (Exception $e) {
		$_SESSION = array();
		echo $e->getMessage();
		exit();
	}