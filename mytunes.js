//
// MyTunes Refresh
// Author: Stephen Yeargin; Version: 0.1
//
// Simple script that retreives HTML output and replaces
// the contents of the myTunes div when 'Referesh' icon
// is clicked.
//

function updateMyTunes(items) {
    jQuery('#myTunes').fadeOut(1500).load('/wp-content/plugins/mytunes/mytunes.php', { output: 'html', count: items }).fadeIn();
}
  