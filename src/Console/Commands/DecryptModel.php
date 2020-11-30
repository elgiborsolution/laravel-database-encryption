<?php
/**
 * src/Commands/DecryptModel.php.
 *
 */
namespace ESolution\DBEncryption\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DecryptModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encryptable:decryptModel {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Encrypt models rows';

    private $attributes = [];
    private $model;

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $class = $this->argument('model');
        $this->model = $this->guardClass($class);
        $this->attributes = $this->model->getEncryptableAttributes();
        $table = $this->model->getTable();
        $pk_id = $this->model->getKeyName();
        $total = $this->model->where('encrypted', 1)->count();
        $this->model::$enableEncryption = false;

        if($total > 0){
            $this->comment($total.' records will be decrypted');
            $bar = $this->output->createProgressBar($total);
            $bar->setFormat('%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

            $records =  $this->model->orderBy($pk_id, 'asc')->where('encrypted', 1)
            ->chunkById(100, function($records) use($table, $bar, $pk_id) {
                foreach ($records as $record) {
                    $record->timestamps = false;
                    $attributes = $this->getDecryptedAttributes($record);
                    $update_id =  "{$record->{$pk_id}}";
                    DB::table($table)->where($pk_id, $update_id)->update($attributes);
                    $bar->advance();
                    $record = null;
                    $attributes = null;
                }
            });
            
            $bar->finish();

        }

        $this->comment('Finished Model Decryption');
    }

    private function getDecryptedAttributes($record)
    {
        $encryptedFields = ['encrypted' => 0 ];

        foreach ($this->attributes as $attribute) {
            $raw = $record->{$attribute};

            // if (str_contains($raw, $record->encrypter()->getPrefix())) {

                $encryptedFields[$attribute] = $this->model->decryptAttribute($raw);
            // }
        }
        return $encryptedFields;
    }

    private function validateHasEncryptedColumn($model)
    {
        $table = $model->getTable();
        if (! Schema::hasColumn($table, 'encrypted')) {
            $this->comment('Creating encrypted column');
            Schema::table($table, function (Blueprint $table) {
                $table->tinyInteger('encrypted')->default(0);
            });
        }
    }

    /**
     * @param $class
     * @return Model
     * @throws \Exception
     */
    public function guardClass($class)
    {
        if (!class_exists($class))
            throw new \Exception("Class {$class} does not exists");
        $model = new $class();
        $this->validateHasEncryptedColumn($model);
        return $model;
    }
}
