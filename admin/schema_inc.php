<?php

$tables = array(

'cvsarchive' => "
  cvs_id I4 AUTO PRIMARY,
  content_id I4 NOTNULL,
  server C(64),
  cvsroot C(64),
  username C(64),
  passwd C(64),
  mode I4
  CONSTRAINT ', CONSTRAINT `nl_con_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )'
"

);

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( CVSVIEW_PKG_NAME, $tableName, $tables[$tableName] );
}

$gBitInstaller->registerPackageInfo( CVSVIEW_PKG_NAME, array(
	'description' => "'http://www.google.com'.",
	'license' => '<a href="http://www.gnu.org/licenses/licenses.html#LGPL">LGPL</a>',
	'version' => '1.0',
	'state' => 'R2',
	'dependencies' => '',
) );

// ### Default Preferences
$gBitInstaller->registerPreferences( CVSVIEW_PKG_NAME, array(
	array(CVSVIEW_PKG_NAME, 'cvs_default','bitweaver'),
) );

// ### Default UserPermissions
$gBitInstaller->registerUserPermissions( NEWSLETTERS_PKG_NAME, array(
	array('p_cvsview_admin', 'Can admin and add cvs archives', 'admin', 'cvsview'),
	array('p_cvsview_view', 'Can view cvs archive', 'registered', 'cvsview'),
) );

?>
