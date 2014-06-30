<?php

App::uses('BasicSeedAppShell', 'BasicSeed.Console/Command');

class BasicSeedShell extends BasicSeedAppShell {
	public $seedFile = 'seed.php';
	public $seedDevFile = 'seed_dev.php';

	public function main() {
		$this->includeFile($this->absolutePath($this->getFile()));
	}

	public function init() {
		$this->existsOrCreate($this->absolutePath($this->getFile()));
	}

	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->addOption('dev', array(
			'boolean' => true,
			'help' => 'Use the default dev file instead of the default'
		));
		$parser->addOption('file', array(
			'help' => 'Manually specify the file that should be used'
		));
		return $parser;
	}

	public function firstOrCreate($Model, $conditions, $data = array()) {
		$record = $Model->find('first', array('conditions' => $conditions));
		if (empty($record)) {
			$Model->create($data + $conditions);
			if ($Model->save()) {
				$record = $Model->read();
			} else {
				$cond = var_export($conditions);
				$this->out("Failed to create {$Model} record for conditions:\n\n{$cond}");
				exit();
			}
		}
		return $record;
	}

	private function getFile() {
		$file = $this->seedFile;
		if (isset($this->params['file']) && !empty($this->params['file'])) {
			$file = $this->params['file'];
		} else if ($this->params['dev']) {
			$file = $this->seedDevFile;
		}
		return $file;
	}

	private function includeFile($file) {
		include $file;
	}

	private function existsOrCreate($file) {
		if (!file_exists($file)) {
			file_put_contents($file, "<?php\n\n");
		}
	}

	private function absolutePath($file) {
		return APP . 'Config' . DS . $file;
	}
}

