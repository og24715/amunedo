<?php
	require_once dirname(__FILE__) . '/library/TwistOAuth.phar';
	require_once dirname(__FILE__) . '/Tumblr/lib/tumblrPHP.php';
	require_once dirname(__FILE__) . '/get-my-id.php';
	
	@session_start();
	if (isset($_SESSION['logined'])) {
		$_SESSION['max_id'] = $max_id = null;
	}

	$code = 200;
	header('Content-Type: text/html; charsetj=utf-8', true, $code);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Amunedo -fav-</title>
	<!-- jQuery  -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0-alpha1/jquery.min.js"></script>
	<!-- Bootstrap -->
	<link  href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha/js/bootstrap.min.js"></script>
	<!-- Masonry -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/masonry/3.3.2/masonry.pkgd.min.js"></script>
	<!-- imagesloadedded -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.0.0/imagesloaded.min.js"></script>
	<!-- lity -->
	<script src="library/lity/lity.min.js"></script>
	<link rel="stylesheet" type="text/css" href="library/lity/lity.min.css">
	<!-- Bootstrap toggle -->
	<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.0/css/bootstrap-toggle.min.css" rel="stylesheet">
	<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.0/js/bootstrap-toggle.min.js"></script>
	<!-- color box -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.6.3/jquery.colorbox.js"></script>
	<!-- google fonts -->
	<link href='https://fonts.googleapis.com/css?family=Pacifico' rel='stylesheet' type='text/css'>

	<script type="text/javascript">
		$(function(){

			// grid の初期化
			var $grid = $('.grid').imagesLoaded( function()
			{
				$grid.masonry({
					// options...
					itemSelector: '.grid-item',
				});
			});
			// 最下部に到達した時の処理
			$(window).on("scroll", function () 
			{
				var scrollHeight = $(document).height();
				var scrollPosition = $(window).height() + $(window).scrollTop();
				if ((scrollHeight - scrollPosition) / scrollHeight === 0) {
					/*
					console.log('start');
					var ld_html ='<div class="text-center grid-item"><img src="img/loading.gif"></div>';
					$grid.one( 'click', )
					//$grid.after( ld_html );
					*/

					$.ajax({
						url: 'output.php', // 実行するPHP
						type: 'POST',
						async: false,
						// PHPを実行した後の処理
					}).done( function( res ){
						$res = $( res ); // jQueryオブジェクトに変換
						$res.css( 'opacity', '0' ); // masonry によるレイアウトが完了するまで非表示（被ってしまうので）
						$grid.append( $res ); // HTMLに挿入する
						// .grid 内の画像が全て読み込まれた後の処理
						$grid.imagesLoaded( function(){
							$res.css( 'opacity', '1' );
							$grid.masonry( 'appended', $res );										
						});
					}).always( function(){
						console.log('end');
					});// ajax
				}
			});
			// .unfav をクリックした時の処理
			$grid.on( 'click', '.unfav', function() {
				// 非表示
				var $itm_box = $(this).parents('.grid-item');
				$grid.masonry( 'remove', $itm_box ).masonry('layout');
				// あんふぁぼする
				id = $itm_box.find('.tweet').attr('id');
				$.ajax({
					type: 'POST',
					url: 'unfav.php',
					data: { 'id': id },
					timeout: 5000
				});
			});

			// .save をクリックした時の処理
			$grid.on( 'click', '.save', function(){
				alert('debug: clicked save');
				
				$.ajax({
					url: 'download.php',
					type: 'POST',
					data: { name: 'yaju.jpg', path: 'http://i.imgur.com/pjjxzPL.jpg' }
				}).done( function( res ){

				});
				
			});
			// .post をクリックした時の処理
			$grid.on( 'click', '.post', function(){
				if ( "<?=$_SESSION['logined_with_tumblr']?>" != "1" ) {
					alert('tumblrにログインしていません');
				} else {
					var $itm_box = $(this).parents('.grid-item');
					
					// 選択した tumblog を取得
					var tumblogName = $(this).attr('title');				
					// user名を取得
					var userName = $itm_box
						.find('.user-name')
						.text();
					// user idを取得
					var userID = $itm_box
						.find('.user-id')
						.text();
						//console.log(userID);
					// 固定リンクを取得
					var permaLink = $itm_box
						.find('.tweet-link')
						.attr('href');
					// tweetテキストを取得
					var tweetText = $itm_box
						.find('.tweet-text')
						.html();
					// 画像を取得
					var imgPath = new Array();;
					$.each( $itm_box.find('.tweet-img'), function(i, elm) {
						path = $(elm).attr('src');
						imgPath.push( path );
					});
					// 
					var videoPath;
					$.each( $itm_box.find('.tweet-video'), function(i, elm) {
						videoPath = $(elm).attr('href');
					});

					// 非表示
					var $itm_box = $(this).parents('.grid-item');
					$grid.masonry( 'remove', $itm_box ).masonry('layout');
					
					var id = $itm_box.find('.tweet').attr('id');
					$.ajax({
						url: 'Tumblr/post.php',
						type: 'POST',
						data: { 
							'path[]': imgPath,
							'vpath' : videoPath,
							'blogName': tumblogName,
							'url': permaLink,
							'userName': userName,
							'text': tweetText,
							'tags' : userID
						},
						'dataType': 'text',
						timeout: 15000
					}).done( function( res ) {
						if ( Number(res) !== 201 ){
							alert('Tumblrの投稿上限に達したっぽいです。0時に回復します。');
						}
						$.ajax({
							type: 'POST',
							url: 'unfav.php',
							data: { 'id': id },
							timeout: 10000
						});
					}).fail( function( res ){
						alert('tumblrに投稿を失敗しました')
					}).always( function( res ){
						//console.log( res );
					});
					
				}
			}); // post
			
			// ちょいスクロールした時の処理
			var $top_btn = $('#jump-top');
			$top_btn.hide();
			$(window).scroll( function (){
				if( $(this).scrollTop() > 100 ){
					$top_btn.fadeIn();
				} else {
					$top_btn.fadeOut();
				}
			});
			// #jump-top をクリックした時の処理
			$top_btn.on( 'click', function() {
				$('body,html').animate({
					scrollTop: 0
				}, 1000);
				return false;
			});
			// ログインボタン表示したり、コンフィグメニューだす処理
			// この書き方、外部JSにした時動かないのでヒジョーにマズイです
			var $login_btn = $('#login-btn');
			var $config_area = $('#config-area');
			if ( "<?=$_SESSION['logined']?>" == '1' ) {
				$login_btn.hide();
				$config_area.show();
			} else {
				$config_area.hide();
				$login_btn.show();	
			}
			var $login_tumblr = $('#login-tumblr');
			var $logout_tumblr = $('#logout-tumblr');
			if ( "<?=$_SESSION['logined_with_tumblr']?>" == '1' ) {
				$login_tumblr.hide();
				$logout_tumblr.show();
			} else {
				$logout_tumblr.hide();
				$login_tumblr.show();	
			}

		});
	</script>

	<link rel="stylesheet" type="text/css" href="style.css">
	<style type="text/css">
		
		
	</style>

