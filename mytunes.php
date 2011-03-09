<?php
/*
Plugin Name: Last.fm Feed Reader
Plugin URI: http://stephenyeargin.com/blog/tag/plugins/
Description: An AJAX-enabled feed parser for Last.fm
Author: Stephen Yeargin
Version: 0.2
Author URI: http://stephenyeargin.com/
*/

// Make sure that WordPress is loaded
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php')) {
	$MY_TUNES_WP_PATH = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/stephenyeargin.com/wp-load.php' )) {
	$MY_TUNES_WP_PATH = $_SERVER['DOCUMENT_ROOT'] . '/stephenyeargin.com/wp-load.php';
}

require_once($MY_TUNES_WP_PATH);


/**
 * Show Last.fm Feed
 *
 * @param	int		Number of items to show
 * @return	string	Displays last $count of items in a list
 */
function show_lastfm($count) {

    // Import and run MagpieRSS
    if (!function_exists('MagpieRSS')) {
        require_once (ABSPATH . WPINC . '/rss.php');
        error_reporting(E_ERROR);
    }
       
    $options['mytunes_username'] = 'yearginsm';
    
    $_last_fm_url = 'http://ws.audioscrobbler.com/1.0/user/';
    $_last_fm_url .= $options['mytunes_username'] . '/recenttracks.rss';

    if (!is_numeric($count) || $count < 1) { $count=5; }

    // Clear the cache if a refresh
    global $wpdb, $is_refresh;
    $feed_hash = md5($_last_fm_url);
    if ($is_refresh == true) {
        $wpdb->query("UPDATE wp_options SET option_value = 0 WHERE option_name LIKE '%$feed_hash%'; ");
    }
   
    $rss = fetch_rss($_last_fm_url);
    $items = array_slice($rss->items, 0, $count);
    $time_offset = 3600*get_option('gmt_offset');
    
    if (count($items) == 0) {
        print "<ul><li><a href=\"javascript:void(0)\" onclick=\"alert('My feed is broken. That\'s really an error message.');\">Help!</a><br />The Beatles</li></ul>";
    } else {
        print "<ul>\n";
        foreach ($items as $item ) {
            	// We do a bit of slicing to get the artist and song
            	$info = explode(' – ',$item['title']);
            	$artist= htmlentities($info[0]);
            	$song = htmlentities($info[1]);
            	$url   = $item['link'];
            	$played = date('m/d/Y h:i a', strtotime($item['pubdate'])+$time_offset);
            	print "  <li><a href=\"$url\" title=\"Played: $played\">$song</a><br />\n";
            	print "      <small>$artist</small></li>\n";
        }
        print "</ul>\n";
    }
}

// Make sure output is set
if (isset($_REQUEST['output'])) {
	$output_format = $_REQUEST['output'];
} else {
	$output_format = null;
}

// Handle AJAX requests
if ($output_format == 'html') {
    $is_refresh = true;
    $count = $_REQUEST['count'];
    show_lastfm($count);
    exit;
}


// Show JS Script
if ($output_format == 'js') {
header('Content-type: text\javascript');
	$path = get_bloginfo('url') . '/wp-content/plugins/mytunes/mytunes.php';
	print "function updateMyTunes(items) {\n";
	print "   jQuery('#myTunes').fadeOut(1500).load('$path', { output: 'html', count: items }).fadeIn();\n";
	print "}\n";
	exit;
}

// This is required for the refresh button to work
// You must also be using the wp_head() template tag
function addMyTunesScript() {
    wp_enqueue_script('mytunes', $_SERVER['SCRIPT_NAME'] . '?output=js', array('jquery'), '0.2' );
}

// Loads only on template pages instead of in Admin
add_action('template_redirect', 'addMyTunesScript'); 
