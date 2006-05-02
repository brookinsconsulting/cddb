<?php

$Module = $Params['Module'];

include_once( 'kernel/common/template.php' );
$tpl =& templateInit();

require_once( 'Net/CDDB.php' );

$cddbClient = new Net_CDDB();

//$cddbClient->disconnect();

$Result = array();
$Result['content'] = & $tpl->fetch( 'design:cddb/submit.tpl' );
$Result['left_menu'] = 'design:parts/cddb/menu.tpl';
$Result['path'] = array(
    array( 'text' => ezi18n( 'extension/cddb', 'CDDB' ),
           'url' => '/cddb/search' ),
    array( 'text' => ezi18n( 'extension/cddb', 'Submit' ),
           'url' => false )
    );

?>