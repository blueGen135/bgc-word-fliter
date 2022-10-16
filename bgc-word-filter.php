<?php

/*
  Plugin Name: BGC Word Filter Plugin
  Description: Replaces a list of words.
  Version 1.0
  Author: Raaj
  Author URI: https://github.com/blueGen135
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class BGCWordFilterPlugin{

	function __construct(){

		add_action( 'admin_menu', array($this, 'ourMenu'));
		add_action('admin_init', array($this, 'ourSettings'));
		if(get_option('plugin_words_to_filter')){
		add_filter( 'the_content', array($this, 'filterLogic') );
		}
	}

	function ourSettings() {
	    add_settings_section('replacement-text-section', null, null, 'word-filter');
	    register_setting('replacementFields', 'replacementText');
	    add_settings_field('replacement-text', 'Filtered Text', array($this, 'replacementFieldHTML'), 'word-filter', 'replacement-text-section');
	}
	function replacementFieldHTML() { ?>
	    <input type="text" name="replacementText" value="<?php echo esc_attr(get_option('replacementText', '***')) ?>">
	    <p class="description">Leave blank to simply remove the filtered words.</p>
	<?php }  
	function filterLogic($content){
		$badWords = explode(',', get_option( 'plugin_words_to_filter')); 
		$badWordsTrim = array_map('trim', $badWords);
		return str_ireplace($badWordsTrim, esc_html( get_option('replacementText', '****') ), $content);
	}

	function ourMenu(){
			$mainPageHook = add_menu_page('Words To Filter', 'Word Filter', 'manage_options', 'word-filter', array($this, 'wordFilterPage'), 'dashicons-media-text', 100);

		 	add_submenu_page('word-filter', 'Words To Filter', 'Words List', 'manage_options', 'word-filter', array($this, 'wordFilterPage'));
		    add_submenu_page('word-filter', 'Word Filter Options', 'Options', 'manage_options', 'word-filter-options', array($this, 'optionsSubPage'));
		    add_action("load-{$mainPageHook}", array($this, 'mainPageAssets'));

	}

	function mainPageAssets() {
  		  wp_enqueue_style('filterAdminCss', plugin_dir_url(__FILE__) . 'style.css');
  	}

  	function handleForm(){
  		if (wp_verify_nonce($_POST['ourNonce'], 'saveFilterWords') AND current_user_can('manage_options')) :
		      update_option('plugin_words_to_filter', sanitize_text_field($_POST['plugin_words_to_filter'])); ?>
		      <div class="updated"><p>Your filtered words were saved.</p></div>
	    <?php else: ?>
	      <div class="error"><p>Sorry, you do not have permission to perform that action.</p></div>
    <?php endif; 
	}

	function wordFilterPage(){ ?>
		<div class="wrap">
		      <h1>Word Filter</h1>

		      <?php if(isset($_POST['submit'])) $this->handleForm() ?>

		      <form method="POST" action="">
		      	<?php wp_nonce_field('saveFilterWords', 'ourNonce') ?>
		        <label for="plugin_words_to_filter"><p>Enter a <strong>comma-separated</strong> list of words to filter from your site's content.</p></label>
		        <div class="word-filter__flex-container">
		          <textarea name="plugin_words_to_filter" id="plugin_words_to_filter" placeholder="bad, mean, awful, horrible"><?php echo esc_textarea(get_option('plugin_words_to_filter')) ?></textarea>
		        </div>
		        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		      </form>
	    </div>
	<?php }


    function optionsSubPage() { ?>
    <div class="wrap">
      <h1>Word Filter Options</h1>
      <form action="options.php" method="POST">
        <?php
          settings_errors();
          settings_fields('replacementFields');
          do_settings_sections('word-filter');
          submit_button();
        ?>
      </form>
    </div>
  <?php }
}

$bgcWordFilter = new BGCWordFilterPlugin();

?>