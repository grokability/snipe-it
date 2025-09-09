<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MoveUploadsToNewDisk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:move-uploads {delete_local?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will move your locally uploaded files to whatever your current disk is.';

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
     * @return mixed
     */
    public function handle()
    {
        if (config('filesystems.default') == 'local') {
            $this->error('Your current disk is set to local so we cannot proceed.');
            $this->warn("Please configure your .env settings for S3. \nChange your PUBLIC_FILESYSTEM_DISK value to 's3_public' and your PRIVATE_FILESYSTEM_DISK to s3_private.");

            return false;
        }
        $delete_local = $this->argument('delete_local');

        $public_uploads['accessories'] = glob('public/uploads/accessories'."/*.*");
        $public_uploads['assets'] = glob('public/uploads/assets'."/*.*");
        $public_uploads['avatars'] = glob('public/uploads/avatars'."/*.*");
        $public_uploads['categories'] = glob('public/uploads/categories'."/*.*");
        $public_uploads['companies'] = glob('public/uploads/companies'."/*.*");
        $public_uploads['components'] = glob('public/uploads/components'."/*.*");
        $public_uploads['consumables'] = glob('public/uploads/consumables'."/*.*");
        $public_uploads['departments'] = glob('public/uploads/departments'."/*.*");
        $public_uploads['locations'] = glob('public/uploads/locations'."/*.*");
        $public_uploads['manufacturers'] = glob('public/uploads/manufacturers'."/*.*");
        $public_uploads['suppliers'] = glob('public/uploads/suppliers'."/*.*");
        $public_uploads['assetmodels'] = glob('public/uploads/models'."/*.*");


        // iterate files
        foreach ($public_uploads as $public_type => $public_upload) {
            $type_count = 0;
            $this->info('- There are ' . count($public_upload) . ' PUBLIC ' . $public_type . ' files.');

            for ($i = 0; $i < count($public_upload); $i++) {
                $type_count++;
                $filename = basename($public_upload[$i]);

                try  {
                    Storage::disk('public')->put('uploads/'.$public_type.'/'.$filename, file_get_contents($public_upload[$i]));
                    $new_url = Storage::disk('public')->url('uploads/'.$public_type.'/'.$filename, $filename);
                    $this->info($type_count.'. PUBLIC: '.$filename.' was copied to '.$new_url);
                } catch (\Exception $e) {
                    Log::debug($e);
                    $this->error($e);
                }
            }
        }

        $logos = glob("public/uploads/setting*.*");
        $this->info("- There are ".count($logos).' files that might be logos.');
        $type_count = 0;

        foreach ($logos as $logo) {
            $this->info($logo);
            $type_count++;
            $filename = basename($logo);
            Storage::disk('public')->put('uploads/' . $filename, file_get_contents($logo));
            $this->info($type_count . '. LOGO: ' . $filename . ' was copied to ' . env('PUBLIC_AWS_URL') . '/uploads/' . $filename);
        }

        $private_uploads['assets'] = glob('storage/private_uploads/assets'."/*.*");
        $private_uploads['signatures'] = glob('storage/private_uploads/signatures'."/*.*");
        $private_uploads['audits'] = glob('storage/private_uploads/audits'."/*.*");
        $private_uploads['assetmodels'] = glob('storage/private_uploads/models'."/*.*");
        $private_uploads['imports'] = glob('storage/private_uploads/imports'."/*.*");
        $private_uploads['licenses'] = glob('storage/private_uploads/licenses'."/*.*");
        $private_uploads['users'] = glob('storage/private_uploads/users'."/*.*");
        $private_uploads['backups'] = glob('storage/private_uploads/backups'."/*.*");
        

        foreach ($private_uploads as $private_type => $private_upload) {
            {
                $this->info('- There are ' . count($private_upload) . ' PRIVATE ' . $private_type . ' files.');

                $type_count = 0;
                for ($x = 0; $x < count($private_upload); $x++) {
                    $type_count++;
                    $filename = basename($private_upload[$x]);

                    try {
                        Storage::put($private_type . '/' . $filename, file_get_contents($private_upload[$i]));
                        $new_url = Storage::url($private_type . '/' . $filename, $filename);
                        $this->info($type_count . '. PRIVATE: ' . $filename . ' was copied to ' . $new_url);
                    } catch (\Exception $e) {
                        Log::debug($e);
                        $this->error($e);
                    }
                }
            }


            if ($delete_local == 'true') {
                $public_delete_count = 0;
                $private_delete_count = 0;

                $this->info("\n\n");
                $this->error('!!!!!!!!!!!!!!!!!!!!!!!!!!!!! WARNING!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
                $this->warn("\nTHIS WILL DELETE ALL OF YOUR LOCAL UPLOADED FILES. \n\nThis cannot be undone, so you should take a backup of your system before you proceed.\n");
                $this->error('!!!!!!!!!!!!!!!!!!!!!!!!!!!!! WARNING!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');

                if ($this->confirm('Do you wish to continue?')) {
                    foreach ($public_uploads as $public_type => $public_upload) {
                        for ($i = 0; $i < count($public_upload); $i++) {
                            $filename = $public_upload[$i];
                            try {
                                unlink($filename);
                                $public_delete_count++;
                            } catch (\Exception $e) {
                                Log::debug($e);
                                $this->error($e);
                            }
                        }
                    }

                    foreach ($private_uploads as $private_type => $private_upload) {
                        for ($i = 0; $i < count($private_upload); $i++) {
                            $filename = $private_upload[$i];
                            try {
                                unlink($filename);
                                $private_delete_count++;
                            } catch (\Exception $e) {
                                Log::debug($e);
                                $this->error($e);
                            }
                        }
                    }

                    $this->info($public_delete_count . ' PUBLIC local files and ' . $private_delete_count . ' PRIVATE local files were deleted from your filesystem.');
                }
            }
        }
    }
}
