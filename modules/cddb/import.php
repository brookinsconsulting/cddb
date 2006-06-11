<?php

$Module =& $Params['Module'];

$category = $Params['Category'];
$discID = $Params['DiscID'];

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
        $disc = new Net_CDDB_Disc( $record );

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
    
    if ( !$disc )
    {
        return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );
    }

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

$uniqueArtists = array();
$artistMappings = array();

$i = 0;
include_once( 'lib/ezutils/classes/ezhttptool.php' );
$http =& eZHTTPTool::instance();
foreach( $eZDisc->attribute( 'tracks' ) as $track )
{
    $uniqueArtist = trim( $track['artist'] );
    if ( !in_array( $uniqueArtist, $uniqueArtists ) )
    {
        $uniqueArtists[$i] = $uniqueArtist;

        if ( $http->hasPostVariable( 'artist_mapping_' . $i  ) )
        {
            $artistMappings[$i] = $http->postVariable( 'artist_mapping_' . $i );
        }
        else
        {
            $artistMappings[$i] = 0;
        }

        $i++;
    }
}

sort( $uniqueArtists );

if ( $Module->currentAction() == 'import' )
{
    eZDebug::writeDebug( 'start importing...' );
    
    // create artists if necessary
    foreach( $uniqueArtists as $key => $uniqueArtist )
    {
        $prefix = 'create_new_';
        if ( substr( $artistMappings[$key], 0, strlen( $prefix ) ) == $prefix )
        {
            $contentClassIdentifier = substr( $artistMappings[$key], strlen( $prefix ) );
            eZDebug::writeDebug( 'creating a new ' . $contentClassIdentifier );
            
            $class = eZContentClass::fetchByIdentifier( $contentClassIdentifier );

            if ( is_object( $class ) )
            {
                $contentClassID = $class->attribute( 'id' );
                $node =& eZContentObjectTreeNode::fetch( $ini->variable( 'ContentSettings', 'ArtistsNodeID' ) );
                $parentContentObject =& $node->attribute( 'object' );
            
                $accessResult = $parentContentObject->checkAccess( 'create', $contentClassID, $parentContentObject->attribute( 'contentclass_id' ) ); 
            
                if ( $accessResult == '1' )
                {
                    include_once( 'kernel/classes/datatypes/ezuser/ezuser.php' );
                    $user =& eZUser::currentUser();
                    $userID =& $user->attribute( 'contentobject_id' );
                    $sectionID = $parentContentObject->attribute( 'section_id' );
            
                    include_once( 'lib/ezdb/classes/ezdb.php' );
                    $db =& eZDB::instance();
                    $db->begin();
            
                    $contentObject =& $class->instantiate( $userID, $sectionID );
                    $nodeAssignment = eZNodeAssignment::create( array( 'contentobject_id' => $contentObject->attribute( 'id' ),
                                                                       'contentobject_version' => $contentObject->attribute( 'current_version' ),
                                                                       'parent_node' => $node->attribute( 'node_id' ),
                                                                       'is_main' => 1 ) );
            
                    $nodeAssignment->store();
                    
                    $version = $contentObject->currentVersion();
        
                    $attribs =& $version->contentObjectAttributes();
                    $attribsCount = count( $attribs );
        
                    for ( $j = 0; $j < $attribsCount; $j++ )
                    {
                        $identifier = $attribs[$j]->attribute( 'contentclass_attribute_identifier' );
                
                        switch ( $identifier )
                        {
                            case 'name':
                                {
                                    $attribs[$j]->setAttribute( 'data_text', $uniqueArtist );
                                }break;
 
                            default:
                                {
                                    // do nothing
                                }
                        }
                
                        $attribs[$j]->store();
                    }
                    
                    include_once( 'lib/ezutils/classes/ezoperationhandler.php' );
        
                    $operationParams = array();
                    $operationParams['object_id']   = $contentObject->attribute( 'id' );
                    $operationParams['version']     = $contentObject->attribute( 'current_version' );
        
                    $operationResult = eZOperationHandler::execute( 'content', 'publish', $operationParams );

                    $db->commit();
        
                    // when preview cache is on, the user is restored but the policy limitations are still wrongly cached
                    // see http://ez.no/community/bugs/cache_for_content_read_limitation_list_isn_t_cleared_after_switching_users
                    // this is a temporary workaround, until the kernel has been fixed
                    if ( isset( $GLOBALS['ezpolicylimitation_list']['content']['read'] ) )
                    {
                        unset( $GLOBALS['ezpolicylimitation_list']['content']['read'] );
                    }
                    
                    $artistMappings[$key] = $contentObject->attribute( 'id' );
                }
            }
            else
            {
                eZDebug::writeError( 'invalid class for artist' );
                return;
            }
        }
    }
    
    // create disc
    $contentClassID = $ini->variable( 'ContentSettings', 'DiscClassID' );
    $class = eZContentClass::fetch( $contentClassID );
    
    $node =& eZContentObjectTreeNode::fetch( $ini->variable( 'ContentSettings', 'DiscsNodeID' ) );
    
    if ( is_object( $class ) )
    {
        $parentContentObject =& $node->attribute( 'object' );
    
        $accessResult = $parentContentObject->checkAccess( 'create', $contentClassID, $parentContentObject->attribute( 'contentclass_id' ) ); 
    
        if ( $accessResult == '1' )
        {
            include_once( 'kernel/classes/datatypes/ezuser/ezuser.php' );
            $user =& eZUser::currentUser();
            $userID =& $user->attribute( 'contentobject_id' );
            $sectionID = $parentContentObject->attribute( 'section_id' );
    
            include_once( 'lib/ezdb/classes/ezdb.php' );
            $db =& eZDB::instance();
            $db->begin();
    
            $contentObject =& $class->instantiate( $userID, $sectionID );
            $nodeAssignment = eZNodeAssignment::create( array( 'contentobject_id' => $contentObject->attribute( 'id' ),
                                                               'contentobject_version' => $contentObject->attribute( 'current_version' ),
                                                               'parent_node' => $node->attribute( 'node_id' ),
                                                               'is_main' => 1,
                                                               'sort_field' => 8,
                                                               'sort_order' => 1 ) );
    
            $nodeAssignment->store();
            
            $version = $contentObject->currentVersion();
    
            $attribs =& $version->contentObjectAttributes();
            $attribsCount = count( $attribs );
    
            for ( $j = 0; $j < $attribsCount; $j++ )
            {
                $identifier = $attribs[$j]->attribute( 'contentclass_attribute_identifier' );
        
                switch ( $identifier )
                {
                    case 'title':
                    case 'genre':
                    case 'revision':
                        {
                            $attribs[$j]->setAttribute( 'data_text', $eZDisc->attribute( $identifier ) );
                        }break;

                    case 'year':
                    case 'length':
                        {
                            $attribs[$j]->setAttribute( 'data_int', $eZDisc->attribute( $identifier ) );
                        }break;
                    case 'id':
                        {
                            $attribs[$j]->setAttribute( 'data_text', $eZDisc->attribute( 'discid' ) );
                        } break;
                    case 'tracks':
                        {
                            $attribs[$j]->setAttribute( 'data_int', $eZDisc->attribute( 'num_tracks' ) );
                        } break;
                    case 'artist':
                        {
                            $discArtist = trim( $eZDisc->attribute( 'artist' ) );
                            foreach ( array_keys( $uniqueArtists ) as $uniqueArtistKey )
                            {
                                if ( strtolower( $uniqueArtists[$uniqueArtistKey] ) == strtolower( $discArtist ) )
                                {
                                    if ( is_numeric( $artistMappings[$uniqueArtistKey] ) && $artistMappings[$uniqueArtistKey] != 0 )
                                    {
                                        $attribs[$j]->setAttribute( 'data_int', $artistMappings[$uniqueArtistKey] );
                                    }

                                    break 2;
                                }
                            }
                            
                            // no artist found, so it's a various artists compilation
                            $attribs[$j]->setAttribute( 'data_int', $ini->variable( 'ContentSettings','VariousArtistsObjectID' ) ); 
                        } break;
                    case 'cddb_genre':
                        {
                            $classContent =& $attribs[$j]->classContent();
                            $options = $classContent['options'];

                            foreach ( $options as $option )
                            {
                                if ( $option['name'] == $category )
                                {
                                    $attribs[$j]->setAttribute( 'data_text', $option['id'] );
                                    break;
                                }
                            }
                        } break;
                    default:
                        {
                            // do nothing
                        }
                }
        
                $attribs[$j]->store();
            }
            
            include_once( 'lib/ezutils/classes/ezoperationhandler.php' );

            $operationParams = array();
            $operationParams['object_id']   = $contentObject->attribute( 'id' );
            $operationParams['version']     = $contentObject->attribute( 'current_version' );

            $operationResult = eZOperationHandler::execute( 'content', 'publish', $operationParams );

            $db->commit();

            // when preview cache is on, the user is restored but the policy limitations are still wrongly cached
            // see http://ez.no/community/bugs/cache_for_content_read_limitation_list_isn_t_cleared_after_switching_users
            // this is a temporary workaround, until the kernel has been fixed
            if ( isset( $GLOBALS['ezpolicylimitation_list']['content']['read'] ) )
            {
                unset( $GLOBALS['ezpolicylimitation_list']['content']['read'] );
            }
            
            $discsNodeID = $node->attribute( 'node_id' );
            
            $node =& eZContentObjectTreeNode::fetchNode( $contentObject->attribute( 'id' ), $discsNodeID );
        }
    }
    else
    {
        eZDebug::writeError( 'invalid class for disc' );
        return;
    }
    
    if ( $node )
    { 
        // create tracks
        $contentClassID = $ini->variable( 'ContentSettings', 'TrackClassID' );
        $class = eZContentClass::fetch( $contentClassID );
    
        if ( is_object( $class ) )
        {
            eZDebug::writeDebug( $uniqueArtists );
            foreach ( $eZDisc->attribute( 'tracks' ) as $number => $track )
            {
                $parentContentObject =& $node->attribute( 'object' );
            
                $accessResult = $parentContentObject->checkAccess( 'create', $contentClassID, $parentContentObject->attribute( 'contentclass_id' ) ); 
            
                if ( $accessResult == '1' )
                {
                    include_once( 'kernel/classes/datatypes/ezuser/ezuser.php' );
                    $user =& eZUser::currentUser();
                    $userID =& $user->attribute( 'contentobject_id' );
                    $sectionID = $parentContentObject->attribute( 'section_id' );
            
                    include_once( 'lib/ezdb/classes/ezdb.php' );
                    $db =& eZDB::instance();
                    $db->begin();
            
                    $contentObject =& $class->instantiate( $userID, $sectionID );
                    $nodeAssignment = eZNodeAssignment::create( array( 'contentobject_id' => $contentObject->attribute( 'id' ),
                                                                       'contentobject_version' => $contentObject->attribute( 'current_version' ),
                                                                       'parent_node' => $node->attribute( 'node_id' ),
                                                                       'is_main' => 1 ) );
            
                    $nodeAssignment->store();
                    
                    $version = $contentObject->currentVersion();
            
                    $attribs =& $version->contentObjectAttributes();
                    $attribsCount = count( $attribs );
            
                    for ( $j = 0; $j < $attribsCount; $j++ )
                    {
                        $identifier = $attribs[$j]->attribute( 'contentclass_attribute_identifier' );
                
                        switch ( $identifier )
                        {
                            case 'number':
                                {
                                    $attribs[$j]->setAttribute( 'data_int', $number + 1 );
                                } break;

                            case 'title':
                                {
                                    $attribs[$j]->setAttribute( 'data_text', $track['title'] );
                                }break;
        
                            case 'length':
                                {
                                    $attribs[$j]->setAttribute( 'data_int', $track['length'] );
                                }break;

                            case 'artist':
                                {
                                    $trackArtist = trim( $track['artist'] );
                                    eZDebug::writeDebug( $trackArtist );
                                    eZDebug::writeDebug( $trackArtist );
                                    foreach ( array_keys( $uniqueArtists ) as $uniqueArtistKey )
                                    {
                                        if ( strtolower( $uniqueArtists[$uniqueArtistKey] ) == strtolower( $trackArtist ) )
                                        {
                                            if ( is_numeric( $artistMappings[$uniqueArtistKey] ) && $artistMappings[$uniqueArtistKey] != 0 )
                                            {
                                                eZDebug::writeDebug( $artistMappings[$uniqueArtistKey] );
                                                $attribs[$j]->setAttribute( 'data_int', $artistMappings[$uniqueArtistKey] );
                                            }
        
                                            break;
                                        }
                                    }
                                } break;

                            default:
                                {
                                    // do nothing
                                }
                        }
                
                        $attribs[$j]->store();
                    }
                    
                    include_once( 'lib/ezutils/classes/ezoperationhandler.php' );
        
                    $operationParams = array();
                    $operationParams['object_id']   = $contentObject->attribute( 'id' );
                    $operationParams['version']     = $contentObject->attribute( 'current_version' );
        
                    $operationResult = eZOperationHandler::execute( 'content', 'publish', $operationParams );
        
                    // when preview cache is on, the user is restored but the policy limitations are still wrongly cached
                    // see http://ez.no/community/bugs/cache_for_content_read_limitation_list_isn_t_cleared_after_switching_users
                    // this is a temporary workaround, until the kernel has been fixed
                    if ( isset( $GLOBALS['ezpolicylimitation_list']['content']['read'] ) )
                    {
                        unset( $GLOBALS['ezpolicylimitation_list']['content']['read'] );
                    }
          
                    $trackNode =& eZContentObjectTreeNode::fetchNode( $contentObject->attribute( 'id' ), $node->attribute( 'node_id' ) );
                    $trackNode->setAttribute( 'priority', $number + 1 );
                    $trackNode->store();
                    
                    $db->commit();
                }
            }
            
            return $Module->redirect( 'cddb', 'search' );
            //return $Module->redirectTo( $node->attribute( 'url_alias' ) );
        }
        else
        {
            eZDebug::writeError( 'invalid class for disc' );
            return;
        }
    }
    
}

$tpl->setVariable( 'unique_artists', $uniqueArtists );
$tpl->setVariable( 'artist_mappings', $artistMappings );
$tpl->setVariable( 'disc', $eZDisc );
$tpl->setVariable( 'category', $category );

$Result = array();
$Result['content'] = & $tpl->fetch( 'design:cddb/import.tpl' );
$Result['left_menu'] = 'design:parts/cddb/menu.tpl';
$Result['path'] = array(
    array( 'text' => ezi18n( 'extension/cddb', 'CDDB' ),
           'url' => '/cddb/search' ),
    array( 'text' => ezi18n( 'extension/cddb', 'Import' ),
           'url' => false )
    );


?>