</head>
<body>
	
	<?php if ( isset($_SESSION['logined']) ){ ?>
		<div class="navbar navbar-default navbar-fixed-top" role="navigation">
			<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">ナビゲーションの切替</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="index.php">ふぁぼを表示する奴。</a> <!-- ブランド -->
			</div><!-- /.navbar-header -->
			<div class="navbar-collapse collapse">
				
				<ul class="nav navbar-nav navbar-right">
					<li id="config-area" class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<span class="glyphicon glyphicon-user"> <?=$_SESSION['myID']?><span class="caret"></span></span>
						</a>
						<ul class="dropdown-menu">
							<li class="dropdown-header">Tumblr</li>
							<li class="disabled"><a href="#"><span class="glyphicon glyphicon-cog"> 設定</span></a></li>
							<li id="login-tumblr"><a href="Tumblr/login.php"><span class="glyphicon glyphicon-ok-circle"> 連携をする</span></a></li>
							<li id="logout-tumblr"><a href="Tumblr/logout.php"><span class="glyphicon glyphicon-remove-circle"> 連携を解除する</span></a></li>
							<li class="divider"></li>
							<li><a class="bg-danger" href="logout.php"><span class="glyphicon glyphicon-log-out"> ログアウト</span></a></li>
						</ul>
					</li>
					<li id="login-btn"><button type="button" class="btn btn-primary navbar-btn" onclick="location.href='login.php'"><span class="glyphicon glyphicon-log-in"> ログイン</span></button></li>
				</ul>
			</div><!-- /.navbar-collapse -->
			</div><!-- /.container -->
		</div><!-- /.navbar -->

		<div id="wrapper">
			<div class="container" style="min-height: 100vh; padding-top: 70px">
				<div class="row grid tweets">
					<?php require('output.php'); ?>	
				</div>
			</div><!-- /.container -->
		</div>
	<?php } else { ?>
		<div class="top">
			<div class="middle-box" style="width: 100%; height: 200px;">
				<h1 class="text-center top-logo">Amunedo</h1>
				<h2 class="text-center top-sub">List to display, share to tumblr</h2>
				<p class="text-center">
					<a type="button" class="btn btn-default btn-lg" href="login.php">try it now!</a>
				</p>
			</div>
		</div>
	<?php } ?>

	<div style="position: fixed; bottom: 20px; right: 20px;">
		<span id="jump-top" class="glyphicon glyphicon-menu-up" style="font-size: 4em; cursor: pointer"></span>
	</div>

</body>
</html>
