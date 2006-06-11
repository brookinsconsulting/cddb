<?php

$Module = array( 'name' => 'CDDB', 'variable_params' => false );

$ViewList = array();

$ViewList['search'] = array(
    'script' => 'search.php',
    'ui_context' => 'navigation',
    'params' => array(),
    'default_navigation_part' => 'ezcddbnavigationpart',
    'single_post_actions' => array(
        'SearchButton' => 'search',
        'RawSearchButton' => 'rawsearch'
        ),
    'post_action_parameters' => array( 
        'search' => array( 
            'offsets' => 'offsets',
            'length' => 'length'
            ),
        'rawsearch' => array(
            'query' => 'query'
            )
        )
);

$ViewList['discinfo'] = array(
    'script' => 'discinfo.php',
    'ui_context' => 'navigation',
    'params' => array( 'Category', 'DiscID' ),
    'default_navigation_part' => 'ezcddbnavigationpart',
    'single_post_actions' => array( 'ImportButton' => 'import' )
);

$ViewList['submit'] = array(
    'script' => 'submit.php',
    'ui_context' => 'edit',
    'params' => array(),
    'default_navigation_part' => 'ezcddbnavigationpart'
);

$ViewList['settings'] = array(
    'script' => 'settings.php',
    'ui_context' => 'setup',
    'params' => array(),
    'default_navigation_part' => 'ezcddbnavigationpart'
);

$ViewList['import'] = array(
    'script' => 'import.php',
    'ui_context' => 'edit',
    'params' => array( 'Category', 'DiscID' ),
    'default_navigation_part' => 'ezcddbnavigationpart',
    'single_post_actions' => array( 'ImportButton' => 'import' )
);

$FunctionList = array();

?>
