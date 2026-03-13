<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use \App\Models\User;


class CreateAdmin extends Command
{

    /** @mixin User **/
    /**
     * App\Console\CreateAdmin
     * @property mixed $first_name
     * @property string $last_name
     * @property string $username
     * @property string $email
     * @property string $permissions
     * @property string $password
     * @property boolean $activated
     * @property boolean $show_in_list
     * @property boolean $autoassign_licenses
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property mixed $created_by
     */



    protected $signature = 'snipeit:create-admin {--first_name=} {--last_name=}  {--email=}  {--username=}  {--password=} {show_in_list?} {autoassign_licenses?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user via command line.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $first_name = $this->option('first_name');
        $last_name = $this->option('last_name');
        $username = $this->option('username');
        $email = $this->option('email');
        $password = $this->option('password');
        $show_in_list = $this->argument('show_in_list');
        $autoassign_licenses = $this->argument('autoassign_licenses');



        if (($first_name == '') || ($last_name == '') || ($username == '') || ($email == '') || ($password == '')) {
            $this->info('ERROR: All fields are required.');
        } else {
            $user = new User;
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            $user->username = $username;
            $user->email = $email;
            $user->permissions = '{"admin":1,"user":1,"superuser":1,"reports.view":1, "licenses.keys":1}';
            $user->password = bcrypt($password);
            $user->activated = 1;

            if ($show_in_list == 'false') {
                $user->show_in_list = 0;
            }

            if ($autoassign_licenses == 'false') {
                $user->autoassign_licenses = 0;
            }

            if ($user->save()) {
                $this->info('New user created');
                $user->groups()->attach(1);
            } else {
                $this->info('Admin user was not created');
                $errors = $user->getErrors();

                foreach ($errors->all() as $error) {
                    $this->info('ERROR:'.$error);
                }
            }
        }
    }
}
