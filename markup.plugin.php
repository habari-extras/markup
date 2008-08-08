<?php

class MarkUp extends Plugin {

  /**
    * Required Plugin Information
    **/
  function info() {
    return array(
      'name' => 'markUp',
      'license' => 'Apache License 2.0',
      'url' => 'http://habariproject.org/',
      'author' => 'Habari Community',
      'authorurl' => 'http://habariproject.org/',
      'version' => '0.21',
      'description' => 'Adds easy tag insertion to Habari\'s editor',
      'copyright' => '2008'
    );
  }


	public function action_admin_header($theme)
	{
		if ( $theme->page == 'publish' ) {
			Stack::add( 'admin_header_javascript', $this->get_url() . '/markitup/jquery.markitup.pack.js' );
			Stack::add( 'admin_header_javascript', $this->get_url() . '/markitup/sets/html/set.js' );
			
			Stack::add('admin_stylesheet', array($this->get_url() . '/markitup/skins/markitup/style.css', 'screen'));
			Stack::add('admin_stylesheet', array($this->get_url() . '/markitup/sets/html/style.css', 'screen'));
		}
	}

	public function action_admin_footer($theme) {
		if ( $theme->page == 'publish' ) {
			echo <<<MARKITUP
<script type="text/javascript">
$(document).ready(function() {
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