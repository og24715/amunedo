<?php
//ライブラリの読み込み(フルパス)
require dirname(__FILE__) . '/library/TwistOAuth.phar';
//セッション開始
@session_start();
//APIkey
$ck = 'fcGerhXv13PvyjbavklEQ6EO1';
$cs = 'j8GBRE6GJBSh6BbV1sXMTBtvRqBnJuaebhMnJ3DlLzA9lAfOJx';

function redirect_to_main_page() {
	$url = 'http://' . $_SERVER['SERVER_NAME'] . '/index.php' ;
	header('location:' . $url );
	exit;
}



if ( isset( $_SESSION['logined'] ) ) {
	redirect_to_main_page();
}

try {
	
	//初回アクセス時の処理(リクエストトークンを取得する)
	if( !isset($_SESSION['to']) ){
		echo 'first access' ; 
		$_SESSION['to'] = new TwistOAuth($ck, $cs);
		//コールバックURLを生成
		$oauth_callback = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
		//リクエストトークンを取得
		$_SESSION['to'] = $_SESSION['to']->renewWithRequestToken( $oauth_callback );
		//認証ページ(Twitter)へ
		header( 'location:' . $_SESSION['to']->getAuthorizeUrl() );
	} 
	//Twitterから帰ってきた時の処理（アクセストークンを取得する）
	else {
		//OAuthTokenをAccessTokenに更新する
		$_SESSION['to'] = $_SESSION['to']->renewWithAccessToken( filter_input(INPUT_GET, 'oauth_verifier') );
		$_SESSION['logined'] = true ;
		// Regenerate session id for security reasons.
		session_regenerate_id(true); /* IMPORTANT */
		// Redirect to the main page.
		redirect_to_main_page();
	}

} catch (Exception $e) {
	$_SESSION = array();
	echo $e->getMessage();
	exit();
}
