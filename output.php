<?php
	//TwistOauthの読み込み
	require_once dirname(__FILE__) . '/library/TwistOAuth.phar';
	//twitter-textの読み込み
	require_once dirname(__FILE__) . '/library/twitter-text/Autolink.php';
	// tumblrのライブラリ読み込み
	require_once dirname(__FILE__) . '/Tumblr/lib/tumblrPHP.php';
	//セッション開始
	@session_start();
	//エラーの詳細を表示する
	ini_set("display_errors", On);
	error_reporting(E_ALL);
	//ヘッダー情報
	//header('Content-type: text/html; charset=utf-8');
	
	/**
	 *	Tumblogを取得し、配列で返す
	 *
	 *  @return array | false
	 *
	 */
	function Get_Tumblog () {
		$blogs = null;
		if ( isset( $_SESSION['logined_with_tumblr']) ) {
			if ( isset( $_SESSION['tmblr'] ) ) {
				// ユーザーの情報を取得
				$userInfo = $_SESSION['tmblr']->oauth_get('/user/info');
				$blogs = $userInfo->response->user->blogs;
			}
		}
		return $blogs;
	}

	/**
	 *	テキストにリンクを付ける
	 *
	 *  @param string $text
	 *  @return string $text
	 */
	function AddLink( $text ) {
		/*
		//ハッシュタグリンク成形
		$pattern = "/(?<![0-9a-zA-Z'\"#@=:;])#(\w*[a-zA-Z_])/u";
		$replacements = "<a href=\"http://search.twitter.com/search?q=\\1\">#\\1</a>";
		$text = preg_replace($pattern, $replacements, $text);
		//アカウントリンク成形
		$pattern = "/(?<![0-9a-zA-Z'\"#@=:;])@([0-9a-zA-Z_]{1,15})/u";
		$replacements = "<a href=\"http://twitter.com/\\1\">@\\1</a>";
		$text = preg_replace($pattern, $replacements, $text);
		//URLリンク成形
		$pattern = "/(https?:\/\/t.co)([-_.!~*'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/";
		$replacements = "<a href=\"\\1\\2\">\\1\\2</a>";
		$text = preg_replace($pattern, $replacements, $text);
		*/
		//tweet-textライブラリ使っても成形できるよ
		$text = Twitter_Autolink::create($text)
			->setNoFollow(false)
			->setTarget(false)
			->addLinks();
		return $text;
	}
	
	// 画像整形
	function AddContent( $fav ){
		if ( isset( $fav->extended_entities ) ) {
			$html = '';
			foreach ($fav->extended_entities->media as $media) {
				switch ( $media->type ) {
					case 'photo':
						$html .= '
							<figure class="tweet-media-box">
								<a href="'. $media->media_url .'" data-lity><img class="tweet-img" src="'. $media->media_url .'" class="img-responsive"></a>
							</figure>
						'; 
						break;
					case 'animated_gif':
						$html = 
							'<figure class="tweet-media-box">
								<a class="tweet-video" href="'. $media->video_info->variants[0]->url .'" data-lity>
									<div class="video-thumb">
										<img class="tweet-img" src="'. $media->media_url .'">
									</div>
								</a>
							</figure>';
						break;
					case 'video':
						foreach ($media->video_info->variants as $k => $variant) {
							if ( $variant->content_type === 'video/mp4' ) {
								if ( empty($max) || $max->bitrate < $variant->bitrate ) {
									$max = $variant;
								}
							}
						}
						$html .= 
							'<figure class="tweet-media-box">
								<a class="tweet-video" href="'. $max->url .'" data-lity>
									<div class="video-thumb">
										<img class="tweet-img" src="'. $media->media_url .'">
									</div>
								</a>
							</figure>';
						unset($max);
						break;
					default:
						$html .= "<p>$media->type</p>";
						break;
				}
			}
			return $html;
		}
	}

	function AddTumblog () {
		$html = "";
		if ( 1 < count( $_SESSION['tumblogs'] ) ) {
			$html .= '
				<div class="dropup btn-group" style="display: inline-block">
					<button class=" btn btn-default dropdown-toggle" data-toggle="dropdown">
						<span class="glyphicon glyphicon-share"><span class="caret"></span></span>
					</button>
					<ul class="dropdown-menu" style="left: -150%;">
			';					
			foreach ($_SESSION['tumblogs'] as $key => $blog) {
				$html .= '
					<li class="post" title="' . $blog->name . '">
						<a href="javascript:void(0);">
							<img src="http://api.tumblr.com/v2/blog/'. $blog->name .'.tumblr.com/avatar/30" style="margin: 0 10 0 0; display: inline-block; class="img-rounded" />
							' . $blog->name . '
						</a>
					</li>
				';
			}
			$html .= '
					</ul>
				</div>
			';
		} else if ( 1 === count( $_SESSION['tumblogs'] ) ) {
			$html .= '<a class="post btn" title="' . $blog->name . '"><span class="glyphicon glyphicon-share"></span></a>';
		} else if ( 0 === count( $_SESSION['tumblogs'] ) ) {
			$html .= '<a class="post btn"><span class="glyphicon glyphicon-share"></span></a>';				
		}
		return $html;
	}

	try {
		
		$html = '';
		if ( !isset($_SESSION['tumblogs']) ) {
			$_SESSION['tumblogs'] = Get_Tumblog();
		}

		if ( isset($_SESSION['max_id']) ) {
			$max_id = $_SESSION['max_id'];			
		} else {
			$max_id = null;
		}
		
		// お気に入り一覧を取得
		$params = array(
			// screan_name はデフォルトで自分のID。だとおもう。
			'count' => 100,
			'max_id' => $max_id,
			'include_entities' => true, 
		);
		$favs = $_SESSION['to']->get('/favorites/list', $params);
		// お気に入りを出力
		foreach ($favs as $key => $fav) {
			$user_name = $fav->user->name;
			$user_id = $fav->user->screen_name;
			$user_link = 'http://twitter.com/' . $fav->user->screen_name;
			$tweet_link = 'http://twitter.com/' . $fav->user->screen_name . '/status/' . $fav->id;
			$reaction = intval( $fav->favorite_count ) + intval( $fav->retweet_count );

			$html .= '<div class="col-xs-12 col-sm-6 col-md-4 grid-item" style="padding: 0 7px;">';
			$html .= '
				<div id="' . $fav->id . '" class="tweet thumbnail">
			';		
			$html .= AddContent( $fav );
			$html .= '
					<div style="padding: 20px">
						<p class="user-name" style="display: none"><a href="'. $user_link .':orig">'. $user_name .'<span class="user-id">@'. $user_id .'</span></a></p>
						<p class="tweet-text">'. AddLink($fav->text) .'</p>
						<div class="tweet-status clearfix">
							<div class="pull-left"><a href="' . $tweet_link . '" class="btn tweet-link" target="_blank">'. $reaction .'リアクション</a></div>
							<div class="pull-right">
								'. AddTumblog () .'
								<a class="btn unfav"><span class="glyphicon glyphicon-remove"></span></a>
							</div>
						</div>
					</div>
			';
			$html .= '
				</div>
			</div>';
		}
		$_SESSION['max_id'] = $fav->id - 1;
		echo $html;

	} catch (Exception $e) {
		echo $e->getMessage();
	}
?>