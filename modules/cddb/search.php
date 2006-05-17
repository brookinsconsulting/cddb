<?php

include_once( 'kernel/common/template.php' );

$module = $Params['Module'];
$tpl =& templateInit();

require_once( 'Net/CDDB/client.php' );

$cddbClient = new Net_CDDB_Client();

if ( $module->currentAction() == 'search' &&
     $module->hasActionParameter( 'offsets' ) && $module->actionParameter( 'offsets' ) != '' &&
     $module->hasActionParameter( 'length' ) && $module->actionParameter( 'length' ) != '' )
{
    $trackOffsets = explode( ' ', $module->actionParameter( 'offsets' ) );
    $tpl->setVariable( 'offsets', $module->actionParameter( 'offsets' ) );

    $length = $module->actionParameter( 'length' );
    $tpl->setVariable( 'length', $length );
    
    $id = $cddbClient->calculateDiscId( $trackOffsets, $length );
    eZDebug::writeDebug( 'disc id: ' . $id );
    
    $discs = $cddbClient->searchDatabase( $trackOffsets, $length  );
    
    $results = array();
}
else if ( $module->currentAction() == 'rawsearch' &&
         $module->hasActionParameter( 'query' ) && $module->actionParameter( 'query' ) != '' )
{
    $tpl->setVariable( 'query', $module->actionParameter( 'query' ) );
    $discs = $cddbClient->searchDatabaseWithRawQuery( $module->actionParameter( 'query' ) );
}

if ( isset( $discs ) && is_array( $discs ) )
{
    include_once( 'extension/cddb/classes/ezcddbdisc.php' );
    foreach ( $discs as $disc )
    {
        $results[] = new eZCDDBDisc( $disc );
    }    

    $tpl->setVariable( 'discs', $results );
}

$cddbClient->disconnect();

$Result = array();
$Result['content'] = $tpl->fetch( 'design:cddb/search.tpl' );
$Result['left_menu'] = 'design:parts/cddb/menu.tpl';
$Result['path'] = array(
    array( 'text' => ezi18n( 'extension/cddb', 'CDDB' ),
           'url' => '/cddb/search' ),
    array( 'text' => ezi18n( 'extension/cddb', 'Search' ),
           'url' => false )
    );
?>
