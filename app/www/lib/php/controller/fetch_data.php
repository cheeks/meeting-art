<?php
  include_once DIR_PLUGINS . '/instaphp/instaphp.php';
	
  set_time_limit(120);
  $tag = (isset($_REQUEST['tag']) ? $_REQUEST['tag'] : 'meetingart');
  $previous_run = Photo::find('first', array('conditions' => "active_flag = 1", 'order' => 'photo_date DESC'));
  $params = array();

  if (!is_null($previous_run)) {
  	$params = array('max_tag_id' => $previous_run->instagram_id);
  }

    for ($i = 0; $i < 20; $i++) {
      $api = Instaphp\Instaphp::Instance();
      $response = $api->Tags->Recent($tag, $params);
      
        // echo "<pre>";
        // var_dump($response);
        // echo "</pre>";
        // die;
       
      if (empty($response->error) && count($response->data) > 0) {
        // shortcut if this is the last page 
        foreach ($response->data as $item) {
          $photos = Photo::all(array('conditions' => "instagram_id='" . addslashes($item->id) . "'"));
          if (count($photos) == 0) {
            $photo = new Photo();
            $photo->instagram_id = $item->id;
            $photo->photo_url = str_replace('http://', 'https://', $item->images->low_resolution->url);
            $photo->user_name = $item->user->username;
            $photo->description = $item->caption->text;
            if (isset($item->caption)) {
              $photo->photo_date = date('Y-m-d H:i:s', $item->caption->created_time);
            } else {
              $photo->photo_date = date('Y-m-d H:i:s');
            }
            try {
            	echo "saving photo" . $item->id . '<br/>';
              $photo->save();
            } catch (Exception $e) {
              // prolly a unique key error. which means we have it already
              echo $e->getMessage();
            }
          }
        }
        if (isset($response->pagination->next_max_tag_id)) {
          $params['max_id'] = $response->pagination->next_max_tag_id;
          sleep(1);
        } else {
          $i = 20;
        }
      } else {
        $i = 20;
      }
    }
  
