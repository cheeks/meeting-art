<?php
  include_once DIR_PLUGINS . '/instaphp/instaphp.php';
	
  set_time_limit(120);
  $tag = (isset($_REQUEST['tag']) ? $_REQUEST['tag'] : 'meetingart');
  $previous_run = Photo::find('first', array('conditions' => "active_flag = 1", 'order' => 'photo_date DESC'));
  $params = array();

  if (is_null($previous_run)) {
    // in either of these cases, grab 20 pages of data as to not hammer Instagram needlessly
    if (!is_null($previous_run) && $ancient_history_flag) {
      $params = array('max_tag_id' => $previous_run->instagram_id);
    } else if (is_null($previous_run)) {
      $previous_run = new Import();
      $previous_run->tag = strtolower($tag);
    }
    for ($i = 0; $i < 20; $i++) {
      $api = Instaphp\Instaphp::Instance();
      $response = $api->Tags->Recent($tag, $params);
      /*
        echo "<pre>";
        var_dump($response);
        echo "</pre>";
        die;
       */
      if (empty($response->error) && count($response->data) > 0) {
        if (!isset($previous_run->most_recent_tag_id) || is_null($previous_run->most_recent_tag_id) || $response->pagination->min_tag_id > $previous_run->most_recent_tag_id) {
          $previous_run->most_recent_tag_id = $response->pagination->min_tag_id;
        }
        if (!isset($previous_run->oldest_tag_id) || is_null($previous_run->oldest_tag_id)) {
          if ((isset($response->pagination->next_max_tag_id) && $response->pagination->next_max_tag_id < $previous_run->oldest_tag_id)) {
            $previous_run->oldest_tag_id = $response->pagination->next_max_tag_id;
          } else if (!isset($response->pagination->next_max_tag_id)) {
            $previous_run->oldest_tag_id = $response->pagination->min_tag_id;
          }
        }
        // shortcut if this is the last page 
        foreach ($response->data as $item) {
          $photos = Photo::all(array('conditions' => "instagram_id='" . addslashes($item->id) . "'"));
          if (count($photos) == 0) {
            $photo = new Photo();
            $photo->instagram_id = $item->id;
            if (isset($item->tags) && count($item->tags) > 0) {
              $photo->tags_used = implode(',', $item->tags);
            }
            $photo->photo_url_large = str_replace('http://', 'https://', $item->images->standard_resolution->url);
            $photo->photo_url_medium = str_replace('http://', 'https://', $item->images->low_resolution->url);
            $photo->photo_username = $item->user->username;
            $photo->photo_full_name = $item->user->full_name;
            $photo->photo_full_name = $item->user->full_name;
            if (isset($item->caption)) {
              $photo->photo_datetime = date('Y-m-d H:i:s', $item->caption->created_time);
            } else {
              $photo->photo_datetime = date('Y-m-d H:i:s');
            }
            $photo->instagram_likes = $item->likes->count;
            try {
              $photo->save();
            } catch (Exception $e) {
              // prolly a unique key error. which means we have it already
              echo $e->getMessage();
            }
          }
        }
        if (isset($response->pagination->next_max_tag_id)) {
          $params['max_id'] = $response->pagination->next_max_tag_id;
          sleep(5);
        } else {
          $i = 20;
        }
      } else {
        $i = 20;
      }
    }
    if (isset($previous_run->most_recent_tag_id) && !is_null($previous_run->most_recent_tag_id)) {
      $previous_run->save();
    }
  } else {
    // in this case, we need to take everything from now until the last time we did an update
    $target_tag_id = $previous_run->most_recent_tag_id;
    $continue = true;
    $params = array();
    $i = 0;
    while ($continue) {

      $api = Instaphp\Instaphp::Instance();
      $response = $api->Tags->Recent($tag, $params);

      /*
        echo "Iteration[".$i."]: ".count($response->data)."<br />";
        if (count($response->data) == 0) {
        echo "<pre>";
        var_dump($response);
        echo "</pre>";

        }
        if ($i == 20) {
        exit;
        }
       */
      if (empty($response->error)) {
        if (!isset($previous_run->most_recent_tag_id) || is_null($previous_run->most_recent_tag_id) || $response->pagination->min_tag_id > $previous_run->most_recent_tag_id) {
          $previous_run->most_recent_tag_id = $response->pagination->min_tag_id;
        }
        if (!isset($response->pagination->next_max_tag_id) || $response->pagination->next_max_tag_id < $target_tag_id) {
          $continue = false;
        }
        // shortcut if this is the last page 
        foreach ($response->data as $item) {
          $photos = Photo::all(array('conditions' => "instagram_id='" . addslashes($item->id) . "'"));
          if (count($photos) == 0) {
            $photo = new Photo();
            $photo->instagram_id = $item->id;
            if (isset($item->tags) && count($item->tags) > 0) {
              $photo->tags_used = implode(',', $item->tags);
            }
            $photo->photo_url_large = str_replace('http://', 'https://', $item->images->standard_resolution->url);
            $photo->photo_url_medium = str_replace('http://', 'https://', $item->images->low_resolution->url);
            $photo->photo_username = $item->user->username;
            $photo->photo_full_name = $item->user->full_name;
            $photo->photo_full_name = $item->user->full_name;
            if (isset($item->caption)) {
              $photo->photo_datetime = date('Y-m-d H:i:s', $item->caption->created_time);
            } else {
              $photo->photo_datetime = date('Y-m-d H:i:s');
            }
            $photo->instagram_likes = $item->likes->count;
            try {
              $photo->save();
            } catch (Exception $e) {
              // prolly a unique key error. which means we have it already
              echo $e->getMessage();
            }
          }
        }
        if (isset($response->pagination->next_max_tag_id)) {
          $params['max_id'] = $response->pagination->next_max_tag_id;
          sleep(5);
        } else {
          $continue = false;
        }
      }
      $i++;
    }
    $previous_run->save();
  }
