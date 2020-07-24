<?php

/*
Plugin Name: Yahoo Watcher
Description: Yahoo post watcher
Version: 1.0
Author: leonidpodriz
Author URI: https://leonidpodriz.github.io/
*/

require "posts_creator.php";

function yahoo_watcher_setup()
{
    register_post_type('finance', array(
        'label' => 'Finance',
        'labels' => array(
            'name' => 'Finance',
            'singular_name' => 'Finance',
            'menu_name' => 'Finance',
            'all_items' => 'All finance posts',
            'add_new' => 'Add finance post',
            'add_new_item' => 'Add new finance post',
            'edit' => 'Edit',
            'edit_item' => 'Edit finance post',
            'new_item' => 'New finance post',
        ),
        'description' => 'Posts from Yahoo.com',
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_rest' => false,
        'rest_base' => '',
        'show_in_menu' => true,
        'exclude_from_search' => false,
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'hierarchical' => false,
        'rewrite' => array('slug' => 'finance/%finance%', 'with_front' => false, 'pages' => false, 'feeds' => false, 'feed' => false),
        'has_archive' => 'finances',
        'query_var' => true,
        'supports' => array('title', 'editor')
    ));

    register_post_type('entertainment', array(
        'label' => 'Entertainment',
        'labels' => array(
            'name' => 'Entertainment',
            'singular_name' => 'Entertainment',
            'menu_name' => 'Entertainment',
            'all_items' => 'All entertainment posts',
            'add_new' => 'Add entertainment post',
            'add_new_item' => 'Add new entertainment post',
            'edit' => 'Edit',
            'edit_item' => 'Edit entertainment post',
            'new_item' => 'New entertainment post',
        ),
        'description' => 'Posts from Yahoo.com',
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_rest' => false,
        'rest_base' => '',
        'show_in_menu' => true,
        'exclude_from_search' => false,
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'hierarchical' => false,
        'rewrite' => array('slug' => 'entertainment/%entertainment%', 'with_front' => false, 'pages' => false, 'feeds' => false, 'feed' => false),
        'has_archive' => 'entertainment',
        'query_var' => true,
        'supports' => array('title', 'editor')
    ));

}

add_action('init', 'yahoo_watcher_setup');

function yahoo_watcher_activate()
{
    yahoo_watcher_setup();
    flush_rewrite_rules();

    updateYahooPosts();
}

add_action('update_yahoo_action_hook', 'updateYahooPosts');

function updateYahooPosts()
{

    $finance_post_creator = new FinanceRSSParser;
    $finance_post_creator -> createNewPosts();

    $entertainment_post_creator = new EntertainmentRSSParser;
    $entertainment_post_creator -> createNewPosts();

    wp_schedule_single_event(time() + 7200, 'update_yahoo_action_hook');
}

register_activation_hook(__FILE__, 'yahoo_watcher_activate');

function yahoo_watcher_deactivate()
{
    unregister_post_type('book');
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'yahoo_watcher_deactivate');