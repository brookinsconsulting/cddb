<?php

$Module = $Params['Module'];
$genre = $Params['Genre'];
$discID = $Params['DiscID'];

if (!isset( $genre ) or !isset( $discID ) )
{
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
}

include_once( 'kernel/common/template.php' );
$tpl =& templateInit();

require_once( 'Net/CDDB.php' );

$cddbClient = new Net_CDDB();

$disc = $cddbClient->getDetailsByDiscId( $genre, $discID );

if ( !$disc )
{
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
}

include_once( 'extension/cddb/classes/ezcddbdisc.php' );
$eZDisc = new eZCDDBDisc( $disc );

$cddbClient->disconnect();

$tpl->setVariable( 'disc', $eZDisc );
$tpl->setVariable( 'genre', $genre );

$Result = array();
$Result['content'] = & $tpl->fetch( 'design:cddb/discinfo.tpl' );
$Result['left_menu'] = 'design:parts/cddb/menu.tpl';
$Result['path'] = array(
    array( 'text' => ezi18n( 'extension/cddb', 'CDDB' ),
           'url' => '/cddb/search' ),
    array( 'text' => ezi18n( 'extension/cddb', 'Disc info' ),
           'url' => false )
    );

?>