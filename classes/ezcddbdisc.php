<?php

class eZCDDBDisc
{
    var $netCDDBDisc;
    var $CDDBCharset;

    function eZCDDBDisc( $Net_CDDB_Disc, $CDDBCharset = 'iso-8859-1' )
    {
        $this->CDDBCharset = $CDDBCharset;
        $this->netCDDBDisc = $Net_CDDB_Disc;
    }

    function attributes()
    {
        return array( 'discid', 
                      'title', 
                      'artist',
                      'category',
                      'genre', 
                      'num_tracks', 
                      'length', 
                      'length_formatted', 
                      'cddb_string', 
                      'year', 
                      'tracks', 
                      'revision' );
    }
    
    function offset2time( $offset )
    {
        $frames = $offset % 75;
        $time = (int) ($offset / 75);
        
        return $time;
    }
    
    function getDiscLength( $formatted = false )
    {
        $tt = 75 * $this->netCDDBDisc->getDiscLength();
        $totalSeconds = $this->offset2time( $tt );
        
        if ( $formatted )
        {
            $hours = floor($totalSeconds / (60 * 60));
            $minutes = floor(($totalSeconds % (60 * 60))/60);
            $seconds = $totalSeconds % 60;
            
            return sprintf('%02d', $hours) . ':' . sprintf('%02d', $minutes) . ':' . sprintf('%02d', $seconds);
        }
        else
        {
            return $totalSeconds;
        }
        
    }

    function getTrackLength($track_num, $formatted = false)
    {
        # unfortunately, we do not have disc length in frames!
        $tt = 75 * $this->netCDDBDisc->getDiscLength();  
        
        $offset = $this->netCDDBDisc->getTrackOffset( $track_num );
        
        $numTracks = $this->netCDDBDisc->numTracks();
        if ( $track_num < ( $numTracks - 1 ) )
        {
            $nextOffset = $this->netCDDBDisc->getTrackOffset( $track_num + 1 );
        }
        else
        {
            $nextOffset = $tt;
        }
        
        $duration= $nextOffset - $offset;
        $duration= 75 * (int) ( ( $duration + 37 ) / 75 ); # round to nearest seconds
        $length = $this->offset2time( $duration );
        
        if ($formatted) {
            $minutes = floor( $length / 60 );
            $seconds = $length % 60;
            
            return sprintf( '%02d', $minutes ) . ':' . sprintf( '%02d', $seconds );
        } else {
            return $length;
        }
    }
    
    function attribute( $key )
    {
        include_once( 'lib/ezi18n/classes/eztextcodec.php' );
        $charset = eZTextCodec::internalCharset();

        include_once( 'lib/ezi18n/classes/ezcharsetinfo.php' );
        $charset = eZCharsetInfo::realCharsetCode( $charset );

        $cddbCharset = eZCharsetInfo::realCharsetCode( $this->CDDBCharset );

        include_once( 'lib/ezi18n/classes/eztextcodec.php' );
        $codec =& eZTextCodec::instance( $cddbCharset, $charset, false );

        switch( $key )
        {
            case 'discid':
            {
                return $this->netCDDBDisc->getDiscId();
            } break;

            case 'title':
            {
                return $codec->convertString( $this->netCDDBDisc->getTitle() );
            } break;
            
            case 'artist':
            {
                return $codec->convertString( $this->netCDDBDisc->getArtist() );
            } break;

            case 'category':
            {
                return $codec->convertString( $this->netCDDBDisc->getCategory() );
            } break;
            
            case 'genre':
            {
                return $codec->convertString( $this->netCDDBDisc->getGenre() );
            } break;
            
            case 'num_tracks':
            {
                return $this->netCDDBDisc->numTracks();
            } break;
            
            case 'length':
            {
                return $this->getDiscLength();
            } break;
            
            case 'length_formatted':
            {
                return $this->getDiscLength( true );
            } break;
            
            case 'cddb_string':
            {
                return $this->netCDDBDisc->toString();
            } break;
            
            case 'year':
            {
                return $this->netCDDBDisc->_year;   
            } break;
            
            case 'tracks':
            {
                $trackArray = array();
                for( $i = 0; $i < $this->netCDDBDisc->numTracks(); $i++ )
                {
                    $trackArray[] = array( 'title' => $codec->convertString( $this->netCDDBDisc->getTrackTitle($i) ),
                                           'offset' => $this->netCDDBDisc->getTrackOffset($i),
                                           'length' => $this->getTrackLength($i),
                                           'length_formatted' => $this->getTrackLength($i, true)
                                         );
                }
                return $trackArray;
            } break;
            
            case 'revision':
            {
                return $this->netCDDBDisc->_revision;
            } break;
            
            default;
            {
                eZDebug::writeWarning( 'unknown attribute: ' . $key, 'eZCDDBDisc' );
            }
        }
    }

    function hasAttribute( $key )
    {
        $attributes = eZCDDBDisc::attributes();
        
        if ( in_array( $key, $attributes ) )
        {
            return true;
        }

        return false;
    }
}
?>