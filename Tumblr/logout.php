<?
	session_start();
	//セッションの破棄
	unset( $_SESSION['tmblr'] );
	unset( $_SESSION['logined_with_tumblr'] );
	unset( $_SESSION['tumblogs'] );
	//session_destroy();
	if ( $_SERVER['HTTP_REFERER'] ){
	 	header('location:' . $_SERVER['HTTP_REFERER']) ;
	 }