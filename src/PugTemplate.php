<?php
/**
 * PugTemplate
 *
 * @author Anton Desin anton.desin@gmail.com
 * @copyright (c) Anton Desin
 * @link https://desin.name
 */

namespace Desin;


class PugTemplate
{
	static $cachePath = "/cache";
	static $rootDir = null;
	
	public static function setRootDir ($value) {
		self::$rootDir = $value;
	}
	
	public static function displayFile ($file, $variables = [], $return=false) {
		$fileinfo = pathinfo ($file);
		
		$options = self::getOptions([
			'paths' => [
				$fileinfo['dirname'],
			],
		]);
		
		//$timeStart = microtime(true);
		
		$method = ($return===true)?'renderFile':'displayFile';
		try{
			$result = \Phug\Optimizer::call($method, [$fileinfo['filename'], $variables], $options);
		}catch(Throwable $t){
			echo "<p>".$t->getTraceAsString()."</p>";
		}
		
		if($return === true){
			return $result;
		}
	}
	
	public static function renderFile ($file, $variables = []) {
		return self::displayFile($file, $variables, true);
	}
	
	public static function cacheDir ($dir) {
		$options = self::getOptions([
			'paths' => [
				$dir,
			],
		]);
		
		$pug = new \Pug\Pug($options);
		list($success, $errors) = $pug->cacheDirectory($dir);
		
		echo "$success files have been cached\n";
		echo "$errors errors occurred\n";
	}
	
	private static function getOptions ($options) {
		$path = dirname(__FILE__);
		$cachePath = realpath($path . '/..') . self::$cachePath;
		
		$optionsDefault = [
			'debug' => false,
			'prettyprint' => true,
			'pugjs' => true,
			'modules' => [\JsPhpize\JsPhpizePhug::class],
			//'up_to_date_check' => false,
			'keep_base_name' => true,
			'expressionLanguage' => 'js',
			'cache_dir' => $cachePath,
			//'basedir' => ,
			'on_output' => function (\Phug\Compiler\Event\OutputEvent $event) {
				$string = $event->getOutput();
				$string = preg_replace("/<\?xml.*\?>/", "", $string);
				$event->setOutput($string);
			},
		];
		return array_merge_recursive($options, $optionsDefault);
	}
}