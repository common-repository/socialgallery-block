<?php
// Requesting Instagram media data using Instagram Graph API
// This request sends a user's access token to retrieve media data
// More info: https://developers.facebook.com/docs/instagram-api

function socialgallery_block_is_valid_instagram_token($access_token) {
    $url = "https://graph.instagram.com/me?fields=id&access_token=" . $access_token;

    // Make the request using wp_remote_get
    $response = wp_remote_get($url);

    // Check for errors and retrieve the response code
    if (is_wp_error($response)) {
        return false;
    }

    $http_code = wp_remote_retrieve_response_code($response);

    // Check if response is valid and token is valid
    if ($http_code == 200) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['id'])) {
            return true;
        }
    }

    return false;
}

function socialgallery_block_render_callback($attributes){
    $socialgalleryblockaccesskey = get_option('socialgallery_block_access_token');
    $uniqueid = $attributes['uniqueid'];

    if (socialgallery_block_is_valid_instagram_token($socialgalleryblockaccesskey)):
        $list_items_markup = '';
        $access_token = $socialgalleryblockaccesskey;
        $endpoint = "https://graph.instagram.com/me?fields=id,username&access_token=$socialgalleryblockaccesskey";

        // Make the request using wp_remote_get
        $response = wp_remote_get($endpoint);

        // Check for errors
        if (is_wp_error($response)) {
            echo 'Error:' . esc_html($response->get_error_message());
        } else {
            // Retrieve the response code and body
            $http_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);

            // Decode the JSON response
            $data = json_decode($body, true);

            // Check if the response contains the user ID
            if ($http_code == 200 && isset($data['id'])) {
                $user_id = $data['id'];
                $user_name = $data['username'];
            } else {
                echo 'Error retrieving user info.';
            }
        }

        $initial_count = 20;
        $max_count = 50; // Maximum number of posts to fetch at once

        $displaydesktop=($attributes['hidedesktop'] != true) ? 'hide-desktop' : '';
        $displaytablet=($attributes['hidetablet'] != true)   ? 'hide-tablet' : '';
        $displaymobile=($attributes['hidemobile'] != true)   ? 'hide-mobile' : '';
        $displayclass=$displaydesktop.' '.$displaytablet.' '.$displaymobile;
        $alignmentClass = 'has-text-align-'.$attributes['captionalign'];

        $layoutcss="";
        $layoutcsstab="";
        $layoutcssmob="";
        if($attributes['layout']==='columns'){
            $layoutcss.="#".$uniqueid.".socialgallery-block-wrapper{grid-template-columns: repeat(".$attributes['nocolumn'].", 1fr);}";
            $layoutcsstab.="#".$uniqueid.".socialgallery-block-wrapper{grid-template-columns: repeat(".$attributes['nocolumntab'].", 1fr);}";
            $layoutcssmob.="#".$uniqueid.".socialgallery-block-wrapper{grid-template-columns: repeat(".$attributes['nocolumnmob'].", 1fr);}";
        }else if($attributes['layout']==='custom'){
            $layoutcss.="";
        }

        $boxshadowcss=($attributes['boxshadow'] == true) ? $attributes['hshadow'].'px '.$attributes['vshadow'].'px '.$attributes['blurshadow'].'px '.$attributes['shadowColor']:'';

        $bordertop=(!empty($attributes['border']['top'])) ? $attributes['border']['top']['width'].' '. $attributes['border']['top']['style'].' '. $attributes['border']['top']['color'] : null;
        $borderright=(!empty($attributes['border']['right'])) ? $attributes['border']['right']['width'].' '. $attributes['border']['right']['style'].' '. $attributes['border']['right']['color'] : null;
        $borderbottom=(!empty($attributes['border']['bottom'])) ? $attributes['border']['bottom']['width'].' '. $attributes['border']['bottom']['style'].' '. $attributes['border']['bottom']['color'] : null;
        $borderleft=(!empty($attributes['border']['left'] )) ? $attributes['border']['left']['width'].' '. $attributes['border']['left']['style'].' '. $attributes['border']['left']['color'] : null;

        $capstyle=($attributes['captionstyle']!='default') ? 'insta-feed-overlay' : '';

        $background = '';
          if( $attributes['bgoverlayColor'] == '' && $attributes['bggradientoverlayValue'] != ''){
            $background = $attributes['bggradientoverlayValue'];
          }
          else if( $attributes['bgoverlayColor'] != '' && $attributes['bggradientoverlayValue'] == ''){
            $background = $attributes['bgoverlayColor'];
          }
        $increment = 0;
        // if(isset($_GET['count']) && is_numeric($_GET['count'])){
        //     $increment = $_GET['count'];
        //     $max_count = $attributes['noposts'];
        // } else {
        //     $increment = 0;
        // }
        // echo 'dev'.$increment;
        $url = "https://graph.instagram.com/{$user_id}/media";
        $params = [
            'fields' => 'id,media_type,media_url,thumbnail_url,caption,timestamp',
            'access_token' => $access_token,
            'limit' => $attributes['noposts'],
        ];

        // Build the URL with query parameters
        $endpoint = add_query_arg($params, $url);

        // Make the request using wp_remote_get
        $response = wp_remote_get($endpoint);

        // Check for errors
        if (is_wp_error($response)) {
            echo 'Error:' . esc_html($response->get_error_message());
        } else {
            // Retrieve the response body
            $body = wp_remote_retrieve_body($response);
            
            // Decode the JSON response
            $data = json_decode($body, true);
            
            // Handle the data as needed
        }

        $masonryclass=$masonryitemclass='';
        if($attributes['gallerylayout']==='masonry'){
            $masonryclass='masonry';
            $masonryitemclass='item';

        }
        
        $list_items_markup.=$attributes['customcss'];

      
            $list_items_markup.= '<div id="'.$uniqueid.'" class="socialgallery-block-wrapper '.$displayclass.' '.$alignmentClass.' '.$masonryclass.' ">';
            //print_r($data['data']);
            foreach (array_slice($data['data'], $increment, $initial_count) as $post) {
                $postcaption='';
                if(isset($post['caption'])){$postcaption=$post['caption'];}
                $list_items_markup.= '<div id="dev" class="socialgallery-block-item '.$masonryitemclass.' '.$capstyle.'" data-type="'.$post['media_type'].'" data-src="' . $post['media_url'] . '" data-caption="' . $postcaption . '">';
                if ($post['media_type'] == 'IMAGE') {
                    $list_items_markup.= '<img src="' . $post['media_url'] . '" alt="Instagram Image">';
                } elseif ($post['media_type'] == 'VIDEO') {
                    $list_items_markup.= '<video autoplay >';
                    $list_items_markup.= '<source src="' . $post['media_url'] . '" type="video/mp4">';
                    $list_items_markup.= 'Your browser does not support the video tag.';
                    $list_items_markup.= '</video>';
                }
                $list_items_markup.= '<div class="gallery-content">';
                if(isset($post['caption'])){
                    if($attributes['hidecaption']==true){
                        $list_items_markup.= '<p>' . $post['caption'] . '</p>';
                    }
                }
                $list_items_markup.= '</div>';
                // $list_items_markup.= '<p>Timestamp: ' . date('Y-m-d H:i:s', $post['timestamp']) . '</p>';
                $list_items_markup.= '</div>';
            }
            $list_items_markup.= '</div>';
          
        
        $list_items_markup.='<div id="lightbox" class="lightbox alignfull">
                                <div class="lightbox-content">
                                    <span class="close">&times;</span>
                                    <div class="media"></div>
                                    <div class="caption"></div>
                                </div>
                            </div>';

        $list_items_markup.=$attributes['customjs'];

        return $list_items_markup;
    endif;

}

