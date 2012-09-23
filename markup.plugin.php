<?php

class MarkUp extends Plugin {

	/**
	 * Set options to defaults
	 */
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			$opts = array( 
						'Markup__markup_type' => 'html',
						'Markup__skin' => 'simple',
						'Markup__process_comments' => FALSE,
						'Markup__show_comments' => FALSE,
						'Markup__comment_markup_type' => 'html',
						'Markup__comment_skin' => 'simple' );
			foreach ( $opts as $opt => $value ) {
				$cur = Options::get( $opt );
				if ( empty( $cur ) ) {
					Options::set( $opt, $value );
				}
			}

		}
	}

	public function action_init()
	{
		$this->load_text_domain( 'markup' );
		spl_autoload_register( array( __CLASS__, '_autoload' ) );
	}

	public static function _autoload( $class )
	{
		if( strtolower( $class ) == 'textile' ) {
			require( 'markitup/parsers/textile/classTextile.php' );
		}
		elseif( strtolower( $class ) == 'markdown_parser' ) {
			require( 'markitup/parsers/markdown/markdown.php' );
		}
		elseif( strtolower( $class ) == 'bbcode' ) {
		    require( 'markitup/parsers/bbcode/bbcode.php' );
		}
	}

	public function configure()
	{
		$types = array(
			'html' => _t( 'Html', 'markup' ),
			'markdown' => _t( 'Markdown', 'markup' ),
			'textile' => _t( 'Textile', 'markup' ),
			'bbcode' => _t( 'BBCode', 'markup' ),
		);

		$skins = array(
			'simple' => _t( 'Simple', 'markup' ),
			'markitup' => _t( 'MarkItUp', 'markup' ),
		);

		$form = new FormUI( 'markup' );

		$form->append( 'fieldset', 'editor', _t( 'Post Editor', 'markup' ) );
		$form->editor->append( 'select', 'markup_type', 'Markup__markup_type', _t( 'Markup Type ', 'markup' ) );
		$form->editor->markup_type->options = $types;

		$form->editor->append( 'select', 'skin', 'Markup__skin', _t( 'Editor Skin&nbsp;&nbsp;&nbsp; ', 'markup' ) );
		$form->editor->skin->options = $skins;

		$form->append( 'fieldset', 'comment', _t( 'Comment Form', 'markup' ) );

		$form->comment->append( 'label', 'process_label', _t( 'Process markup in comments?', 'markup' ) );
		$form->comment->append( 'radio', 'process_comments', 'Markup__process_comments', _t( 'Process markup in comments?', 'markup' ), array( TRUE => _t( 'Yes' ), FALSE => _t( 'No' ) ) );

		$form->comment->append( 'label', 'show_label', _t( 'Show markup toolbar on comment form?', 'markup' ) );
		$form->comment->append( 'radio', 'show_comments', 'Markup__show_comments', _t( 'Show markup toolbar on comment form?', 'markup' ), array( TRUE => _t( 'Yes' ), FALSE => _t( 'No' ) ) );

		$form->comment->append( 'select', 'comment_markup_type', 'Markup__comment_markup_type', _t( 'Markup Type ', 'markup' ) );
		$form->comment->comment_markup_type->options = $types;

		$form->comment->append( 'select', 'comment_skin', 'Markup__comment_skin', _t( 'Editor Skin&nbsp;&nbsp;&nbsp; ', 'markup' ) );
		$form->comment->comment_skin->options = $skins;

		$form->append( 'submit', 'save', _t( 'Save', 'markup' ) );

		$form->on_success( array( $this, 'config_success' ) );
		$form->out();
	}

	public static function config_success( $ui )
	{
		$ui->save();
		Session::notice( _t( 'Markup settings updated.', 'markup' ) );
	}

	public function action_admin_header( $theme )
	{
		if ( $theme->page == 'publish' ) {
			$set = Options::get( 'Markup__markup_type' );

			switch( $set ) {
				case 'markdown':
					$dir = 'markdown';
					break;
				case 'textile':
					$dir = 'textile';
					break;
				case 'bbcode':
					$dir = 'bbcode';
					break;
				case 'html':
				default:
					$dir = 'html';
			}

			$skin = Options::get( 'Markup__skin' );

			Stack::add( 'admin_header_javascript', $this->get_url() . '/markitup/jquery.markitup.js', 'markitup', 'jquery' );
			Stack::add( 'admin_header_javascript', $this->get_url() . '/markitup/sets/' . $dir . '/set.js', 'markitup_set', 'jquery' );

			Stack::add( 'admin_stylesheet', array( $this->get_url() . '/markitup/skins/' . $skin . '/style.css', 'screen' ) );
			Stack::add( 'admin_stylesheet', array( $this->get_url() . '/markitup/sets/' . $dir . '/style.css', 'screen' ) );
		}
	}

	public function theme_header( $theme )
	{
		if ( Options::get( 'Markup__show_comments' ) ) {
			$set = Options::get( 'Markup__comment_markup_type' );

			switch( $set ) {
				case 'markdown':
					$dir = 'markdown';
					break;
				case 'textile':
					$dir = 'textile';
					break;
				case 'bbcode':
					$dir = 'bbcode';
					break;
				case 'html':
				default:
					$dir = 'html';
			}

			$skin = Options::get( 'Markup__comment_skin' );

			Stack::add( 'template_stylesheet', array( $this->get_url() . '/markitup/skins/' . $skin . '/style.css', 'screen' ) );
			Stack::add( 'template_stylesheet', array( $this->get_url() . '/markitup/sets/' . $dir . '/style.css', 'screen' ) );
		}
	}

	public function alias()
	{
		return array(
		  'do_markup' => array( 'filter_post_content_out_7', 'filter_post_content_excerpt_7', 'filter_post_content_summary_7', 'filter_post_content_atom_7', 'filter_post_title_atom_7', 'filter_comment_content_out_7', 'filter_comment_content_atom_7', 'filter_atom_add_comment_7' /* Remove this last one when Ticket #1245 is resolved (issue #115 on github ) */ )
		);
	}

	public function do_markup( $content, $post = null )
	{
		static $textile;
		static $markdown;
		static $bbcode;
		$markup = 'html';

		$process_comments = Options::get( 'Markup__process_comments' );

		// Posts are Post objects and comments are comment objects.
		if ( $post instanceof Comment && $process_comments ) {
			$markup = Options::get( 'Markup__comment_markup_type' );
		}
		else if ( $post instanceof Post ) {
			$markup = Options::get( 'Markup__markup_type' );
		}
		
		switch( $markup ) {
			case 'markdown':
				if( !isset( $markdown ) ) {
					$markdown = new Markdown_Parser;
				}
				return $markdown->transform( $content );
				break;
			case 'textile':
				if( !isset( $textile) ) {
					$textile = new Textile();
				}
				return $textile->TextileThis( $content );
				break;
			case 'bbcode':
				if( !isset( $bbcode ) ) {
				    $bbcode = new BBCode();
				}
				return $bbcode->transform( $content );
				break;
			case 'html':
			default:
				return $content;
		}
	}

	public function action_admin_footer( $theme )
	{
		if ( $theme->page == 'publish' ) {
			$skin = Options::get( 'Markup__skin' );
			$set = ( ( 'markitup' == $skin ) ? Options::get( 'Markup__markup_type' ) : '' );
			$path = $this->get_url();
			$markup = <<<MARKITUP
$(document).ready(function() {
	mySettings.nameSpace = '$set';
	mySettings.resizeHandle= false;
	mySettings.markupSet.push({separator:'---------------' });
	mySettings.markupSet.push({
		name: 'Full Screen',
		className: 'fullScreen',
		key: "F",
		call: function(){
			if ($('.markItUp').hasClass('fullScreen')) {
				$('.markItUp').removeClass('fullScreen');
				$('textarea#content').css(
					'height',
					markItUpTextareaOGHeight + "px"
				);
			}
			else {
				markItUpTextareaOGHeight = \$('textarea#content').innerHeight();
				$('.markItUp').addClass('fullScreen');
				$('.markItUp.fullScreen textarea#content').css(
					'height',
					($('.markItUp.fullScreen').innerHeight() - 90) + "px"
				);
			}
		}
	});

	$("#content").markItUp(mySettings);
	$('label[for=content].overcontent').attr('style', 'margin-top:30px;margin-left:5px;');
	$('#content').focus(function(){
		$('label[for=content]').removeAttr('style');
	}).blur(function(){
		if ($('#content').val() == '') {
			$('label[for=content]').attr('style', 'margin-top:30px;margin-left:5px;');
		} else {
			$('label[for=content]').removeAttr('style');
		}
	});
});
MARKITUP;
			Stack::add( 'admin_footer_javascript', $markup, 'markup_footer', 'jquery' );

			echo <<<STYLE
<style type="text/css">
	.markItUp.fullScreen {
		position: absolute;
		top: 0;
		left: 0;
		height: 100%;
		width: 100%;
		z-index: 9999;
		margin: 0;
		background: #f0f0f0;
		}
	.markItUp.fullScreen .markItUpContainer{
		padding: 20px 40px 40px;
		}
	.markItUp li.fullScreen {
		background: transparent url($path/fullscreen.png) no-repeat;
		}
	.markItUp.fullScreen li.fullScreen {
		background: transparent url($path/normalscreen.png) no-repeat;
		}
</style>
STYLE;
		}
	}

	public function theme_footer( $theme )
	{
		if ( Options::get( 'Markup__show_comments' ) ) {
			$set = Options::get( 'Markup__comment_markup_type' );

			switch( $set ) {
				case 'markdown':
					$dir = 'markdown';
					break;
				case 'textile':
					$dir = 'textile';
					break;
				case 'bbcode':
					$dir = 'bbcode';
					break;
				case 'html':
				default:
					$dir = 'html';
			}

			// We put this in the footer as we don't want to slow the whole site down unnecessarily.
			Stack::add( 'template_footer_javascript', Site::get_url( 'scripts' ) . '/jquery.js', 'jquery' );
			Stack::add( 'template_footer_javascript', $this->get_url() . '/markitup/jquery.markitup.js', 'markitup', 'jquery' );
			Stack::add( 'template_footer_javascript', $this->get_url() . '/markitup/sets/' . $dir . '/set.js', 'markitup_set', 'jquery' );

			$skin = Options::get( 'Markup__comment_skin' );
			$path = $this->get_url();
			// This is the same javascript as used in action_admin_footer, just modified to select the FormUI comment form textarea, without the fullscreen button and optimised for improved performance
			$markup = <<<MARKITUP
$(document).ready(function(){mySettings.nameSpace='$set';mySettings.resizeHandle=false;$("#comment_content").markItUp(mySettings);$("label[for=comment_content].overcontent").attr("style","margin-top:30px;margin-left:5px;");$("#comment_content").focus(function(){\$("label[for=comment_content]").removeAttr("style")}).blur(function(){if($("#comment_content").val()==""){\$("label[for=comment_content]").attr("style","margin-top:30px;margin-left:5px;")}else{\$("label[for=comment_content]").removeAttr("style")}})});
MARKITUP;
			Stack::add( 'template_footer_javascript', $markup, 'markup_footer', 'jquery' );
		}
	}
}

?>
