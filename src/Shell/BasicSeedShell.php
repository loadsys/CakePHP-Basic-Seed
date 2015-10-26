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
use Cake\ORM\Entity;
use Cake\ORM\Table;
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
	 * Set up the Shell.
	 *
	 * @return void
	 */
	public function initialize() {
		$this->_io->styles('success', ['text' => 'green']);
		// quiet = (none)/warning, white/yellow
		// out = info, cyan
		// verbose = success, green
	}

	/**
	 * Public method used for creating a new blank seed file.
	 *
	 * @return void
	 */
	public function init() {
		$path = $this->absolutePath($this->getFile());
		$this->quiet('Initializing seed file: ' . $this->shortPath($path));
		$this->existsOrCreate($path);
	}

	/**
	 * Executes the proper seed file.
	 *
	 * Initializes an empty seed file first if it does not already exist.
	 *
	 * @return int|void
	 */
	public function main() {
		$this->includeFile($this->absolutePath($this->getFile()));
		$this->quiet("...Done!");
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
	 * 			//'_options' => [ // Define options to pass to newEntity(). Allows for disabling validation
	 * 			//	'validate' => false,
	 * 			//],
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
	 * @return void
	 */
	public function importTables(array $data) {
		$tableCount = count($data);
		$this->out("<info>Starting seed of {$tableCount} table(s).</info>");

		foreach ($data as $table => $records) {
			$this->out("<info>{$table}</info>");

			// Set default field values.
			$defaults = [];
			if (array_key_exists('_defaults', $records)) {
				$defaults = $records['_defaults'];
				unset($records['_defaults']);
				$this->verbose("<success>{$table}: Default values set.</success>");
			}

			// Set entity options, if present.
			$entityOptions = [];
			if (array_key_exists('_options', $records)) {
				$entityOptions = $records['_options'];
				unset($records['_options']);
				$this->verbose("<success>{$table}: Entity options set, but...</success>");
				$this->quiet("<warning>{$table}: Deprecation notice: Change [_options] to [_entityOptions].</warning>");
			} elseif (array_key_exists('_entityOptions', $records)) {
				$entityOptions = $records['_entityOptions'];
				unset($records['_entityOptions']);
				$this->verbose("<success>{$table}: Entity options set.</success>");
			}

			// Set save options, if present.
			$saveOptions = [];
			if (array_key_exists('_saveOptions', $records)) {
				$saveOptions = $records['_saveOptions'];
				unset($records['_saveOptions']);
				$this->verbose("<success>{$table}: Table save() options set.</success>");
			}

			// Truncate the table, if requested.
			$Table = $this->loadModel($table);
			if (array_key_exists('_truncate', $records) && $records['_truncate']) {
				$this->truncateTable($Table);
			}

			unset($records['_truncate']);

			// Create or update all defined records.
			$this->importTable(
				$Table,
				$this->entityGenerator($Table, $records, $defaults, $entityOptions),
				$saveOptions
			);
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
	 * @param array $defaults Optional array of default field values to merge into each record.
	 * @param array $options Optional array of newEntity() options to use.
	 * @return void
	 */
	public function entityGenerator(
		Table $Table,
		array $records,
		array $defaults = [],
		array $options = []
	) {
		$defaultOptions = [
			'validate' => true,
			'accessibleFields' => ['*' => true],
		];
		$options = $options + $defaultOptions;

		$keyField = $Table->primaryKey();

		foreach ($records as $r) {
			$r = Hash::merge($defaults, $r);

			$id = (!empty($r[$keyField]) ? $r[$keyField] : false);
			if ($id) {
				$entity = $Table->find()->where([$keyField => $id])->first();
				if ($entity) {
					$entity = $Table->patchEntity($entity, $r, $options);
					if (!$entity->dirty()) {
						$this->verbose("<success>{$Table->alias()} ({$id}): No changes.</success>");
						continue;
					}

				} else {
					$entity = $Table->newEntity($r, $options);
					$entity->isNew(true);
				}

			} else {
				$entity = $Table->newEntity($r, $options);
			}

			$errors = $entity->errors();
			if ($errors) {
				$this->printValidationErrors(
					$Table->alias(),
					$id,
					$errors
				);

				continue;
			}

			yield $entity;
		}
	}

	/**
	 * Helper function to import a set of records for a single Table.
	 *
	 * Used by imporTables().
	 *
	 * @param Cake\ORM\Table $Table A Table instance to save records into.
	 * @param array|\Generator $records An array of Entity records to save into the Table.
	 * @param array $options Options to pass to save().
	 * @return void
	 */
	public function importTable(Table $Table, $records, array $options = []) {
		$defaultOptions = [
			'checkRules' => true,
			'checkExisting' => true,
		];
		$options = $options + $defaultOptions;

		foreach ($records as $record) {
			$action = ($record->isNew() ? 'Create' : 'Update');
			$result = $Table->save($record, $options);
			$key = $this->findKey($Table, $record);

			if ($result) {
				$this->verbose("<success>{$Table->alias()} ({$key}): {$action} successful.</success>");
			} else {
				$this->quiet("<warning>{$Table->alias()} ({$key}): {$action} failed.</warning>");
				$this->printValidationErrors(
					$Table->alias(),
					$this->findKey($Table, $record),
					$record->errors()
				);
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
			$this->verbose("<success>{$Table->alias()}: Existing DB records truncated.</success>");
		} else {
			$this->quiet("<warning>{$Table->alias()}: Can not truncate existing records.</warning>");
		}

		return $success;
	}

	/**
	 * Helper method to find the primary key for a record.
	 *
	 * @param Cake\ORM\Table $Table An instantiated Table object.
	 * @param Cake\ORM\ntity $entity The record being saved into the DB.
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
		foreach ($errors as $field => $messages) {
			foreach ((array)$messages as $message) {
				$this->quiet("<warning>{$table} ({$id}): {$field}: {$message}</warning>");
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
		$this->quiet('Loading seed file: ' . $this->shortPath($file));
		include $file;
	}

	/**
	 * Generates a new, empty seed file if one does not already exist.
	 *
	 * This is called during initialize to ensure that the file is
	 * _always_ available for reading.
	 *
	 * @TODO: Convert this into a proper bake template.
	 *
	 * @param string $file The full filesystem path to check/create.
	 * @return void
	 */
	protected function existsOrCreate($file) {
		if (!file_exists($file)) {
			$this->out('<info>Creating empty seed file: ' . $this->shortPath($file) . '</info>');

			file_put_contents($file, <<<'EOD'
<?php
/**
 * BasicSeed plugin data seed file.
 */

namespace App\Config\BasicSeed;

use Cake\ORM\TableRegistry;

// Write your data import statements here.
$data = [
	'TableName' => [
		//'_truncate' => true,
		//'_entityOptions' => [
		//	'validate' => false,
		//],
		//'_saveOptions' => [
		//	'checkRules' => false,
		//],
		'_defaults' => [],
		[
			'id' => 1,
			'name' => 'record 1',
		],
	],
];

$this->importTables($data);

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
			->addSubcommand('init', [
				'help' => 'Initialize a new, empty seed file. Respects both the --dev and --file options.',
			])
			->addOption('dev', [
				'short' => 'd',
				'boolean' => true,
				'default' => false,
				'help' => 'Use the "dev" seed file instead of the default.'
			])
			->addOption('file', [
				'short' => 'f',
				'help' => 'Manually specify the file that should be used. When this option is present, its argument will always be used explicitly, overriding the --dev option if it is also present.'
			]);
		return $parser;
	}
}
