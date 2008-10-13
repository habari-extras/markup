<?php

include_once( 'markitup/parsers/markdown/markdown.php' );

class MarkUp extends Plugin {

	/**
	* Required Plugin Information
	**/
	public function info() {
		return array(
		'name' => 'markUp',
		'license' => 'Apache License 2.0',
		'url' => 'http://habariproject.org/',
		'author' => 'Habari Community',
		'authorurl' => 'http://habariproject.org/',
		'version' => '0.3',
		'description' => 'Adds easy html or markdown tag insertion to Habari\'s editor',
		'copyright' => '2008'
		);
	}

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
				case 'html':
				default:
					$dir = 'html';
			}

			$skin = Options::get( 'Markup__skin' );

			Stack::add( 'admin_header_javascript', $this->get_url() . '/markitup/jquery.markitup.pack.js' );
			Stack::add( 'admin_header_javascript', $this->get_url() . '/markitup/sets/' . $dir . '/set.js' );

			Stack::add( 'admin_stylesheet', array( $this->get_url() . '/markitup/skins/' . $skin . '/style.css', 'screen' ) );
			Stack::add( 'admin_stylesheet', array( $this->get_url() . '/markitup/sets/' . $dir . '/style.css', 'screen' ) );
		}
	}


	public static function filter_post_content_out( $content, $post )
	{
		$markup = Options::get( 'Markup__markup_type' );

		switch( $markup ) {
			case 'markdown':
				return Markdown( $content );
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
			echo <<<MARKITUP
<script type="text/javascript">
$(document).ready(function() {
	mySettings.nameSpace = '$set';
	mySettings.resizeHandle= false;
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
</script>
MARKITUP;
    }
  }
	
  public function action_update_check() {
    Update::add( 'markUp', 'F695D390-2687-11DD-B5E1-2D6F55D89593',  $this->info->version );
  }
}
  
?>