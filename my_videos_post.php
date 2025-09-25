<?php 
require "needed/scripts.php";

$_CDNS = [
'14'
];
$video_CDN = array_rand($_CDNS);
$video_CDN = $_CDNS[$video_CDN];

use SoftCreatR\MimeDetector\MimeDetector;
use SoftCreatR\MimeDetector\MimeDetectorException;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['fileToUpload'])) {
    $maxFileSize = 100 * 1024 * 1024;
    $fileSize = $_FILES['fileToUpload']['size'];
    
    if ($fileSize > $maxFileSize) {
         header("Location: my_videos_upload.php");
         exit;
    }
// create an instance of the MimeDetector
$mimeDetector = new MimeDetector();

// set our file to read
try {
    $mimeDetector->setFile($_FILES['fileToUpload']['tmp_name']);
} catch (MimeDetectorException $e) {
    header("Location: my_videos_upload.php");
         exit;
}

// try to determine its mime type and the correct file extension
$type = $mimeDetector->getFileType();

$mime = strtolower($type['mime']);
$isVideo = 0;

// Check if the MIME type matches the most popular video formats
if (strpos($mime, 'video/') === 0) {
    $ok = 1;
    if ($ok == 1) {
        $cooldown = $conn->prepare(
			"SELECT * FROM videos
			WHERE uid = ? AND videos.uploaded > DATE_SUB(NOW(), INTERVAL 1 DAY)
			ORDER BY uploaded DESC"
		);
        $cooldown->execute([$session['uid']]);
        // blazing
        // 1 = Weak
        // 2 = Moderate
        // 3 = Strict
        // >4 = stop spamming up the site dumbass

		if ($session['blazing'] > 0) {
        if ($session['blazing'] == 1) { $coolit = 8; }
        elseif ($session['blazing'] == 2) { $coolit = 5; }
        elseif ($session['blazing'] == 3) { $coolit = 3; }
        elseif ($session['blazing'] > 4) { $coolit = 1; }
        } else { $coolit = 50; }
        // I redesigned this in a way that makes it seem infinite to the average user
		if($cooldown->rowCount() > $coolit || $cooldown->rowCount() == $coolit) {
			session_error_index("You have uploaded one too many videos today! Check back tomorrow.", "error");
		}
        $my_videos = $conn->prepare(
			"SELECT * FROM videos
			WHERE uid = ?
			ORDER BY uploaded DESC"
		);
        $my_videos->execute([$session['uid']]);
// the user's first video's id *wasn't* their user id
		//if($my_videos->rowCount() > 0) {
			$video_id = generateId();
		//} else {
            //$video_id = $session['uid'];
        //}
        $field_upload_tags = trim($_POST['field_upload_tags']);
        $field_upload_tags = str_replace(',', '', $field_upload_tags); // Remove commas
        $field_upload_tags = str_replace('  ', ' ', $field_upload_tags); // Remove whitespaces
        $field_upload_tags = str_replace('#', '', $field_upload_tags); // Remove hashtags
        if(empty($field_upload_tags)) {
            session_error_index("Enter some tags for your video.", "error");
        }
		 //if ($_POST['addr_yr'] == '---' || $_POST['addr_month'] == '---' || $_POST['addr_day'] == '---') {
		 //	 $recorddate = null;
		 //} elseif ($_POST['addr_yr'] != '---' && $_POST['addr_month'] != '---' && $_POST['addr_day'] != '---') {
		 //	 if (!empty($_POST['addr_yr']) && !empty($_POST['addr_month']) && !empty($_POST['addr_day'])) {
		 //		$recorddate = $_POST['addr_yr']."-".$_POST['addr_month']."-".$_POST['addr_day'];
		 //	 } else {
		 //		$recorddate = null;
		 //	 }
		 //} else {
		 //	 $recorddate = null;
		 //}
		 if ($_POST['addr_date'] != null && !empty($_POST['addr_date'])) {
			 $recorddate = $_POST['addr_date'];
		 } else {
			 $recorddate = null;
		 }
         if ($_POST['field_upload_country'] == '---') {
             $_POST['field_upload_country'] = NULL;
         }
         if ($_POST['private'] == 2) {
            $remove_fav = $conn->prepare("UPDATE users SET priv_vids = priv_vids + 1 WHERE uid = ?");
            $remove_fav->execute([$session['uid']]); 
            $privacy = 2;
         } else {
            $remove_fav = $conn->prepare("UPDATE users SET pub_vids = pub_vids + 1 WHERE uid = ?");
            $remove_fav->execute([$session['uid']]);
            $privacy = 1;
         }
		$stmt = $conn->prepare("INSERT iGNORE INTO videos (uid, vid, tags, title, description, file, privacy, cdn, recorddate, address, addrcountry) VALUES (:uid, :vid, :tags, :title, :description, :file, :privacy, :cdn, :recorddate, :address, :country)");
$stmt->execute([
    ':uid' => $session['uid'],
    ':vid' => $video_id,
    ':tags' => $field_upload_tags,
    ':title' => $_POST['field_upload_title'],
    ':description' => $_POST['field_upload_description'],
    ':file' => strip_tags($_FILES['fileToUpload']['name']),
    ':privacy' => $privacy,
    ':cdn' => $video_CDN,
    ':recorddate' => $recorddate,
    ':address' => $_POST['field_upload_address'],
    ':country' => $_POST['field_upload_country']
]);

    // Create a new cURL resource

  

$request = (object) [
    "targetdir" => __DIR__ . '/data/videos/',
    "vfile" => $_FILES["fileToUpload"],
    "vext" => strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION)),
    "v_id" =>  trim($video_id)
];
if(move_uploaded_file($request->vfile['tmp_name'], $request->targetdir.$request->v_id."_temp.".$request->vext)) {
system(sprintf('php cdn/process/video_process.php "%s" "%s" "%s" > %s 2>&1 &', $request->targetdir, $request->vext, $request->v_id, './cdn/data/.log'));
    }
    $successful = "/my_videos_upload_complete.php?v=" . $video_id;

    header("Location: $successful");
    exit();

    }
}
} else {
session_error_index("Invalid file format.", "error");
}


?>