<?php
global $gBitSystem, $gBitSmarty;

$registerHash = array(
	'package_name' => 'cvsview',
	'package_path' => dirname( __FILE__ ).'/',
	'homeable' => TRUE,
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'cvsview' ) ) {

	$menuHash = array(
		'package_name'  => CVSVIEW_PKG_NAME,
		'index_url'     => CVSVIEW_PKG_URL.'index.php',
		'menu_template' => 'bitpackage:cvsview/menu_cvsview.tpl',
	);
	$gBitSystem->registerAppMenu( $menuHash );

	require_once ( CVSVIEW_PKG_PATH.'theme.php' );
	require_once( CVSVIEW_PKG_PATH.'phpcvs.php' );
	require_once ( CVSVIEW_PKG_PATH.'phpcvsmime.php' );

}
?>
