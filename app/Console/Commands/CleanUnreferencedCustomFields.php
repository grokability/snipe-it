<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\AssetModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanUnreferencedCustomFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:clean-custom-fields
    {--force : Run immediately without requiring confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will iterate through all of your custom fields and fieldsets to determine which ones *should* be blank but aren\'t actually blank - and will blank those out, as well';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will blank out *ALL* custom fields in your assets that are not referenced by their model\'s fieldset. Are you sure you want to continue?')) {
            $this->fail('Command was canceled');
        }
        // first, we get a 'master fieldmap' of db fieldnames => null
        // (the 'null' part will be important later)
        $master_fieldmap = [];
        foreach (CustomField::all() as $field) {
            $master_fieldmap[$field->db_column] = null;
        }

        $update_count = 0;
        // then, we iterate through *each* customfieldset to figure out models which use that fieldset, and the fields in the fieldset
        foreach (CustomFieldset::all() as $fieldset) {
            // similar to the $master_fieldmap above, we assemble now an associative array of db_column -> null entries...
            $fieldset_fields = [];
            foreach ($fieldset->fields as $field) {
                $fieldset_fields[$field->db_column] = null;
            }
            $model_ids = $fieldset->models()->withTrashed()->select('id'); // should generate a subquery

            // and boom! the 'null' payoff - we get a pre-formatted list of unused keys => null
            $fields_to_blank = array_diff_assoc($master_fieldmap, $fieldset_fields);
            $assets_updated_count = Asset::whereIn('model_id', $model_ids)->withTrashed()->update($fields_to_blank);
            $this->info('Fieldset "' . $fieldset->name . '" (' . $fieldset->id . ') - updated ' . $assets_updated_count);
            $update_count += $assets_updated_count;
        }

        //finally, we need to handle the 'empty fieldset' models
        $model_ids = AssetModel::select('id')->whereNull('fieldset_id')->withTrashed(); //note: no ->get() because this should end up being a subquery (we hope!)
        $assets_updated_count = Asset::whereIn('model_id', $model_ids)->withTrashed()->update($master_fieldmap);
        $this->info('No Fieldset - updated ' . $assets_updated_count);
        $update_count += $assets_updated_count;
        $this->info("Finished cleaning unreferenced custom fields, total updated assets are: $update_count");
    }
}
