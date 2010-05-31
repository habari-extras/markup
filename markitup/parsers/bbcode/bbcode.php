<?php
/**
 * A wrapper around the BBCode parser by Jay Salvat
 *
 */

require_once( 'markitup.bbcode-parser.php' );

class BBCode {

    public function transform( $text )
    {
        return BBCode2Html( $text );
    }
}

?>
