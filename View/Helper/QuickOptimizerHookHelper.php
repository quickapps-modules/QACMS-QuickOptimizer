<?php
App::uses('JSMin', 'Vendor');

class QuickOptimizerHookHelper extends AppHelper {
	public function stylesheets_alter(&$css) {
		if (!Configure::read('Modules.QuickOptimizer.settings.compress_css')) {
			return false;
		}

		if (!is_writable(CSS)) {
			trigger_error(__d('quick_optimizer', 'Quick Optimizer was unable to minify CSS assets, make sure `ROOT/webroot/css/` is writable'), E_USER_WARNING);
			return false;
		}

		$join = array();

		foreach ($css as $media => $files) {
			foreach ($files as $key => $f) {
				if (strpos($f, '//') === false) {
					$url = $this->assetUrl($f, array('pathPrefix' => CSS_URL, 'ext' => '.css'));

					if ($p = $this->__assetPath($url)) {
						$join[$media][] = array('path' => $p, 'url' => $url);

						unset($css[$media][$key]);
					}
				}
			}
		}

		if (!empty($join)) {
			foreach ($join as $media => $files) {
				$hash = md5(implode('::', Hash::extract($files, '{n}.path')));
				$cache = '';
				$cacheFile = 'quick-optimizer-' . $hash . '.css';

				if (file_exists(CSS . $cacheFile)) {
					$css[$media][] = "/css/{$cacheFile}";
					continue;
				}

				foreach ($files as $asset) {
					$asset['content'] = @file_get_contents($asset['path']);
					$cache .= $this->__minifyCss($asset) . "\n\n";
				}

				cache(str_replace(WWW_ROOT, '', CSS) . $cacheFile, $cache, '+7 days', 'public');
				$css[$media][] = "/css/{$cacheFile}";
			}
		}
	}

	public function javascripts_alter(&$js) {
		if (!Configure::read('Modules.QuickOptimizer.settings.compress_js')) {
			return false;
		}
		
		if (!is_writable(JS)) {
			trigger_error(__d('quick_optimizer', 'Quick Optimizer was unable to minify JS assets, make sure `ROOT/webroot/js/` is writable'), E_USER_WARNING);
			return false;
		}

		$join = array();

		foreach ($js['file'] as $key => $f) {
			if (strpos($f, '//') === false) {
				$url = $this->assetUrl($f, array('pathPrefix' => JS_URL, 'ext' => '.js'));

				if ($p = $this->__assetPath($url)) {
					$join[] = $p;

					unset($js['file'][$key]);
				}
			}
		}

		if (!empty($join)) {
			$hash = md5(implode('::', $join));
			$cache = '';
			$cacheFile = 'quick-optimizer-' . $hash . '.js';

			if (file_exists(JS . $cacheFile)) {
				$js['file'][] = "/js/{$cacheFile}";
				return true;
			}

			foreach ($join as $f) {
				$cache .= @file_get_contents($f) . "\n\n";
			}

			$cache = JSMin::minify($cache);

			cache(str_replace(WWW_ROOT, '', JS) . $cacheFile, $cache, '+7 days', 'public');

			$js['file'][] = "/js/{$cacheFile}";
		}
	}

/**
 * Gets full path to a given asset-relative-url.
 *
 * @param string $url Relative path to asset
 * @return string Full path to asset file. or null if does not exists
 */
	private function __assetPath($url) {
		$base = preg_replace('/^\//', '', $this->_View->request->base);
		$url = preg_replace('/^\/' . $base . '\//', '/', $url);
		$url = preg_replace('/^\//', '', $url);

		if (strpos($url, '..') !== false || strpos($url, '.') === false) {
			return false;
		}

		$pathSegments = explode('.', $url);
		$ext = array_pop($pathSegments);
		$parts = explode('/', $url);
		$assetFile = null;

		if ($parts[0] === 'theme') {
			$themeName = $parts[1];

			unset($parts[0], $parts[1]);

			$fileFragment = urldecode(implode(DS, $parts));
			$path = App::themePath($themeName) . 'webroot' . DS;

			if (file_exists($path . $fileFragment)) {
				$assetFile = $path . $fileFragment;
			}
		} elseif ($parts[0] === 'js') {
			unset($parts[0]);

			$fileFragment = urldecode(implode(DS, $parts));

			if (file_exists(JS . $fileFragment)) {
				$assetFile = JS . $fileFragment;
			}
		} elseif ($parts[0] === 'css') {
			unset($parts[0]);

			$fileFragment = urldecode(implode(DS, $parts));

			if (file_exists(CSS . $fileFragment)) {
				$assetFile = CSS . $fileFragment;
			}
		} else {
			$plugin = Inflector::camelize($parts[0]);

			if (CakePlugin::loaded($plugin)) {
				unset($parts[0]);

				$fileFragment = urldecode(implode(DS, $parts));
				$pluginWebroot = CakePlugin::path($plugin) . 'webroot' . DS;

				if (file_exists($pluginWebroot . $fileFragment)) {
					$assetFile = $pluginWebroot . $fileFragment;
				}
			}
		}

		return $assetFile;
	}

	private function __minifyCss($info = array()) {
		$info = array_merge(array(
			'content' => '/* content */',
			'url' => null,
			'path' => null
		), $info);

		$info['url'] = preg_replace('/^\//', '', $info['url']);
		$parts = explode('/', $info['url']);
		array_pop($parts);
		$info['url'] = '/' . implode('/', $parts) . '/';

		preg_match_all('/url\([\'"]?(?<url>.*?)[\'"]?\)/', $info['content'], $matches);

		if (count($matches[0]) > 0) {
			$paths = $matches[1];

			foreach ($matches[0] as $k => $v) {
				// Rewrite the path only if it's not a URL
				if (!preg_match('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', $paths[$k])) {
					$info['content'] = str_replace($v, 'url('. $info['url'] . $paths[$k] . ')', $info['content']);
				}
			}
		}

		// remove comments
		$info['content'] = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $info['content']);
		// remove tabs, consecutivee spaces, newlines, etc.
		$info['content'] = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '	', '	'), '', $info['content']);
		// remove single spaces
		$info['content'] = str_replace(array(" {", "{ ", "; ", ": ", " :", " ,", ", ", ";}"), array("{", "{", ";", ":", ":", ",", ",", "}"), $info['content']);

		return $info['content'];
	}
}