<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateAssetMaintenancesTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('asset_maintenances', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('asset_id')
                      ->unsigned();
                $table->integer('supplier_id')
                      ->unsigned();
                $table->string('asset_maintenance_type');
                $table->string('title', 100);
                $table->boolean('is_warranty');
                $table->date('start_date');
                $table->date('completion_date')
                      ->nullable();
                $table->integer('asset_maintenance_time')
                      ->nullable();
                $table->longText('notes')
                      ->nullable();
                $table->decimal('cost', 10, 2)
                      ->nullable();
                $table->dateTime('deleted_at')
                      ->nullable();
                $table->timestamps();
            });
        }

        protected function getEnumFields()
        {
            return [
                trans('admin/maintenances/general.maintenance'),
                trans('admin/maintenances/general.repair'),
                trans('admin/maintenances/general.upgrade'),
            ];
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('asset_maintenances');
        }
    }
