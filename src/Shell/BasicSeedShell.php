<?php
/**
 * A dataset loader Shell. Seed files are allowed to perform any actions
 * available within a Cake environment. It's up to the seed file to inject
 * the appropraite data into the appropriate tables. This Shell just
 * provides a runtime environment for that work.
 */

namespace BasicSeed\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Log\Log;

use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\Utility\Hash;


/**
 * BasicSeed Shell
 */
class BasicSeedShell extends Shell {

	/**
	 * The default "normal" seed file name.
	 *
	 * @var string
	 */
	public $seedFile = 'seed.php';

	/**
	 * The default "dev" seed file name.
	 *
	 * @var string
	 */
	public $seedDevFile = 'seed_dev.php';

	/**
	 * Executes the proper seed file.
	 *
	 * Initializes an empty seed file first if it does not already exist.
	 *
	 * @return int|void
	 */
	public function main() {
		$this->existsOrCreate($this->absolutePath($this->getFile()));
		$this->includeFile($this->absolutePath($this->getFile()));
		$this->out("...Done!");
	}

	/**
	 * Helper method for seeds to use when loading data from many tables.
	 *
	 * Will create or update records as necessary to make the database contain
	 * *at least* what is specified in this Seed. (Although any additional
	 * records will remain untouched unless the `_truncate` key is present and
	 * true.)
	 *
	 * Provided data must be in the following format:
	 *
	 * 	$data = [
	 * 		'TableName' => [ // Name of the table to import.
	 * 			//'_truncate' => true, // Will remove all existing records when present and true.
	 * 			'_defaults' => [ // Define default values for fields.
	 * 				'author' => 'Jane Doe',
	 * 				'is_published' => true,
	 * 			],
	 * 			[ // Each record must only define *unique* fields.
	 * 				'id' => 1,
	 * 				'title' => 'Frequently Asked Questions',
	 * 				'body' => 'Lorum ipsum.',
	 * 			],
	 * 			[
	 * 				'id' => 2,
	 * 				'title' => 'About Us',
	 * 				'body' => 'Bacon fillet tenderloin.',
	 * 			],
	 * 		],
	 * 	];
	 *
	 * @param array $data Array of [TableName => [ [record 1], [record 2]] sets.
	 * @return
	 */
	public function importTables(array $data) {
		$tableCount = count($data);
		$this->out("<info>Starting seed of {$tableCount} table(s).</info>");

		foreach($data as $table => $records) {
			$this->out("<info>{$table}</info>");

			// Set default field values.
			$defaults = [];
			if (array_key_exists('_defaults', $records)) {
				$defaults = $records['_defaults'];
				unset($records['_defaults']);
				$this->out("<info>{$table}: Default values set.</info>");
			}

			// Truncate the table, if requested.
			$Table = $this->loadModel($table);
			if (array_key_exists('_truncate', $records) && $records['_truncate']) {
				$this->truncateTable($Table);
			}
			unset($records['_truncate']);

			// Create or update all defined records.
			$this->importTable($Table, $this->entityGenerator($Table, $records, $defaults));
		}

		$this->out("<info>Seeding complete.</info>");
	}

	/**
	 * Helper generator for use with importTable().
	 *
	 * Yields a single new Entity instance approciate for $Table for each
	 * of $records where the values are merged with $defaults.
	 *
	 * Will skip any records that fail to validate, dumping validation
	 * errors to the console in the process.
	 *
	 * Used by imporTables().
	 *
	 * @param Cake\ORM\Table $Table A Table instance to save records into.
	 * @param array $records An array of Entity records to save into the Table.
	 * @return void
	 */
	public function entityGenerator(Table $Table, array $records, array $defaults = []) {
		foreach ($records as $i => $r) {
			$r = $Table->newEntity(Hash::merge($defaults, $r));
			if ($errors = $r->errors()) {
				$this->printValidationErrors($Table->alias(), $this->findKey($Table, $r), $errors);
				continue;
			}
			yield $r;
		}
	}

	/**
	 * Helper function to import a set of records for a single Table.
	 *
	 * Used by imporTables().
	 *
	 * @param Cake\ORM\Table $Table A Table instance to save records into.
	 * @param array $records An array of Entity records to save into the Table.
	 * @return void
	 */
	public function importTable(Table $Table, $records) {
		foreach ($records as $record) {
			$result = $Table->save($record);
			$key = $this->findKey($Table, $record);
			if ($result) {
				$this->out("{$Table->alias()} ({$key}): Save successful.");
			} else {
				$this->out("{$Table->alias()} ({$key}): <warning>Save failed.</warning>");
			}
		}
	}

