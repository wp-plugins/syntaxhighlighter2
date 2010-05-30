<?php
/*
 Plugin Name: SyntaxHighlighter2
 Plugin URI: http://mohanjith.com/wordpress/syntaxhighlighter2.html
 Description: An advanced upload-and-activate WordPress implementation of Alex Gorbatchev's <a href="http://code.google.com/p/syntaxhighlighter/">SyntaxHighlighter</a> JavaScript code highlighting package. See WordPress.com's "<a href="http://faq.wordpress.com/2007/09/03/how-do-i-post-source-code/">How do I post source code?</a>" for details.
 Author: S H Mohanjith
 Version: 2.1.2
 Author URI: http://mohanjith.com/
 Text Domain: syntaxhighlighter2
 License: GPL

 Credits:

 * Alex Gorbatchev ( alexgorbatchev.com ) -- SyntaxHighlighter (The Javascript Library)
 * Matt ( ma.tt ) -- original concept and code on WP.com
 * Viper007Bond ( viper007bond.com ) -- SyntaxHighlighter
 * S H Mohanjith ( mohanjith.com ) -- current plugin version (with theming)

 Simply put, Matt and Viper007Bond deserves the majority of the credit for
 this plugin. Viper007Bond took the plugin Matt had already written (it looked
 a lot like this current one) after seeing his code operate on WP.com and
 incorporated his ingenius TinyMCE handling and some other misc. code.
 I (mohanjith) just took the plugin SyntaxHighlighter and upgraded to the
 latest syntaxhighlighter and added theming functionality.

 **************************************************************************

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; version 3 of the License, with the
 exception of the JQuery JavaScript framework which is released
 under it's own license.  You may view the details of that license in
 the prototype.js file.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 */

class SyntaxHighlighter2 {
	private $languages = array();
	private $languagesregex;
	private $jsfiles2load = array();
	private $pluginurl;
	private $kses_active = array();
	private $kses_filters = array();
	private $widget_format_to_edit = false;
	private static $translation_domain = 'syntaxhighlighter2_trans_domain';

