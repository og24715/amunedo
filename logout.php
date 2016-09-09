<?
	session_start();
	//セッションの破棄
	$_SESSION[] = array();
	session_destroy();
	if ( $_SERVER['HTTP_REFERER'] ){
	 	header('location:' . $_SERVER['HTTP_REFERER']) ;
	 }