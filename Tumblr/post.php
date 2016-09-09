<?
	// tumblrのライブラリ読み込み
	require dirname(__FILE__) . '/lib/tumblrPHP.php';
	// セッションスタート
	session_start();
	// エラーを表示
	ini_set("display_errors", On);
	error_reporting(E_ALL);
	// tumblrにログインしてなかったら終了
	if ( !isset( $_SESSION['logined_with_tumblr'] ) ) {
		echo "fail";
		exit;
	}

	// tumblogの名前を取得
	$blogName = filter_input( INPUT_POST, 'blogName'); 
	// ユーザー名を取得
	$userName = filter_input( INPUT_POST, 'userName'); 
	// urlを取得
	$permaLink = filter_input( INPUT_POST, 'url'); 
	// textを取得
	$text = filter_input( INPUT_POST, 'text'); 
	// tagを取得
	$tags = filter_input( INPUT_POST, 'tags'); 
	// 画像のパスを配列で取得する
	$filePath = filter_input( INPUT_POST, 'path', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	// videoを取得
	$videoPath = filter_input( INPUT_POST, 'vpath'); 
	
	if ( isset($videoPath) ) {
		// キャプションを生成
		$caption = '<p>' . $text . '</p><p>' . $userName . 'さんの動画</p><p>出展: <a href="' . $permaLink . '">Twitter</a><p>';
		$data = file_get_contents($videoPath);
		$params = array(
			'type' => 'video', // need
			'data' => $data,
			'state' => 'public',
			'caption' => $caption,
			'tags' => $tags
		);
	} else if( isset($filePath) )  {
		// キャプションを生成
		$caption = '<p>' . $text . '</p><p>' . $userName . 'さんの画像</p><p>出展: <a href="' . $permaLink . '">Twitter</a><p>';
		// photosetを生成
		$data = array();
		foreach ($filePath as $key => $path) {
			$data[] = file_get_contents($path . ':orig');
		}
		$params = array(
			'type' => 'photo', // need
			'data' => $data,
			'state' => 'public',
			'caption' => $caption,
			'tags' => $tags
		);
	} else {
		$source = '<p><a href="' . $permaLink . '">' . $userName . ' on Twitter</a></p>'; 
		$params = array(
			'type' => 'quote',
			'quote' => $text,
			'source' => $source
		);
	}
	$res = $_SESSION['tmblr']->oauth_post('/blog/' . $blogName . '.tumblr.com/post', $params);
	echo $res->meta->status;