	/**
	 * Helper method that clears all records from the provided Table instance.
	 *
	 * The default file is `config/seed.php`. When the `--dev` flag is
	 * present, the default file changes to `config/seed_dev.php`. If
	 * the `--file` option is present, it overrides everything else and
	 * its value is used explicitly.
	 *
	 * @param Cake\ORM\Table $Table The Table instance to TRUNCATE.
	 * @return bool True on success truncation, false on failure.
	 */
	protected function truncateTable($Table) {
		$truncateSql = $Table->schema()->truncateSql($Table->connection())[0];
		$success = $Table->connection()->query($truncateSql);
		if ($success) {
			$this->out("<info>{$Table->alias()}: Existing DB records truncated.</info>");
		} else {
			$this->out("<warning>{$Table->alias()}: Can not truncate existing records.</warning>");
		}

		return $success;
	}

	/**
	 * Helper method to find the primary key for a record.
	 *
	 * @param Table $Table An instantiated Table object.
	 * @param array $record The record being saved into the DB.
	 * @return string The numeric or UUID value of the record's primary key, or 'unknown' on failure.
	 */
	protected function findKey(Table $Table, Entity $entity) {
		if (!empty($entity->{$Table->primaryKey()})) {
			$key = $entity->{$Table->primaryKey()};
		} else {
			$key = 'unknown';
		}
		return $key;
	}

	/**
	 * Helper method to print a validation errors array in a console-readable format.
	 *
	 * @param string $table The string name of the Table.
	 * @param mixed $id The primary key for the given record.
	 * @param array $errors Validation errors array.
	 * @return void
	 */
	protected function printValidationErrors($table, $id, $errors) {
		foreach($errors as $field => $messages) {
			foreach ((array)$messages as $message) {
				$this->out("<warning>{$table} ({$id}): {$field}: {$message}</warning>");
			}
		}
	}

	/**
	 * Determines which file will be executed.
	 *
	 * The default file is `config/seed.php`. When the `--dev` flag is
	 * present, the default file changes to `config/seed_dev.php`. If
	 * the `--file` option is present, it overrides everything else and
	 * its value is used explicitly.
	 *
	 * @return string The partial filename (from the /config dir) to execute.
	 */
	protected function getFile() {
		$file = ($this->params['dev'] ? $this->seedDevFile : $this->seedFile);
		if (!empty($this->params['file'])) {
			$file = $this->params['file'];
		}
		return $file;
	}

	/**
	 * Wraps around PHP's `include` to "execute" a seed file in the local scope.
	 *
	 * Seed files can do anything a Shell can do from this point, including
	 * loading Tables and saving new Entities into them. Within the seed
	 * file, the `$this` object will refer to the BasicSeedShell instance
	 * itself, providing access to `$this->loadModel()`, for example.
	 *
	 * @param string $file The full filesystem path to check/create.
	 * @return void
	 */
	protected function includeFile($file) {
		$this->out('Loading seed file `' . $this->shortPath($file) . '`...');
		include $file;
	}

	/**
	 * Generates a new, empty seed file if one does not already exist.
	 *
	 * This is called during initialize to ensure that the file is
	 * _always_ available for reading.
	 *
	 * @param string $file The full filesystem path to check/create.
	 * @return void
	 */
	protected function existsOrCreate($file) {
		if (!file_exists($file)) {
			$this->out('Seed file `' . $this->shortPath($file) . '` does not exist. Creating empty seed.');

			file_put_contents($file, <<<EOD
<?php
/**
 * BasicSeed plugin data seed file.
 */

namespace App\Config\BasicSeed;
use Cake\ORM\TableRegistry;

// Write your data import statements here.

EOD
			);
		}
	}

	/**
	 * Return a fully qualified system path to the named file.
	 *
	 * Starts from the app's `/config` directory.
	 *
	 * @param string $file The base filename.
	 * @return string The full filesystem path.
	 */
	protected function absolutePath($file) {
		return ROOT . DS . 'config' . DS . $file;
	}

	/**
	 * Configure command line options and arguments.
	 *
	 * @return ConsoleOptionParser
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser
			->description(
				'Provides a mechanism for loading data into any of Cake\'s configured databases.'
			)
			->addOption('dev', [
				'short' => 'd',
				'boolean' => true,
				'default' => false,
				'help' => 'Use the "dev" seed file instead of the default.'
			])
			->addOption('file', [
				'short' => 'f',
				'help' => 'Manually specify the file that should be used. This option overrides the --dev option. When this option is present, its argument will always be used explicitly.'
			]);
		return $parser;
	}
}
