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
$total = count($icons);
$num = 0;

// Loop through each icon.
foreach ($icons as $destination => $id) {
	$percent = ($num++ / $total) * 100;

	// Load the Unicode for the Font Awesome icon.
	$unicode = findUnicode($id);
	if ($unicode) {
		$displaypercent = number_format($percent, 0);
		echo "$displaypercent%\t$destination: ";
		// Load the character for the icon.
		$char = unicodeToChar($unicode);

		// Write out the icon.
		$size = iconSize($destination);
		file_put_contents("node_modules/char.utf8", $unicode);
		//echo "convert -background none -fill '#f2f2f2' -font node_modules/@mdi/font/fonts/materialdesignicons-webfont.ttf -trim -pointsize $size label:$char 'node_modules/$destination.png'";
		$process = new Process(["convert -background none -fill '#f2f2f2' -font node_modules/@mdi/font/fonts/materialdesignicons-webfont.ttf -trim -pointsize $size label:$char 'node_modules/$destination.png'"]);
		$process->enableOutput();
		$process->run();
		$error = $process->getErrorOutput();
		echo $error;
		echo "$id\n";

		// Size it correctly.
		// usleep(500);
		// $process = new Process(["convert 'node_modules/$destination.png' -gravity center -background none -extent 512x512 'png/$destination.png'"]);
		// $process->enableOutput();
		// $process->run();
		// $error = $process->getErrorOutput();
		// echo $error;
		// echo "$id\n";
	}
	else {
		throw new \RuntimeException("When building $destination.png, the source of $id was not found.");
	}
}

function iconSize($icon) {
	$size = 512;
	switch ($icon) {
		case 'on':
		case 'off':
			$size *= 0.65;
			break;
		case 'subsetting':
			$size *= 0.8;
			break;
	}

	return $size;
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
