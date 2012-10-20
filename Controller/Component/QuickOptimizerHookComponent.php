<?php
class QuickOptimizerHookComponent extends Component {
	public function startup(Controller $controller) {
		if (Configure::read('debug') == 0 && !Configure::read('Modules.QuickOptimizer.settings.enable_gzip')) {
			@ob_start ('ob_gzhandler');
			header('Content-type: text/html; charset: UTF-8');
			header('Cache-Control: must-revalidate');
			header("Expires: " . gmdate('D, d M Y H:i:s', time() - 1) . ' GMT');
		}
	}
}