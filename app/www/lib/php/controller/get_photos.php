<?php
header('Content-Type: application/json');
$params = array('conditions' => 'active_flag = 1', 'order' => 'photo_date DESC');
$photos = Photo::find('all', $params);

$retval = array();
foreach($photos as $photo) {
  $temp = array();
  $temp['id'] = $photo->id;
  $temp['photo_url'] = $photo->photo_url;
  $temp['photo_username'] = $photo->user_name;
  $temp['instagram_id'] = $photo->instagram_id;
  $temp['description'] = htmlspecialchars($photo->description);
  $retval[] = $temp;
}

echo json_encode($retval);