<?php

if (!defined('ABSPATH')) {
    exit;
}

class koo_api
{

    public function post_to_koo($params)
    {
        if (isset($params) && !empty(trim($params['key']))) {

            $headers = array(
                'Content-Type' => 'application/json',
                'X-KOO-API-TOKEN' => $params['key']
            );

            // max title length: 400
            $post_title = (strlen($params['title']) > 400)?substr($params['title'],0,400):$params['title'];

            // default language: english
            $body = array(
                "koo" => $post_title,
                "language" => "en",
                "link" => $params['url']
            );

            $args = array(
                'body'        => json_encode($body),
                'timeout'     => '5',
                'redirection' => '5',
                'httpversion' => '1.0',
                'blocking'    => true,
                'headers'     => $headers,
                'cookies'     => array(),
            );

            $response = wp_remote_post('https://api.kooapp.com/api/post/koo', $args);
            
            // expecting 'success' in response
            return json_decode(wp_remote_retrieve_body($response))->status;
             
        } else {
            return "Koo API key not found!";
        }
    }
}
