<?php

require "wp-upload-image-from-url.php";

class BaseRSSParser
{
    public $rss_url = "https://rss.url/";

    public function get_xml_data($link)
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
    public $max_post_pre_once = 1;
    private $posts;

    private function convert_post_data($post_data)
    {
        return array(
            'post_title' => $post_data -> title -> __toString(),
            'post_content' => $post_data -> children('media', True) -> text -> __toString(),
            'post_type' => $this->post_type,
            'post_status' => 'publish',
            'post_author' => 1,
        );
    }

    private function is_not_published($new_post)
    {
        // Not the best choice, need to be changed
        foreach ($this->posts as $post) {
            if ($post->post_title == $new_post -> title -> __toString()) return false;
        }
        return true;
    }

    private function remove_published_posts($rss_post)
    {
        return array_filter($rss_post, array($this, "is_not_published"));
    }

    private function publish_post_if_need($rss_post)
    {
        $wp_post = $this->convert_post_data($rss_post);
        $post_id = wp_insert_post($wp_post);
        $content = $rss_post -> children("media", True) -> content;
        $image_url = (string)$content -> attributes()["url"];
        ksa_upload_from_url($image_url, $post_id );
    }

    public function createNewPosts()
    {
        $this->posts = get_posts(array(
            'post_type' => $this->post_type,
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ));

        $rss_posts = $this->get_rss_posts();
        $posts = $this->remove_published_posts($rss_posts);
        foreach (array_slice($posts, 1, $this->max_post_pre_once) as $post) {
            $this->publish_post_if_need($post);
        }
    }
}