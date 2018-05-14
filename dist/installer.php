<?php

// Github link of the repository
define( "OWNER", "mindstorm38" );
define( "REPO", "php-helper" );
define( "REPO_LINK", "https://github.com/" . OWNER . "/" . REPO );
define( "PHP_HELPER_DIR", "php-helper" );
define( "VERSION_FILE", "version" );
define( "TMP_FOLDER", "tmp" );
define( "SRC_FOLDER", "src" );

function println( $msg ) {
	echo $msg . "\n";
}

function file_build_path( ...$segments ) {
	return join( DIRECTORY_SEPARATOR, $segments );
}

function short_sha( $sha ) {
	return substr( $sha, 0, 7 );
}

function rmdir_recursive( $dir ) {
	foreach( scandir( $dir ) as $file ) {
		if ( '.' === $file || '..' === $file ) continue;
		if ( is_dir( "$dir/$file" ) ) rmdir_recursive( "$dir/$file" );
		else unlink( "$dir/$file" );
	}
	rmdir( $dir );
}

function github_api_raw( $api, $params = [] ) {

	$url = "http://api.github.com/{$api}";

	$options = [
		"http" => [
			"header" => "Content-type: application/x-www-form-urlencoded\r\n",
			"method" => "POST",
			"content" => http_build_query( $params ),
			"user_agent" => "https://github.com/" . OWNER
		]
	];

	$context = stream_context_create( $options );
	$result = file_get_contents( $url, false, $context );

	if ( $result === false ) return null;

	return $result;

}

function github_api_json( $api, $params = [] ) {
	$result = github_api_raw( $api, $params );
	if ( $result === null ) return null;
	return json_decode( $result, true );
}

println( "Starting PHPHelper installer ..." );
println( "Repository link : " . REPO_LINK );

println( "Checking current installation ..." );

$update = file_exists( VERSION_FILE );
$current_version_commit_sha = $update ? explode( "\n", file_get_contents( VERSION_FILE ) )[0] : "master";

if ( $update ) println( "Currently installed (" . short_sha( $current_version_commit_sha ) ."), checking for newer version ..." );
else println( "Not currently installed, installing ..." );

// println( "PHPHelper is currently installed commit version {$current_version_commit_sha}" );

$latest_commits = github_api_json( "repos/" . OWNER . "/" . REPO . "/commits", [ "sha" => $current_version_commit_sha ] );

if ( count( $latest_commits ) === 0 ) {

	if ( $update ) {

		// println( "Can't update from current version, installing ..." );
		$update = false;

	} else {

		println( "No commit fount ... Stoping installation." );
		die();

	}

}

$latest_commit_sha = $latest_commits[0]["sha"];

if ( $update && $latest_commit_sha === $current_version_commit_sha ) {

	println( "Latest version already installed." );
	die();

}

// Paths
$download_link = REPO_LINK . "/archive/{$latest_commit_sha}.zip";
$tmp_download_file = file_build_path ( TMP_FOLDER, "{$latest_commit_sha}.zip" );

// Clearing existing downloaded file
if ( !is_dir( TMP_FOLDER ) ) mkdir( TMP_FOLDER );
if ( file_exists( $tmp_download_file ) ) unlink( $tmp_download_file );

println( "Downloading latest version (" . short_sha( $latest_commit_sha ) . ") ..." );
file_put_contents( $tmp_download_file, fopen( $download_link, "r" ) );
println( "Latest version downloaded. Installing ..." );

// Clearing src dir
println( "Clearing old version" );
if ( is_dir( SRC_FOLDER ) ) rmdir_recursive( SRC_FOLDER );
mkdir( SRC_FOLDER );

// Extracting new files from zip archive
$zip_archive = new ZipArchive();

if ( $zip_archive->open( $tmp_download_file ) !== TRUE ) {

	println( "Error while opening downloaded zip file '{$tmp_download_file}'." );
	die();

}

println( "Extracting zip files ..." );

for ( $i = 0; $i < $zip_archive->numFiles; $i++ ) {

	$raw_name = $zip_archive->getNameIndex( $i );

	if ( $raw_name[ strlen( $raw_name ) - 1 ] === "/" ) continue;

	$name = substr( $raw_name, strpos( $raw_name, "/" ) + 1 );

	$matches = [];

	if ( preg_match( "/^src\/(.*)/", $name, $matches ) === 1 ) {

		copy( "zip://{$tmp_download_file}#{$raw_name}", file_build_path( SRC_FOLDER, $matches[ 1 ] ) );

		// $zip_archive->extractTo( $src_folder_path, $raw_name );

	}

}

$zip_archive->close();

println( "All need files extracted. Updating version file..." );
file_put_contents( VERSION_FILE, $latest_commit_sha );

println( "Removing tmp folder..." );
rmdir_recursive( TMP_FOLDER );

println( "PHPHelper successfuly " . ( $update ? "updated" : "installed" ) . "." );

die();

?>
