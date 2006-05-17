<?php

$Module =& $Params['Module'];
$category = $Params['Category'];
$discID = $Params['DiscID'];

if ( $Module->currentAction() == 'import' )
{
    $Module->redirectToView( 'import', array( $category, $discID ) );
    return;
}

if (!isset( $category ) or !isset( $discID ) )
{
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
}

include_once( 'kernel/common/template.php' );
$tpl =& templateInit();

include_once( 'lib/ezutils/classes/ezini.php' );
$ini =& eZINI::instance( 'cddb.ini' );

$debug = false;

if ( trim( $ini->variable( 'DebugSettings', 'Debug' ) ) == 'enabled' )
{
    $debug = true;
}

include_once( 'lib/ezutils/classes/ezsys.php' );
$sys =& eZSys::instance();

$cacheDir = $sys->cacheDirectory();
$sep = $sys->fileSeparator();

$disc = false;

$categoryDir = $cacheDir . $sep . 'cddb' . $sep . $category;

include_once( 'lib/ezfile/classes/ezfile.php' );

$cachePath = $categoryDir . $sep . $discID;

if ( file_exists( $cachePath ) )
{
    $cddbEntry = eZFile::getContents( $cachePath );
    
    if ( $cddbEntry )
    {
        require_once( 'Net/CDDB/Utilities.php' );
        $record = Net_CDDB_Utilities::parseRecord( $cddbEntry );
        
        require_once( 'Net/CDDB/Disc.php' );
        $disc = new Net_CDDB_Disc($record['DISCID'], $record['ARTIST'], $record['TITLE'], $category, $record['GENRE'], $record['YEAR'], $record['TRACKS'], $record['LENGTH'], $record['REVISION'], $record['PLAYORDER']);

        include_once( 'lib/ezi18n/classes/eztextcodec.php' );        
        $cddbCharset = eZTextCodec::internalCharset();
    }
}

if ( !$disc )
{
    require_once( 'Net/CDDB/Client.php' );

    $cddbClient = new Net_CDDB_Client();

    if ( $debug )
    {
        $cddbClient->debug( true );
        eZDebug::writeDebug( 'CDDB server version: ' . $cddbClient->version(), 'cddb/discinfo' );
    } 

    $stats = $cddbClient->statistics();
    if ( array_key_exists( 'current_proto', $stats ) && intval( $stats['current_proto'] ) > 5 )
    {
        $cddbCharset = 'utf-8';
    }
    else
    {
        $cddbCharset = 'iso-8859-1';
    }

    if ( $debug )
    {
        eZDebug::writeDebug( $stats, 'cddb/discinfo' );
    }

    $disc = $cddbClient->getDetailsByDiscId( $category, $discID );
    $cddbClient->disconnect();

    include_once( 'lib/ezi18n/classes/eztextcodec.php' );
    $charset = eZTextCodec::internalCharset();

    include_once( 'lib/ezi18n/classes/ezcharsetinfo.php' );
    $charset = eZCharsetInfo::realCharsetCode( $charset );
    
    include_once( 'lib/ezi18n/classes/ezcharsetinfo.php' );
    $cddbCharset = eZCharsetInfo::realCharsetCode( $cddbCharset );

    include_once( 'lib/ezi18n/classes/eztextcodec.php' );
    $codec =& eZTextCodec::instance( $cddbCharset, $charset, false );
    
    $success = eZFile::create( $discID, $categoryDir, $codec->convertString( $disc->toString() ) );
}

if ( !$disc )
{
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
}

include_once( 'extension/cddb/classes/ezcddbdisc.php' );
$eZDisc = new eZCDDBDisc( $disc, $cddbCharset );

$tpl->setVariable( 'disc', $eZDisc );

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