	// WordPress hooks
	public function SyntaxHighlighter2() {
		add_option('syntaxhighlighter2_theme', 'default');
		add_option('syntaxhighlighter2_post_code_in_posts', 'false');

		add_action( 'init', array(&$this, 'SetVariables'), 1000 );
		add_action( 'wp_head', array(&$this, 'AddStylesheet'), 1000 );
		add_action( 'admin_head', array(&$this, 'AddStylesheet'), 1000 );
		add_action( 'wp_footer', array(&$this, 'FileLoader'), 1000 );
		add_action( 'admin_footer', array(&$this, 'FileLoader'), 1000 ); // For viewing comments in admin area

		// Find and replace the BBCode
		add_filter( 'the_content', array(&$this, 'BBCodeToHTML'), 8 );
		add_filter( 'widget_text', array(&$this, 'BBCodeToHTML'), 8 );

		// Account for kses
		add_filter( 'content_save_pre', array(&$this, 'before_kses_normalization'), 1 );
		add_filter( 'content_save_pre', array(&$this, 'after_kses_normalization'), 11 );
		add_action( 'admin_head', array(&$this, 'before_kses_normalization_widget'), 1 );
		add_action( 'update_option_widget_text', array(&$this, 'after_kses_normalization_widget'), 1, 2 );
		add_filter( 'format_to_edit', array(&$this, 'after_kses_normalization_widget_format_to_edit'), 1 );

		// Account for TinyMCE
		add_filter( 'content_save_pre', array(&$this, 'TinyMCEDecode'), 8 );
		add_filter( 'the_editor_content', array(&$this, 'TinyMCEEncode'), 8 );

		if (get_option('syntaxhighlighter2_post_code_in_posts') == 'true') {
			// Uncomment these next lines to allow commenters to post code
			add_filter( 'comment_text', array(&$this, 'BBCodeToHTML'), 8 );
			add_filter( 'pre_comment_content', array(&$this, 'before_kses_normalization_comment'), 1 );
			add_filter( 'pre_comment_content', array(&$this, 'after_kses_normalization_comment'), 11 );
		}

		add_filter('admin_menu', array(&$this, 'admin_menu'));

		load_plugin_textdomain(self::$translation_domain, PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)), dirname(plugin_basename(__FILE__)));
	}


	// Set some variables now that we've given all other plugins a chance to load
	public function SetVariables() {
		$this->pluginurl = apply_filters( 'syntaxhighlighter2_url', get_bloginfo( 'wpurl' ) . '/wp-content/plugins/syntaxhighlighter2/files/' );
		if ( defined( 'WP_CONTENT_URL' ) )
		$this->pluginurl = apply_filters( 'syntaxhighlighter2_url', WP_CONTENT_URL . '/plugins/syntaxhighlighter2/files/' );
		// Define all allowed languages and allow plugins to modify this
		$this->languages = apply_filters( 'syntaxhighlighter2_languages', array(
			'as'         => 'shBrushAS3.js',
                        'as3'        => 'shBrushAS3.js',
			'bash'       => 'shBrushBash.js',
			'cpp'        => 'shBrushCpp.js',
			'c'          => 'shBrushCpp.js',
			'c++'        => 'shBrushCpp.js',
			'c#'         => 'shBrushCSharp.js',
                        'cf'         => 'shBrushColdFusion.js',
                        'coldfusion' => 'shBrushColdFusion.js',
			'c-sharp'    => 'shBrushCSharp.js',
			'csharp'     => 'shBrushCSharp.js',
			'css'        => 'shBrushCss.js',
			'delphi'     => 'shBrushDelphi.js',
                        'erlang'     => 'shBrushErlang.js',
			'pascal'     => 'shBrushDelphi.js',
			'diff'       => 'shBrushDiff.js',
			'groovy'     => 'shBrushGroovy.js',
			'java'       => 'shBrushJava.js',
                        'javafx'     => 'shBrushJavaFX.js',
			'js'         => 'shBrushJScript.js',
			'jscript'    => 'shBrushJScript.js',
			'javascript' => 'shBrushJScript.js',
			'patch'      => 'shBrushDiff.js',
			'perl'       => 'shBrushPerl.js',
			'php'        => 'shBrushPhp.js',
			'plain'      => 'shBrushPlain.js',
                        'powershell' => 'shBrushPowerShell.js',
			'py'         => 'shBrushPython.js',
			'python'     => 'shBrushPython.js',
			'rb'         => 'shBrushRuby.js',
			'ruby'       => 'shBrushRuby.js',
			'rails'      => 'shBrushRuby.js',
			'ror'        => 'shBrushRuby.js',
			'scala'      => 'shBrushScala.js',
			'sh'         => 'shBrushBash.js',
			'shell'      => 'shBrushBash.js',
			'sql'        => 'shBrushSql.js',
			'vb'         => 'shBrushVb.js',
			'vb.net'     => 'shBrushVb.js',
			'xml'        => 'shBrushXml.js',
			'html'       => 'shBrushXml.js',
			'xhtml'      => 'shBrushXml.js',
			'xslt'       => 'shBrushXml.js',
		) );

		$this->themes = apply_filters( 'syntaxhighlighter2_themes', array(
			'default'    => array('description' => 'Default', 'file' => 'shThemeDefault.css'),
			'django'     => array('description' => 'Django', 'file' => 'shThemeDjango.css'),
			'emacs'      => array('description' => 'Emacs', 'file' => 'shThemeEmacs.css'),
			'fadetogrey' => array('description' => 'Fade to Grey', 'file' => 'shThemeFadeToGrey.css'),
			'midnight'   => array('description' => 'Midnight', 'file' => 'shThemeMidnight.css'),
			'rdark'      => array('description' => 'RDark', 'file' => 'shThemeRDark.css'),
			'eclipse'      => array('description' => 'Eclipse', 'file' => 'shThemeEclipse.css'),
		) );

		// Quote them to make them regex safe
		$languages = array();
		foreach ( $this->languages as $language => $filename ) $languages[] = preg_quote( $language );

		// Generate the regex for them
		$this->languagesregex = '(' . implode( '|', $languages ) . ')';

		$this->kses_filters = apply_filters( 'syntaxhighlighter2_kses_filters', array(
			'wp_filter_kses',
			'wp_filter_post_kses',
			'wp_filter_nohtml_kses'
			) );
	}


	// We need to stick the stylesheet in the header for best results
	public function AddStylesheet() {
		echo '	<link type="text/css" rel="stylesheet" href="' . $this->pluginurl . 'shCore.css"></link>' . "\n";
		echo '	<link type="text/css" rel="stylesheet" href="' . $this->pluginurl . $this->themes[get_option('syntaxhighlighter2_theme', 'default')]['file'] . '"></link>' . "\n";
	}


	// This function checks for the BBCode cheaply so we don't waste CPU cycles on regex if it's not needed
	// It's in a seperate function since it's used in mulitple places (makes it easier to edit)
	public function CheckForBBCode( $content ) {
		if ( stristr( $content, '[sourcecode' ) && stristr( $content, '[/sourcecode]' ) ) return TRUE;
		if ( stristr( $content, '[source' ) && stristr( $content, '[/source]' ) ) return TRUE;
		if ( stristr( $content, '[code' ) && stristr( $content, '[/code]' ) ) return TRUE;

		return FALSE;
	}


	// This function is a wrapper for preg_match_all() that grabs all BBCode calls
	// It's in a seperate function since it's used in mulitple places (makes it easier to edit)
	public function GetBBCode( $content, $addslashes = FALSE ) {
		$regex = '/\[(sourcecod|sourc|cod)(e language=|e lang=|e=)';
		if ( $addslashes ) $regex .= '\\\\';
		$regex .= '([\'"])' . $this->languagesregex;
		if ( $addslashes ) $regex .= '\\\\';
		$regex .= '(\3\s*options=\3([a-z0-9\;\-\s:]*)\3|\3)\s*\](.*?)\[\/\1e\]/si';

		preg_match_all( $regex, $content, $matches, PREG_SET_ORDER );

		return $matches;
	}


	/* If KSES is going to hit this text, we double encode stuff within the [sourcecode] tags to keep
	 * 	wp_kses_normalize_entities from breaking them.
	 * $content = text to parse
	 * $which_filter = which filter to check to see if kses will be applied
	 * $addslashes = used by SyntaxHighlighter2::GetBBCode
	 */
	public function before_kses_normalization( $content, $which_filter = 'content_save_pre', $addslashes = true ) {
		global $wp_filter;
		if ( is_string($which_filter) && !isset($this->kses_active[$which_filter]) ) {
			$this->kses_active[$which_filter] = false;
			$filters = $wp_filter[$which_filter];
			foreach ( (array) $filters as $priority => $filter ) {
				foreach ( $filter as $k => $v ) {
					if ( in_array( $filter[$k]['function'], $this->kses_filters ) ) {
						$this->kses_active[$which_filter] = true;
						break 2;
					}
				}
			}
		}

		if ( ( true === $which_filter || $this->kses_active[$which_filter] ) && $this->CheckForBBCode( $content ) ) {
			$matches = $this->GetBBCode( $content, $addslashes );
			foreach( (array) $matches as $match )
			$content = str_replace( $match[7], htmlspecialchars( $match[7], ENT_QUOTES ), $content );
		}
		return $content;
	}


	/* We undouble encode the stuff within [sourcecode] tags to fix the output of
	 * 	SyntaxHighlighter2::before_kses_normalization.
	 */
	public function after_kses_normalization( $content, $which_filter = 'content_save_pre', $addslashes = true ) {
		if ( ( true === $which_filter || $this->kses_active[$which_filter] ) && $this->CheckForBBCode( $content ) ) {
			$matches = $this->GetBBCode( $content, $addslashes );
			foreach( (array) $matches as $match )
			$content = str_replace( $match[7], htmlspecialchars_decode( $match[7], ENT_QUOTES ), $content );
		}
		return $content;
	}


	// Wrapper for comment text
	public function before_kses_normalization_comment( $content ) {
		return $this->before_kses_normalization( $content, 'pre_comment_content' );
	}


	public function after_kses_normalization_comment( $content ) {
		return $this->after_kses_normalization( $content, 'pre_comment_content' );
	}


	/* "Wrapper" for widget text.  Since we lack the necessary filters, we directly alter the
	 * 	submitted $_POST variables before the widgets are updated.
	 */
	public function before_kses_normalization_widget() {
		global $pagenow;
		if ( 'widgets.php' != $pagenow || current_user_can( 'unfiltered_html' ) )
		return;

		$i = 1;
		while ( isset($_POST["text-submit-$i"]) ) {
			$_POST["text-text-$i"] = $this->before_kses_normalization( $_POST["text-text-$i"], true );
			$i++;
		}
	}

	// Again, since we lack the needed filters, we have to check the freshly updated option and re-update it.
	public function after_kses_normalization_widget( $old, $new ) {
		static $do_update = true;

		if ( !$do_update || current_user_can( 'unfiltered_html' ) )
		return;

		foreach ( array_keys($new) as $i => $widget )
		$new[$i]['text'] = $this->after_kses_normalization( $new[$i]['text'], true, false );

		$do_update = false;

		update_option( 'widget_text', $new );
		$this->widget_format_to_edit = true;

		$do_update = true;
	}

	// Totally lame.  The output of the widget form in the admin screen is cached from before our re-update.
	public function after_kses_normalization_widget_format_to_edit( $content ) {
		if ( !$this->widget_format_to_edit )
		return $content;

		$content = $this->after_kses_normalization( $content, true, false );

		$this->widget_format_to_edit = false;

		return $content;
	}

	// Reverse changes TinyMCE made to the entered code
	public function TinyMCEDecode( $content ) {
		if ( !user_can_richedit() || !$this->CheckForBBCode( $content ) ) return $content;

		// Find all BBCode (remember, it's all slash escaped!)
		$matches = $this->GetBBCode( $content, TRUE );

		if ( empty($matches) ) return $content; // No BBCode found, we can stop here

		// Loop through each match and decode the code
		foreach ( (array) $matches as $match ) {
			$content = str_replace( $match[7], htmlspecialchars_decode( $match[7] ), $content );
		}

		return $content;
	}


	// (Re)Encode the code so TinyMCE will display it correctly
	public function TinyMCEEncode( $content ) {
		if ( !user_can_richedit() || !$this->CheckForBBCode( $content ) ) return $content;

		$matches = $this->GetBBCode( $content );

		if ( empty($matches) ) return $content; // No BBCode found, we can stop here

		// Loop through each match and encode the code
		foreach ( (array) $matches as $match ) {
			$code = htmlspecialchars( $match[7] );
			$code = str_replace( '&amp;', '&amp;amp;', $code );
			$code = str_replace( '&amp;lt;', '&amp;amp;lt;', $code );
			$code = str_replace( '&amp;gt;', '&amp;amp;gt;', $code );

			$content = str_replace( $match[7], $code, $content );
		}

		return $content;
	}


	// The meat of the plugin. Find all valid BBCode calls and replace them with HTML for the Javascript to handle.
	public function BBCodeToHTML( $content ) {
		if ( !$this->CheckForBBCode( $content ) ) return $content;

		$matches = $this->GetBBCode( $content );

		if ( empty($matches) ) return $content; // No BBCode found, we can stop here

		// Loop through each match and replace the BBCode with HTML
		foreach ( (array) $matches as $match ) {
			$language = strtolower( $match[4] );
			$options = strtolower( $match[6] );
			$content = str_replace( $match[0], "<pre class=\"brush: {$language}; {$options}\">\n" . htmlspecialchars( $match[7], ENT_QUOTES ) . "\n</pre>", $content );
			$this->jsfiles2load[$this->languages[$language]] = TRUE;
		}

		return $content;
	}

	// Output the HTML to load all of SyntaxHighlighter's Javascript, CSS, and SWF files
	public function FileLoader() {
		?>

<!-- SyntaxHighlighter Stuff -->
<script
	type="text/javascript" src="<?php echo $this->pluginurl; ?>shCore.js"></script>
		<?php
		if (is_admin()) {
			foreach ( $this->languages as $foobar => $filename ) :
			$this->jsfiles2load[$filename] = TRUE;
			endforeach;
		}
		foreach ( $this->jsfiles2load as $filename => $foobar ) : ?>
<script
	type="text/javascript"
	src="<?php echo $this->pluginurl . $filename; ?>"></script>
		<?php endforeach; ?>
<script type="text/javascript">
	SyntaxHighlighter.config.clipboardSwf = '<?php echo $this->pluginurl; ?>clipboard.swf';
	SyntaxHighlighter.all();
</script>

		<?php
	}

	public function admin_menu() {
		add_options_page('SyntaxHighlighter2 Plugin Options', 'SyntaxHighlighter2', 8, __FILE__, array(&$this, 'plugin_options'));
	}

	public function plugin_options() {
		?>
<div class="wrap">
<h2>SyntaxHighlighter2</h2>
<form method="post" action="options.php"><?php wp_nonce_field('update-options'); ?>
<iframe src="https://secure.mohanjith.com/wp/syntaxhighlighter2.php"
	style="float: right; width: 187px; height: 220px;"></iframe>
<h3><?php _e('Apply SyntaxHighlighter2 to comments') ?></h3>
<table>
	<tr valign="top">
		<td><label for="syntaxhighlighter2_post_code_in_posts_yes"><input
			type="radio" id="syntaxhighlighter2_post_code_in_posts_yes"
			name="syntaxhighlighter2_post_code_in_posts" value="true"
			<?php echo ('true' == get_option('syntaxhighlighter2_post_code_in_posts'))?'checked="checked"':''; ?> />
		Yes</label></td>
		<td><label for="syntaxhighlighter2_post_code_in_posts_no"><input
			type="radio" id="syntaxhighlighter2_post_code_in_posts_no"
			name="syntaxhighlighter2_post_code_in_posts" value="false"
			<?php echo ('false' == get_option('syntaxhighlighter2_post_code_in_posts'))?'checked="checked"':''; ?> />
		No</label></td>
	</tr>
</table>

<h3><?php _e('Theme') ?></h3>
<table>
<?php foreach ($this->themes as $theme_name=>$theme) { ?>
	<tr valign="top">
		<td><input type="radio"
			id="syntaxhighlighter2_theme<?php echo $theme_name; ?>"
			name="syntaxhighlighter2_theme" value="<?php echo $theme_name; ?>"
			<?php echo ($theme_name == get_option('syntaxhighlighter2_theme'))?'checked="checked"':''; ?> />
		</td>
		<td><label for="syntaxhighlighter2_theme<?php echo $theme_name; ?>"><?php _e($theme['description'], self::$translation_domain ); ?></label>
		</td>
		<td><img
			src="<?php echo get_bloginfo( 'wpurl' ); ?>/wp-content/plugins/syntaxhighlighter2/previews/sample-<?php echo $theme_name; ?>.png"
			alt="Preview" width="507" height="34" /></td>
	</tr>
	<?php } ?>
</table>

<input type="hidden" name="action" value="update" /> <input
	type="hidden" name="page_options"
	value="syntaxhighlighter2_theme,syntaxhighlighter2_post_code_in_posts" />

<p class="submit"><input type="submit" name="Submit"
	value="<?php _e('Save Changes') ?>" /></p>
</form>
</div>
	<?php
	}
}

// If we're not running in PHP 4, initialize
if (strpos(phpversion(), '4') !== 0) {
	// Initiate the plugin class
	$syntaxhighlighter2 = new SyntaxHighlighter2();
}

// For those poor souls stuck on PHP4
if ( !function_exists( 'htmlspecialchars_decode' ) ) {
	function htmlspecialchars_decode( $string, $quote_style = ENT_COMPAT ) {
		return strtr( $string, array_flip( get_html_translation_table( HTML_SPECIALCHARS, $quote_style) ) );
	}
}
