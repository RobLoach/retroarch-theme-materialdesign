<?php

/**
 * RetroArch Material Design Theme
 *
 * @license MIT
 */

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Process\Process;

require_once 'vendor/autoload.php';

// Clear out the PNG directory.
deleteContents('png');

// Inherit from MonoChrome.
copyDir('vendor/libretro/retroarch-assets/xmb/monochrome/png', 'png');

// Copy over the background.
copy('src/bg.png', 'png/bg.png');

// Load the icons to generate.
$yaml = file_get_contents('src/icons.yml');
$icons = Yaml::parse($yaml);

// Loop through each icon.
foreach ($icons as $destination => $id) {
	// Load the Unicode for the Font Awesome icon.
	$unicode = findUnicode($id);
	if ($unicode) {
		// Load the character for the icon.
		$char = unicodeToChar($unicode);

		// Write out the icon.
		$process = new Process("convert -background none -fill '#f2f2f2' -font node_modules/mdi/fonts/materialdesignicons-webfont.ttf -pointsize 230 label:$char png/$destination.png");
		$process->run();
		usleep(250000);

		// Size it correctly.
		$process = new Process("convert png/$destination.png -gravity Center  -background none -extent 256x256 png/$destination.png");
		$process->run();
		usleep(250000);
	}
	else {
		echo "Not found $id\n";
	}
}

/**
 * Converts the given unicode-16 to its UTF-8 character.
 */
function unicodeToChar($unicode) {
	return json_decode('"\u'.$unicode.'"');
}

/**
 * Scan through the available Font Awesome icons for the given ID.
 */
function findUnicode($id) {
	$icons = materialDesignIcons();
	if (isset($icons[$id])) {
		return $icons[$id];
	}
}

/**
 * Load the list of Material Design icons.
 */
function materialDesignIcons() {
	static $icons = array();

	if (empty($icons)) {
		$yaml = file_get_contents('src/materialdesign-icons.yaml');
		$icons = Yaml::parse($yaml);
	}
	return $icons;
}

/**
 * Copies the contents of one directory to another.
 */
function copyDir($src, $dest) {
	$files = glob("$src/*.*");
    foreach($files as $file){
    	$file_to_go = str_replace($src, $dest, $file);
    	copy($file, $file_to_go);
    }
}

/**
 * Delete the contents of the given directory.
 */
function deleteContents($dir) {
	return array_map('unlink', glob("$dir/*"));
}
