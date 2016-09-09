<?php
	//TwistOauthの読み込み
	require dirname(__FILE__) . '/library/TwistOAuth.phar';
	//セッション開始
	@session_start();
	//エラーの詳細を表示する
	//ini_set("display_errors", On);
	//error_reporting(E_ALL);
	//ヘッダー情報
	header('Content-type: text/html; charset=utf-8');

	$id = filter_input( INPUT_POST, 'id' );
	$params = array( 'id' => $id );
	$_SESSION['to']->post('favorites/destroy', $params);
?>