<?php

class MarkUp extends Plugin {

	/**
	 * Set options to defaults
	 */
	public function action_plugin_activation( $file )
	{
		if ( realpath( $file ) == __FILE__ ) {
			$type = Options::get( 'Markup__markup_type' );
			if ( empty( $type ) ) {
				Options::set( 'Markup__markup_type', 'html' );
			}
			$skin = Options::get( 'Markup__skin' );
			if( empty( $skin ) ) {
				Options::set( 'Markup__skin', 'simple' );
			}
		}
	}

	public function action_init()
	{
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
	}

	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = _t( 'Configure' );
		}
		return $actions;
	}

	public function action_plugin_ui( $plugin_id, $action )
	{
		$types = array(
			'html' => 'html',
			'markdown' => 'markdown',
			'textile' => 'textile',
		);

		$skins = array(
			'simple' => 'Simple',
			'markitup' => 'MarkItUp',
		);

		if ( $plugin_id == $this->plugin_id() ) {
			switch ( $action ) {
				case _t( 'Configure' ):
					$form = new FormUI( 'markup' );

					$form->append( 'select', 'markup_type', 'Markup__markup_type', _t( 'Markup Type ' ) );
					$form->markup_type->options = $types;

					$form->append( 'select', 'skin', 'Markup__skin', _t( 'Editor Skin&nbsp;&nbsp;&nbsp; ' ) );
					$form->skin->options = $skins;

					$form->append( 'submit', 'save', _t( 'Save' ) );

					$form->on_success( array( $this, 'config_success' ) );
					$form->out();
				break;
			}
		}
	}

	public static function config_success( $ui )
	{
		$ui->save();
		Session::notice( 'Markup settings updated.' );
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
				case 'html':
				default:
					$dir = 'html';
			}

			$skin = Options::get( 'Markup__skin' );

			Stack::add( 'admin_header_javascript', $this->get_url() . '/markitup/jquery.markitup.pack.js', 'markitup', 'jquery' );
			Stack::add( 'admin_header_javascript', $this->get_url() . '/markitup/sets/' . $dir . '/set.js', 'markitup_set', 'jquery' );

			Stack::add( 'admin_stylesheet', array( $this->get_url() . '/markitup/skins/' . $skin . '/style.css', 'screen' ) );
			Stack::add( 'admin_stylesheet', array( $this->get_url() . '/markitup/sets/' . $dir . '/style.css', 'screen' ) );
		}
	}

	public function alias()
	{
		return array(
			'do_markup' => array( 'post_content_out', 'post_content_excerpt', 'post_content_summary' )
		);
	}

	public function do_markup( $content, $post )
	{
		static $textile;
		static $markdown;
		$markup = Options::get( 'Markup__markup_type' );

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
			case 'html':
			default:
				return $content;
		}
	}

	public function action_admin_footer($theme) {
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
			if (\$('.markItUp').hasClass('fullScreen')) {
				\$('.markItUp').removeClass('fullScreen');
				\$('textarea#content').css(
					'height',
					markItUpTextareaOGHeight + "px"
				);
			}
			else {
				markItUpTextareaOGHeight = \$('textarea#content').innerHeight();
				\$('.markItUp').addClass('fullScreen');
				\$('.markItUp.fullScreen textarea#content').css(
					'height',
					(\$('.markItUp.fullScreen').innerHeight() - 90) + "px"
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

  public function action_update_check() {
    Update::add( 'markUp', 'F695D390-2687-11DD-B5E1-2D6F55D89593',  $this->info->version );
  }
}

?>
