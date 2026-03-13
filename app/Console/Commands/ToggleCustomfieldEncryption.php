<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\CustomField;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ToggleCustomfieldEncryption extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:customfield-encryption
                             {fieldname : the db_column_name of the field}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command should be used to convert an unencrypted custom field into a custom field and encrypt the associated data in the assets table for that column.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fieldname = $this->argument('fieldname');

        if ($field = CustomField::where('db_column', $fieldname)->first()) {

            // If the field is not encrypted, make it encrypted and encrypt the data in the assets table for the
            // corresponding field.
            DB::transaction(function () use ($field) {

                if ($field->field_encrypted == 0) {
                    $assets = Asset::whereNotNull($field->db_column)->get();

                    foreach ($assets as $asset) {
                        $asset->{$field->db_column} = encrypt($asset->{$field->db_column});
                        $asset->save();
                    }

                    $field->field_encrypted = 1;
                    $field->save();

                // This field is already encrypted. Do nothing.
                } else {
                    $this->error('The custom field ' . $field->db_column.' is already encrypted. No action was taken.');
                }
            });

        // No matching column name found
        } else {
            $this->error('No matching results for unencrypted custom fields with db_column name: ' . $fieldname.'. Please check the fieldname.');
        }

    }
}
