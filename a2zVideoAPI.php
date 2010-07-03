<?php
/*
Plugin Name: a2zVideoAPI widget
Plugin URI: http://svnlabs.com/a2zvideoapi/
Description: a2zVideoAPI allows you to adds a sidebar widget to show video from various sites including youtube, dailymotion, google, vimeo, metacafe, blip.tv, hulu, 5min, myspace, ehow, break, flickr etc.
Author: Sandeep Verma
Version: 0.7
Author URI: http://blog.svnlabs.com
Other: Curl must be on your server to use this plugin. This widget tested to latest version of wordpress.
*/

/*

 Some API supported URL:
	http://www.youtube.com/watch?v=mXMf9GOzzOA
	http://www.dailymotion.com/video/x5z91e_lets-play-holi_music
	http://video.google.com/videoplay?docid=-7577046582869136330&hl=en
	http://www.vimeo.com/9573920
	http://www.metacafe.com/watch/4230785/ghetto_star_weekly_randy_radermacher/
	http://blip.tv/file/3272712?utm_source=featured_ep&utm_medium=featured_ep
	http://www.hulu.com/watch/131066/saturday-night-live-we-are-the-world-cold-open
	http://www.viddler.com/explore/coop/videos/54/
	http://www.5min.com/Video/How-to-Organize-Your-Life-219728873
	http://vids.myspace.com/index.cfm?fuseaction=vids.individual&videoid=51722257
	http://vodpod.com/watch/2492783-reactos-install-screencast-tutorial
	http://www.ehow.com/video_4983481_change-ip-address-windows-vista.html
	http://www.break.com/usercontent/2009/4/How-to-Run-Linux-on-Windows-Ubuntu-699185.html
	http://www.atom.com/funny_videos/sw_gangsta_rap_chronicles/
	http://www.funnyordie.com/videos/4d47a07835/danny-mendlow-the-solution-to-racism-and-the-biggest-issue-in-the-world
	http://www.flickr.com/photos/traceytilson/3033319841/ 

*/


// Put functions into one big function we'll call at the plugins_loaded
// action. This ensures that all required plugin functions are defined.
function widget_a2zvideoapi_init() {

	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') )
		return;

	// This is the function that outputs our little Google search form.
	function widget_a2zvideoapi($args) {
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);
		
		
		// Each widget can store its own options. We keep strings here.
		$options = get_option('widget_a2zvideoapi');
		$videoid = $options['videoid'];
		$width = $options['width'];
		$height = $options['height'];
		$title = ($options['title'] != "") ? $before_title.$options['title'].$after_title : "";  
		$embed = html_entity_decode($options['embed']);
		
		// These lines generate our output. Widgets can be very complex
		// but as you can see here, they can also be very, very simple.
		//echo $before_widget . $before_title . $title . $after_title;
		

		$embed = preg_replace('/(width)=("[^"]*")/i', 'width="'.$width.'"', $embed);
		$embed = preg_replace('/(height)=("[^"]*")/i', 'height="'.$height.'"', $embed);
			 
		echo $before_widget;
		echo $title;
		echo $embed;
		echo $after_widget;
	
	}

	// This is the function that outputs the form to let the users edit
	// the widget's title. It's an optional feature that users cry for.
	function widget_a2zvideoapi_control() {

		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_a2zvideoapi');
		
		
		if ( !is_array($options) )
			$options = array('title'=>'', 
				'video'=> 'http://www.youtube.com/watch?v=AYxu_MQSTTY',
				'videoid' => 'AYxu_MQSTTY',
				'width' => '200',
				'height' => '165',
				
			);
			
			
		if ( $_POST['a2zvideoapi-submit'] ) {

			// Remember to sanitize and format use input appropriately.
			
			$options['video'] = strip_tags(stripslashes($_POST['a2zvideoapi-video']));
			$options['width'] = strip_tags(stripslashes($_POST['a2zvideoapi-width']));
			$options['height'] = strip_tags(stripslashes($_POST['a2zvideoapi-height']));
			
						
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			
			/* Video API Server */
			curl_setopt($ch, CURLOPT_URL, 'http://www.svnlabs.com/a2zvideoapi/server.php');
			curl_setopt($ch, CURLOPT_POST, true);
			
			/* Request variables URL & Format for API server  */
			$post = array(
			  'url'    => $options['video'],
			  'format'    => "json" 
			);
			
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			
			$result = curl_exec($ch);
			$response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			/* Output from a2zVideoAPI */
			if($response==200)
		    {
			  $vid = json_decode($result);
			  $options['title'] = $vid->video_title;
			  $options['embed'] = $vid->video_embed;
			
			}
			
			//  echo $result;
           
			
			update_option('widget_a2zvideoapi', $options);
		}

		// Be sure you format your options to be valid HTML attributes.
		
		$video = htmlspecialchars($options['video'], ENT_QUOTES);
		$width = htmlspecialchars($options['width'], ENT_QUOTES);
		$height = htmlspecialchars($options['height'], ENT_QUOTES);

		
		// Here is our little form segment. Notice that we don't need a
		// complete form. This will be embedded into the existing form.

		echo '<p style="text-align:right;"><label for="a2zvideoapi-video">' . __('Video:', 'widgets') . ' <input style="width: 200px;" id="a2zvideoapi-video" name="a2zvideoapi-video" type="text" value="'.$video.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="a2zvideoapi-width">' . __('Width:', 'widgets') . ' <input style="width: 200px;" id="a2zvideoapi-width" name="a2zvideoapi-width" type="text" value="'.$width.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="a2zvideoapi-height">' . __('Height:', 'widgets') . ' <input style="width: 200px;" id="a2zvideoapi-height" name="a2zvideoapi-height" type="text" value="'.$height.'" /></label></p>';

		echo '<input type="hidden" id="a2zvideoapi-submit" name="a2zvideoapi-submit" value="1" />';
	}
	
	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget(array('a2zVideoAPI', 'widgets'), 'widget_a2zvideoapi');

	// This registers our optional widget control form. Because of this
	// our widget will have a button that reveals a 300x100 pixel form.
	register_widget_control(array('a2zVideoAPI', 'widgets'), 'widget_a2zvideoapi_control', 300, 200);
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_a2zvideoapi_init');

?>