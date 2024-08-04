<?php


include_once 'vendor/autoload.php';

$a = Ability\ComposerReader\Reader::create( __DIR__ );


dd( $a->get( 'require.php' ) );