<?php

class BaseRSSParser
{
    public $rss_url = "https://rss.url/";

    private function get_xml_data($link)
    {
        return simplexml_load_file($link);
    }

    public function xmlToArray($xmlObject, $out = array())
    {
        foreach ((array)$xmlObject as $index => $node)
            $out[$index] = (is_object($node)) ? $this->xmlToArray($node) : $node;

        return $out;
    }

    public function get_rss_posts()
    {
        $posts = array();
        $rss_posts = $this->get_xml_data($this->rss_url)->{"channel"}->item;
        foreach ($rss_posts as $rss_post) {
            array_push($posts, $rss_post);
        }

        return $posts;
    }
}

class RSSWordPressPostsCreator extends BaseRSSParser
{
    public $post_type = "post";

    private function convert_post_data($post_data)
    {
        return array(
            'post_title' => $post_data->title,
            'post_content' => $post_data->description,
            'post_type' => $this->post_type,
            'post_status' => 'publish',
            'post_author' => 1,
        );
    }

    private function publish_post_if_need($rss_posts)
    {
        $found_post = null;

        if ($posts = get_posts(array(
            'post_title' => $rss_posts->title -> __toString(),
            'post_type' => $this->post_type,
            'posts_per_page' => 1,
        ))) $found_post = $posts[0];

        if (!is_null($found_post)) {
            $wp_post = $this->convert_post_data($rss_posts);
            wp_insert_post($wp_post);
        }
    }

    public function createNewPosts()
    {
        $rss_posts = $this->get_rss_posts();
        array_map(array($this, "publish_post_if_need"), $rss_posts);
    }
}