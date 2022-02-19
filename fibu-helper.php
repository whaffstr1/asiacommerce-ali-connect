<?php

class AliFibuHelper
{
    public function hasImage($id)
    {
        $existing = get_post_meta($id, '_knawatfibu_url', true);
      
        return $existing ? true : false;
    }
    
    public function hasGallery($id)
    {
        $existing = get_post_meta($id, '_knawatfibu_gallary', true);
      
        return $existing ? true : false;
    }
    
    // Set Image for Product or Variation
    public function setImage($id, $url)
    {
        $payload = serialize([
            "img_url" => $url,
            "width" => 820,
            "height" => 360
        ]);

        if ($this->hasImage($id)) {
            $result = update_post_meta($id, '_knawatfibu_url', $payload);
        } else {
            $result = add_post_meta($id, '_knawatfibu_url', $payload, true);
        }

        return $result;
    }

    // Set gallery
    public function setGallery($id, $urls = [])
    {
        $payload = [];
        foreach ($urls as $url) {
            $payload[] = [
                "url" => $url,
                "width" => 820,
                "height" => 360
            ];
        }

        if ($this->hasGallery($id)) {
            return update_post_meta($id, '_knawatfibu_wcgallary', $payload);
        }

        return add_post_meta($id, '_knawatfibu_wcgallary', serialize($payload), true);
    }
}
