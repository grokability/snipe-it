<?php

    use App\Models\Actionlog;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;

    class AddThreadIdToAssetLogsTable extends Migration
    {
        /**
         * currentAssetId
         *
         * @var int
         */
        protected $currentAssetId = null;

        /**
         * currentAssetLogId
         *
         * @var int
         */
        protected $currentAssetLogId = null;

        /**
         * assetLogs
         *
         * @var array
         */
        protected $assetLogs = null;

        /**
         * startOfCurrentThread
         *
         * @var bool
         */
        protected $startOfCurrentThread = true;

        /**
         * threadStartingActionTypes
         *
         * @var array
         */
        protected $threadStartingActionTypes = ['checkout', 'requested'];

        /**
         * threadFinalActionTypes
         *
         * @var array
         */
        protected $threadFinalActionTypes = ['checkin from'];

        /**
         * actionlog
         *
         * @var \Actionlog
         */
        private $actionlog;

        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {

            // if (!Schema::hasColumn('asset_logs', 'thread_id')) {

            //     Schema::table( 'asset_logs', function ( Blueprint $table ) {

            //         $table->integer( 'thread_id' )
            //               ->nullable()
            //               ->default( null );
            //         $table->index( 'thread_id' );
            //     } );
            // }

            // $this->actionlog = new App\Models\Actionlog();
            // $this->assetLogs = $this->actionlog->getListingOfActionLogsChronologicalOrder();

            // foreach ($this->assetLogs as $assetLog) {

            //     if ($this->hasAssetChanged( $assetLog )) {
            //         $this->resetCurrentAssetInformation( $assetLog );
            //     }

            //     if ($this->hasBegunNewChain( $assetLog )) {
            //         $this->startOfCurrentThread = false;
            //         continue;
            //     }

            //     $this->updateAssetLogWithThreadInformation( $assetLog );

            //     if ($this->hasReachedEndOfChain( $assetLog )
            //     ) {
            //         $this->clearCurrentAssetInformation();
            //     }

            // }
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            // Schema::table('asset_logs', function (Blueprint $table) {
            //     $table->dropIndex('thread_id');
            //     $table->dropColumn('thread_id');
            // });
        }

        /**
         * hasAssetChanged
         *
         * @param $assetLog
         *
         * @return bool
         * @author  Vincent Sposato <vincent.sposato@gmail.com>
         * @version v1.0
         */
        protected function hasAssetChanged($assetLog)
        {
            return $assetLog->asset_id !== $this->currentAssetId;
        }

        /**
         * resetCurrentAssetInformation
         *
         * @param $assetLog
         *
         * @author  Vincent Sposato <vincent.sposato@gmail.com>
         * @version v1.0
         */
        protected function resetCurrentAssetInformation($assetLog)
        {
            $this->currentAssetId = $assetLog->asset_id;
            $this->currentAssetLogId = $assetLog->id;
            $this->startOfCurrentThread = true;
        }

        /**
         * hasReachedEndOfChain
         *
         * @param $assetLog
         *
         * @return bool
         * @author  Vincent Sposato <vincent.sposato@gmail.com>
         * @version v1.0
         */
        protected function hasReachedEndOfChain($assetLog)
        {
            return in_array($assetLog->action_type, $this->threadFinalActionTypes)
                   && $this->startOfCurrentThread == false;
        }

        /**
         * clearCurrentAssetInformation
         * @author  Vincent Sposato <vincent.sposato@gmail.com>
         * @version v1.0
         */
        protected function clearCurrentAssetInformation()
        {
            $this->startOfCurrentThread = true;
            $this->currentAssetLogId = null;
            $this->currentAssetId = null;
        }

        /**
         * updateAssetLogWithThreadInformation
         *
         * @param $assetLog
         *
         * @author  Vincent Sposato <vincent.sposato@gmail.com>
         * @version v1.0
         */
        protected function updateAssetLogWithThreadInformation($assetLog)
        {
            $loadedAssetLog = Actionlog::find($assetLog->id);

            $loadedAssetLog->thread_id = $this->currentAssetLogId;

            $loadedAssetLog->update();

            unset($loadedAssetLog);
        }

        /**
         * hasBegunNewChain
         *
         * @param $assetLog
         *
         * @return bool
         * @author  Vincent Sposato <vincent.sposato@gmail.com>
         * @version v1.0
         */
        protected function hasBegunNewChain($assetLog)
        {
            return in_array($assetLog->action_type, $this->threadStartingActionTypes)
                   && $this->startOfCurrentThread == true;
        }
    }
