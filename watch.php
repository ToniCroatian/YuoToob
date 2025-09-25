<?php 
require "needed/start.php";
$_GET['v'] = substr($_GET['v'], 0, 11);
if(isset($_SERVER['HTTP_REFERER'])) {
    $vidReferer = $_SERVER['HTTP_REFERER'];
} else {
     $vidReferer = $siteurl;
}
if(strpos($_SERVER['HTTP_REFERER'], "www.".$sitedomain) !== false){
  $vidReferer = $siteurl;
}

if(strpos($_SERVER['HTTP_REFERER'], "66.33.192.247") !== false){
  $vidReferer = $siteurl;
}
$video = $conn->prepare("SELECT * FROM videos WHERE vid = ? AND converted = 1");
$video->execute([$_GET['v']]);

if($video->rowCount() == 0) {
	header("Location: index.php?unavail");
	die();
} else {
	$video = $video->fetch(PDO::FETCH_ASSOC);
}
if ($video['uid'] != $session['uid'] || $session['staff'] != 1) {
if($video['reason'] == 1) { session_error_index("This video has been removed by the user.", "error"); }
if($video['reason'] == 3) { session_error_index("This video has been removed due to copyright infringement.", "error"); }
if($video['reason'] == 2) { session_error_index("This video has been removed due to terms of use violation.", "error"); }
}
if ($video['uid'] == $session['uid'] || $session['staff'] == 1) {
if($video['reason'] == 1) { session_error_index("This video has been removed by the user.", "error"); }
if($video['reason'] == 3) { alert("Your video has been removed due to copyright infringement.", "error"); }
if($video['reason'] == 2) { alert("Your video has been removed due to terms of use violation.", "error"); }
}
$uploader = $conn->prepare("SELECT * FROM users WHERE uid = ?");
$uploader->execute([$video['uid']]);
$uploader = $uploader->fetch(PDO::FETCH_ASSOC);
    $alreadyrelated = $conn->prepare("SELECT COUNT(*) FROM relationships WHERE sender = :member_id AND respondent = :him AND accepted = 1");
    $alreadyrelated->execute([
	":member_id" => $session['uid'],
    ":him" => $uploader['uid']
    ]);

    $newrelated = $conn->prepare("SELECT COUNT(*) FROM relationships WHERE sender = :him AND respondent = :member_id AND accepted = 1");
    $newrelated->execute([
	":member_id" => $session['uid'],
    ":him" => $uploader['uid']
    ]);

    if($newrelated === 1) {
	$friendswith = 1;
    }  
    if($session['staff'] == 1) {
    $friendswith = 1;    
    }
    if ($uploader['uid'] == $session['uid']) {
    $friendswith = 1;        
    }
    if($friendswith < 1 && $video['privacy']  == 2) {
    session_error_index("This is a private video. If you have been sent this video, please make sure you accept the sender's friend request.", "error");
    }
    
if ($uploader['uid'] == NULL) {
    redirect("/index.php");
}

if ($uploader['termination'] == 1) {
    redirect("/index.php");
}

if($video['converted'] == 0) {
	header("Location: index.php");
}
$comments = $conn->prepare("SELECT * FROM comments LEFT JOIN users ON users.uid = comments.uid WHERE vidon = ? AND users.termination = 0 AND is_reply = 0 ORDER BY post_date DESC");
$comments->execute([$video['vid']]);
 
$commentc = $comments->rowCount();
$search = preg_quote($video['tags']); // Escape special characters for regular expression
$search = str_replace(" ", "|", $search);
$results = $conn->prepare("SELECT tags FROM videos LEFT JOIN users ON users.uid = videos.uid WHERE videos.tags REGEXP ? AND videos.converted = 1 And videos.privacy = 1 ORDER BY videos.uploaded DESC LIMIT 200"); // Regex!
$results->execute([$search]);
/*$views = $conn->prepare("SELECT views FROM videos WHERE vid = ?");
$views->execute([$video['vid']]);
$video['views'] = $views->fetchColumn();*/
$notOrganic = false;

