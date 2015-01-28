<?php
/**
 * Plugin Name: change content url to permalink
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: find old urls in content,
 * Version: 1
 * Author: leta
 * 
 * 1. find old urls in content
 * 2. find matching guid, therefore post id
 * 3. create the friendly url
 * 4. replace old url
 *
 **/

global $wpdb;

/*
$results = $wpdb->get_results( 
"(select ID, guid, post_content, post_title from wp_11_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_12_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_13_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_14_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_16_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_17_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_20_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_21_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_22_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_23_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_24_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_25_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_26_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_27_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_28_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_3_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_6_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_7_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_8_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_9_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_posts where post_content like '%?page_id=%')", 
OBJECT );
*/
$results = $wpdb->get_results( 
"(select ID, guid, post_content, post_title from wp_3_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_4_posts where post_content like '%?page_id=%')
union
(select ID, guid, post_content, post_title from wp_posts where post_content like '%?page_id=%')", 
OBJECT );

echo '<pre>';
print_r($results);
echo '</pre>';



 ?>