// Check for organic views (better spam prevention)
$organ_views = $conn->prepare("SELECT COUNT(view_id) AS views FROM views WHERE vid = ? AND viewed > DATE_SUB(NOW(), INTERVAL 1 DAY)");
$organ_views->execute([$video['vid']]);
$organc = $organ_views->fetchColumn();

if ($organc > 300) {
    $notOrganic = true;
}

// Check for organic views (better spam prevention)
$organ_views = $conn->prepare("SELECT COUNT(view_id) AS views FROM views WHERE vid = ? AND viewed > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
$organ_views->execute([$video['vid']]);
$organc = $organ_views->fetchColumn();

if ($organc > 15) {
    $notOrganic = true;
}

if ($notOrganic = true) {
	$already_viewed = $conn->prepare("SELECT COUNT(view_id) FROM views WHERE vid = ? AND viewed > DATE_SUB(NOW(), INTERVAL 1 HOUR)");    
	$already_viewed->execute([$video['vid']]);
} else {
	$already_viewed = $conn->prepare("SELECT COUNT(view_id) FROM views WHERE vid = ? AND sid = ? AND viewed > DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
	$already_viewed->execute([$video['vid'], session_id()]);
}

if($already_viewed->fetchColumn() == 0) {
    if($_SESSION['uid'] != NULL) { 
		$add_view = $conn->prepare("INSERT INTO views (view_id, referer, vid, sid, uid) VALUES (?, ?, ?, ?, ?)");
		$add_view->execute([generateId(34), $vidReferer, $video['vid'], session_id(), $session['uid']]);
		$add_view_cnt = $conn->prepare("UPDATE videos SET views = views + 1 WHERE vid = ?");
		$add_view_cnt->execute([$video['vid']]);
		$add_view_vidswatched = $conn->prepare("UPDATE users SET vids_watched = vids_watched + 1 WHERE uid = ?");
		$add_view_vidswatched->execute([$session['uid']]);
    } else {
		$add_view = $conn->prepare("INSERT INTO views (view_id, referer, vid, sid, uid) VALUES (?, ?, ?, ?, NULL)");
		$add_view->execute([generateId(34), $vidReferer, $video['vid'], session_id()]);    
		$add_view_cnt = $conn->prepare("UPDATE videos SET views = views + 1 WHERE vid = ?");
		$add_view_cnt->execute([$video['vid']]);
    }
}
/*$maker_videos = $conn->prepare("SELECT vid FROM videos WHERE uid = ? AND converted = 1 AND privacy =1");
$maker_videos->execute([$video["uid"]]);
					
$maker_favorites = $conn->prepare("SELECT fid FROM favorites WHERE uid = ?");
$maker_favorites->execute([$video["uid"]]);

$maker_friends = $GLOBALS['conn']->prepare("SELECT relationship FROM relationships WHERE (sender = ? OR respondent = ?) AND accepted = 1");
$maker_friends->execute([$video['uid'], $video['uid']]);*/

$comments = $conn->prepare("SELECT * FROM comments LEFT JOIN users ON users.uid = comments.uid WHERE vidon = ? AND users.termination = 0 ORDER BY post_date DESC");
$comments->execute([$video['vid']]);

if($_SESSION['uid'] != NULL) { 
    // Logged in stuff
            $favorites_of_you = $conn->prepare(
	"SELECT * FROM favorites
	LEFT JOIN videos ON favorites.vid = videos.vid
	LEFT JOIN users ON users.uid = videos.uid
	WHERE favorites.uid = ? AND videos.converted = 1 AND videos.privacy = 1
	ORDER BY favorites.fid DESC"
);
$favorites_of_you->execute([$session['uid']]);

$videos_of_you = $conn->prepare(
	"SELECT * FROM videos
	LEFT JOIN users ON users.uid = videos.uid
	WHERE videos.uid = ? AND videos.converted = 1 AND videos.privacy = 1
	ORDER BY videos.uploaded DESC"
);
$videos_of_you->execute([$session['uid']]);
// End logged in stuff
}

if ($session['uid'] === null) {
    $par_link = 'signup.php';
} else {
    $par_link = 'my_videos_upload.php';
}

$related_vid_count = $conn->prepare("
    SELECT COUNT(*) 
    FROM videos 
    LEFT JOIN users ON users.uid = videos.uid 
    WHERE (videos.tags REGEXP ? OR videos.description REGEXP ? OR videos.title REGEXP ? OR users.username REGEXP ?) 
    AND videos.privacy = 1 
    AND videos.converted = 1 
    AND users.termination = 0
");

$related_vid_count->execute([$search, $search, $search, $search]);

$related_vid_count = $related_vid_count->fetchColumn();

$profile['videos'] = $conn->prepare("SELECT vid FROM videos WHERE uid = ? AND converted = 1");
$profile['videos']->execute([$video["uid"]]);
$videos = $profile['videos']->rowCount();
	
$profile['favorites'] = $conn->prepare("SELECT fid FROM favorites WHERE uid = ?");
$profile['favorites']->execute([$video["uid"]]);
$favorites = $profile['favorites']->rowCount();
?>

<div style="padding: 0px 5px 0px 5px;">
		
<iframe id="invisible" name="invisible" src="watch.php?v=<?php echo htmlspecialchars($video['vid']); ?>" scrolling="yes" width="0" height="0" frameborder="0" marginheight="0" marginwidth="0"></iframe>   

<script>

function CheckLogin()
{
		<?php if($_SESSION['uid'] == NULL) { ?>
		alert("You must be logged in to to perform this action!");
		return false;
	<?php } ?>
		
	return true;
}

function FavoritesHandler()
{
	if (CheckLogin() == false)
		return false;

	alert("Video has been added to Favorites!");
	return true;
}

function CommentHandler()
{
	if (CheckLogin() == false)
		return false;

	var comment = document.comment_form.comment;
	var comment_button = document.comment_form.comment_button;

	if (comment.value.length == 0 || comment.value == null)
	{
		alert("You must enter a comment!");
		comment.focus();
		return false;
	}

	if (comment.value.length > 500)
	{
		alert("Your comment must be shorter than 500 characters!");
		comment.focus();
		return false;
	}

	comment_button.disabled='true';
	comment_button.value='Thanks for your comment!';

	return true;
}
</script>

<div class="pageTitle"><?php echo htmlspecialchars($video['title']); ?></div>

<table width="795" align="center" cellpadding="0" cellspacing="0" border="0">
	<tbody><tr valign="top">
		<td width="515" style="padding-right: 15px;">
		
		<div style="font-size: 13px; font-weight: bold; text-align:center;">
		<a href="mailto:/?subject=<?php echo htmlspecialchars($video['title']); ?>&amp;body=http://www.<?php echo $sitedomain; ?>/?v=<?php echo htmlspecialchars($video['vid']); ?>">Share</a>
		// <a href="#comment">Comment</a>
		// <a href="add_favorites.php?video_id=<?php echo htmlspecialchars($video['vid']); ?>" target="invisible" onclick="return FavoritesHandler();">Add to Favorites</a>
		// <a href="outbox.php?user=<?php echo htmlspecialchars($uploader['username']); ?>&amp;subject=Re: <?php echo htmlspecialchars($video['title']); ?>">Contact Me</a>
		</div>
		
		<?php if(isset($_COOKIE["flash"])) { ?>
		<div style="text-align: center; padding-bottom: 10px;">
		<div id="flashcontent">
		<div style="padding: 20px; font-size:14px; font-weight: bold;">
			<embed src="player.swf?video_id=<?php echo htmlspecialchars($video['vid']); ?>&l=<?php echo ceil($video['time']); ?>&c=<?= $video['cdn'] ?><?php if($_SESSION['uid'] != NULL) { echo "&s=".session_id(); }?>" width="425" height="350">
		</div>
		</div>
		</div>
		
		<!--
		<div style="text-align: center; padding-bottom: 10px;">
		<div id="flashcontent">
		<div style="padding: 20px; font-size:14px; font-weight: bold;">
			Hello, you either have JavaScript turned off or an old version of Macromedia's Flash Player, <a href="http://www.macromedia.com/go/getflashplayer/">click here</a> to get the latest flash player.
		</div>
		</div>
		</div>
		-->
		
		<script type="text/javascript">
			// <![CDATA[
			
			var fo = new FlashObject("player.swf?video_id=<?php echo htmlspecialchars($video['vid']); ?>&l=<?php echo ceil($video['time']); ?>&c=<?= $video['cdn'] ?><?php if($_SESSION['uid'] != NULL) { echo "&s=".session_id(); }?>", "player", "425", "350", 7, "#FFFFFF");
			fo.write("flashcontent");
			
			// ]]>
		</script>
		<?php } else { ?>
		<div style="padding: 10px; margin-left: 20px;">
		<link rel="stylesheet" href="viewfinder/player.css">
		<!-- player HTML begins here -->
        <div class="player" id="playerBox">
            <div class="mainContainer">
                <div class="playerScreen">
                    <div class="playbackArea">
                        <div class="videoContainer">
                            <video class="videoObject" id="video">
                                <source src="get_video.php?video_id=<?php echo htmlspecialchars($video['vid']); ?>&format=webm"> 
                             </video>
                        </div>
                    </div>
                  <div class="watermark">
                        <img src="viewfinder/resource/watermark.png" height="35px">
                    </div>
                </div>
                <div class="controlBackground">
                    <div class="controlContainer">
                        <div class="lBtnContainer">
                            <div class="button" id="playButton">
                                <img src="viewfinder/resource/play.png" id="playIcon">
                                <img src="viewfinder/resource/pause.png" class="hidden" id="pauseIcon">
                            </div>
                        </div>
                        <div class="centerContainer">
                            <div class="seekbarElementContainer">
                                <progress class="seekProgress" id="seekProgress" value="0" min="0"></progress>
                            </div>
                            <div class="seekbarElementContainer">
                                <input class="seekHandle" id="seekHandle" value="0" min="0" step="1" type="range">
                            </div>
                        </div>
                        <div class="rBtnContainer">
                            <div class="button" id="muteButton">
                                <img src="viewfinder/resource/mute.png" id="muteIcon">
                                <img src="viewfinder/resource/unmute.png" class="hidden" id="unmuteIcon">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="aboutBox hidden" id="aboutBox">
                <div class="aboutBoxContent">
                <div class="aboutHeader">Viewfinder</div>
                <div class="aboutBody">
                    <div>Version 1.0<br>
                    <br>
                    2005-Style HTML5 player<br>
                    <br>
                    Created by Purpleblaze
                </div>
                </div>
                <button id="aboutCloseBtn">Close</button>
                </div>
            </div>
            <div class="contextMenu hidden" id="playerContextMenu">
                <div class="contextItem" id="contextMute">
                    <span>Mute</span>
                    <div id="muteTick" class="tick hidden">    
                    </div>
                </div>
                <div class="contextItem" id="contextLoop">
                    <span>Loop</span>
                    <div id="loopTick" class="tick hidden">
                    </div>
                </div>
                <div class="contextSeparator"></div>
                <div class="contextItem" id="contextAbout">About</div>
            </div>
        </div>
        <script src="viewfinder/player.js"></script>
		</div>
		<?php } if ($session['staff'] == 1 && $session['uid'] != $video['uid']) { ?>
		<?php
	    	$featured_video_exists = $conn->prepare("SELECT video FROM picks WHERE video = :video_id");
	    	$featured_video_exists->execute([
	    		":video_id" => htmlspecialchars($video['vid'])
	    	]);
        ?>
		<div style="font-size: 13px; font-weight: bold; text-align:center;">
			<a href="admin/<?php if($featured_video_exists->rowCount() == 1) { ?>un<? } ?>feature_video.php?video_id=<?php echo htmlspecialchars($video['vid']); ?>"><?php if($featured_video_exists->rowCount() == 1) { ?>Unf<? } else { ?>F<? } ?>eature This Video</a>&nbsp;&nbsp;//&nbsp;&nbsp;<a href="admin/mod_video.php?video_id=<?php echo htmlspecialchars($video['vid']); ?>">Moderate This Video</a>&nbsp;&nbsp;//&nbsp;&nbsp;<a href="admin/user_terminate.php?user_id=<?php echo htmlspecialchars($uploader['uid']); ?>">Terminate Uploader</a>
		</div>
		
		<br>
		<? } ?>
		
		<table width="425" cellpadding="0" cellspacing="0" border="0" align="center">
			<tbody><tr>
				<td>
					<div class="watchDescription"><?php
						$real_desc = nl2br(htmlspecialchars($video['description']));
						$real_desc = AutoLinkUrls($real_desc);
						echo $real_desc; ?>
					</div>
					
					<div class="watchTags">Tags // <?php $tags = explode(" ", $video['tags']); $tagCount = count($tags); foreach ($tags as $index => $tag) { ?><a href="results.php?search=<? echo htmlspecialchars($tag); ?>"><? echo htmlspecialchars($tag); ?></a> : <? } ?>					</div>
								
					<div class="watchAdded">
					Added: <?php echo retroDate($video['uploaded'], "F j, Y"); ?> by <a href="profile.php?user=<?php echo htmlspecialchars($uploader['username']); ?>"><?php echo htmlspecialchars($uploader['username']); ?></a> //
					<a href="profile_videos.php?user=<?php echo htmlspecialchars($uploader['username']); ?>">Videos</a> (<?php echo $videos; ?>) | <a href="profile_favorites.php?user=<?php echo htmlspecialchars($uploader['username']); ?>">Favorites</a> (<?php echo $favorites; ?>)
					</div>
			
					<div class="watchDetails">
					Views: <?php echo htmlspecialchars($video['views']); ?> | <a href="#comment">Comments</a>: <?php echo number_format($commentc); ?>					</div>
					
					<?php if (!empty($video['recorddate']) || !empty($video['address']) || !empty($video['addrcountry'])) { ?>
					<br>
					
					<div class="watchDetails">
					<?php if (!empty($video['recorddate'])) { ?>Recorded: <?php echo retroDate($video['recorddate'], "Y-m-d"); ?> | <? } if (!empty($video['address'])) { ?>Location: <a href="http://maps.google.com/maps?t=h&q=<?php echo htmlspecialchars($video['address']); ?>"><?php echo htmlspecialchars($video['address']); ?></a> | <? } if (!empty($video['addrcountry'])) { ?>Country: <?php echo htmlspecialchars($video['addrcountry']); } ?>					</div>
					<? } ?>
				</td>
			</tr>
		</tbody></table>
		
		<!-- watchTable -->
		
		<div style="padding: 15px 0px 10px 0px;">
		<table width="100%" align="center" cellpadding="0" cellspacing="0" border="0" bgcolor="#E5ECF9">
			<tbody><tr>
				<td><img src="img/box_login_tl.gif" width="5" height="5"></td>
				<td width="100%"><img src="img/pixel.gif" width="1" height="5"></td>
				<td><img src="img/box_login_tr.gif" width="5" height="5"></td>
			</tr>
			<tr>
				<form name="linkForm" id="linkForm"></form>
				<td><img src="img/pixel.gif" width="5" height="1"></td>
				<td align="center">
		
				<div style="font-size: 11px; font-weight: bold; color: #CC6600; padding: 5px 0px 5px 0px;">Share this video! Copy and paste this link:</div>
				<div style="font-size: 11px; padding-bottom: 15px;">
				<input name="video_link" type="text" onclick="javascript:document.linkForm.video_link.focus();document.linkForm.video_link.select();" value="http://www.<?php echo $sitedomain; ?>/?v=<?php echo htmlspecialchars($video['vid']); ?>" size="50" readonly="true" style="font-size: 10px; text-align: center;">
				</div>
				
				</td>
				<td><img src="img/pixel.gif" width="5" height="1"></td>
				
			</tr>
			<tr>
				<td><img src="img/box_login_bl.gif" width="5" height="5"></td>
				<td><img src="img/pixel.gif" width="1" height="5"></td>
				<td><img src="img/box_login_br.gif" width="5" height="5"></td>
			</tr>
		</tbody></table>
		</div>
		
		<a name="comment"></a>
		
	<? if($video['comms_allow'] < 1) { ?>
		<div style="padding-bottom: 5px; font-weight: bold; color: #444;">Comments have been disabled for this video.</div>
	<? } else { ?>
		<div style="padding-bottom: 5px; font-weight: bold; color: #444;">Comment on this video:</div>

		<form name="comment_form" id="comment_form" method="post" action="add_comment.php" target="invisible" onsubmit="return CommentHandler();">
		
		<input type="hidden" name="video_id" value="<?php echo htmlspecialchars($video['vid']); ?>">
		
		<textarea name="comment" cols="55" rows="3"></textarea>
		
		<br>
		
		<input type="submit" name="comment_button" value="Add Comment">
		
		</form>
		
		<br>
	<? } ?>
		<div class="commentsTitle">Comments (<?php echo number_format($commentc); ?>):</div>
		<?php if($comments !== false) {
				foreach($comments as $comment) { ?>
		<div class="commentsEntry"><? if ($comment['removed'] == 1) { echo '----- Comment deleted by user -----'; } else { ?>
		"<?= nl2br(htmlspecialsomechars($comment['body'], ['b', 'i', 'big'])) ?>"<? } ?><br>
<? if($comment['termination'] != 1) {
$profile['videos'] = $conn->prepare("SELECT vid FROM videos WHERE uid = ? AND converted = 1");
$profile['videos']->execute([$comment["uid"]]);
$comment_vids = $profile['videos']->rowCount();
	
$profile['favorites'] = $conn->prepare("SELECT fid FROM favorites WHERE uid = ?");
$profile['favorites']->execute([$comment["uid"]]);
$comment_favs = $profile['favorites']->rowCount();
?>
 - <a href="profile.php?user=<?php echo htmlspecialchars($comment['username']); ?>"><?php echo htmlspecialchars($comment['username']); ?></a> // <a href="profile_videos.php?user=<?php echo htmlspecialchars($comment['username']); ?>">Videos</a> (<?php echo $comment_vids; ?>) | <a href="profile_favorites.php?user=<?php echo htmlspecialchars($comment['username']); ?>">Favorites</a> (<?php echo $comment_favs; ?>)<? } ?> - (<?= timeAgo($comment['post_date']); ?>)
<? if ($comment['removed'] == 0) { ?>
<?php if ($comment['uid'] == $session['uid'] || $session['staff'] == 1 && $comment['uid'] != NULL) { ?>
&nbsp;<input type="submit" form="remove_comment" id="<?php echo htmlspecialchars($comment['cid']); ?>" value="Remove Comment">
	<form method="post" action="remove_comment.php" id="remove_comment">
		<input type="hidden" name="video_id" value="<?php echo htmlspecialchars($video['vid']); ?>">
		<input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['cid']); ?>">
	</form>
<? } } ?></div>
<? } } ?>		
		
		</td>
		
		<td width="300">
		
		<table width="300" align="center" cellpadding="0" cellspacing="0" border="0" bgcolor="#CCCCCC">
			<tbody><tr>
				<td><img src="img/box_login_tl.gif" width="5" height="5"></td>
				<td><img src="img/pixel.gif" width="1" height="5"></td>
				<td><img src="img/box_login_tr.gif" width="5" height="5"></td>
			</tr>
			<tr>
				<td><img src="img/pixel.gif" width="5" height="1"></td>
				<td width="295">
				<div class="moduleTitleBar">
				<table width="290" cellpadding="0" cellspacing="0" border="0">
					<tbody><tr valign="top">
						<td><div class="moduleFrameBarTitle">Tag // <?php $tags = explode(" ", $video['tags']); $tagCount = count($tags); foreach ($tags as $index => $tag) { echo htmlspecialchars($tag)." "; } ?>(<? if ($related_vid_count > 10) { echo "10 of "; } else { echo htmlspecialchars($related_vid_count)." of "; } echo htmlspecialchars($related_vid_count); ?>)</div></td>
						<td align="right"><div style="font-size: 11px; margin-right: 5px;"><a href="results.php?&<?php echo urlencode(htmlspecialchars($video['tags'])); ?>" target="_parent">See more Results</a></div></td>
					</tr>
				</tbody></table>
				</div>

				<iframe id="side_results" name="side_results" src="include_results.php?v=<?php echo htmlspecialchars($video['vid']); ?>&search=<?php echo urlencode(htmlspecialchars($video['tags'])); ?>#selected" scrolling="auto" width="290" height="400" frameborder="0" marginheight="0" marginwidth="0">
				 [Content for browsers that don't support iframes goes here]
				</iframe>
				</td>
				<td><img src="img/pixel.gif" width="5" height="1"></td>
			</tr>
			<tr>
				<td><img src="img/box_login_bl.gif" width="5" height="5"></td>
				<td><img src="img/pixel.gif" width="1" height="5"></td>
				<td><img src="img/box_login_br.gif" width="5" height="5"></td>
			</tr>
		</tbody></table>
		
		<? if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') { ?>
		<!--
		<br>
		
		<table width="300" align="center" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFFFCC">
			<tbody><tr>
				<td><img src="img/box_login_tl.gif" width="5" height="5"></td>
				<td><img src="img/pixel.gif" width="1" height="5"></td>
				<td><img src="img/box_login_tr.gif" width="5" height="5"></td>
			</tr>
			<tr>
				<td><img src="img/pixel.gif" width="5" height="1"></td>
				<td width="290">
				<div style="padding: 5px;">
				<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2537513323123758" crossorigin="anonymous"></script>
				<ins class="adsbygoogle"
				style="display:inline-block;width:728px;height:90px"
				data-ad-client="ca-pub-2537513323123758"
				data-ad-slot="3705019363"></ins>
				<script>
					(adsbygoogle = window.adsbygoogle || []).push({});
				</script>
				</div>
				</td>
				<td><img src="img/pixel.gif" width="5" height="1"></td>
			</tr>
			<tr>
				<td><img src="img/box_login_bl.gif" width="5" height="5"></td>
				<td><img src="img/pixel.gif" width="1" height="5"></td>
				<td><img src="img/box_login_br.gif" width="5" height="5"></td>
			</tr>
		</tbody></table>
		-->
		<? } ?>
		
		<div style="font-weight: bold; color: #333; margin: 10px 0px 5px 0px;">Related tags:</div>
		<?php
			$related_tags = [];
			foreach($results as $result) $related_tags = array_merge($related_tags, explode(" ", $result['tags']));
			$related_tags = array_unique($related_tags);
		?>
		<?php foreach($related_tags as $tag) { ?>
			<div style="padding: 0px 0px 5px 0px; color: #999;">&#187; <a href="results.php?search=<?php echo htmlspecialchars($tag); ?>"><?php echo htmlspecialchars($tag); ?></a></div>
		<?php } ?>
		</td></tr>

		
	
</tbody></table>

</div>

<br>

<?php require "needed/end.php"; ?>