<?php

namespace OfficeGest;

use Exception;
use OfficeGest\Controllers\SyncEcoautoForceClearParts;
use OfficeGest\Controllers\SyncEcoautoImages;
use OfficeGest\Controllers\SyncOfficeGestArticles;
use OfficeGest\Controllers\SyncEcoautoParts;
use OfficeGest\Controllers\SyncEcoautoProcesses;
use OfficeGest\Controllers\SyncOfficeGestQueue;
use OfficeGest\Controllers\SyncStocks;

/**
 * This crons will run in isolation
 */
class Crons
{
    public static function addCronInterval()
    {
        $schedules['hourly'] = array(
            'interval' => 3000,
            'display' => __('A cada cinquenta minutos')
        );
        $schedules['five_minutes'] = array(
            'interval' => 300,
            'display' => __('A cada cinco minutos')
        );
        $schedules['every_minutes'] = array(
            'interval' => 60,
            'display' => __('A cada minuto')
        );
        return $schedules;
    }

    public static function requires()
    {
        $composer_autoloader = '../vendor/autoload.php';
        if (is_readable($composer_autoloader)) {
            /** @noinspection PhpIncludeInspection */
            require $composer_autoloader;
        }
    }

    /**
     * @global $wpdb
     * @return bool
     */
    public static function productsSync()
    {

        global $wpdb;
        $runningAt = time();
        try {
	        self::requires();
            if (!Start::login()) {
                Log::write("Não foi possível estabelecer uma ligação ao OfficeGest");
                return false;
            }
			$stock_sync = OfficeGestDBModel::getOption('officegest_stock_sync');
            if ($stock_sync==1) {
                Log::write("A iniciar a sincronização de stocks automática...");
                $sync_time = OfficeGestDBModel::getOption('officegest_stock_sync_time');
                if ($sync_time==null) {
	                $sync_time =  (int)(time() - 600);
	                OfficeGestDBModel::setOption('officegest_stock_sync_time',$sync_time);
                }
                (new SyncStocks())->run();
            } else {
                Log::write("Stock sync disabled in plugin settings");

            }

        } catch (Exception $ex) {
            Log::write("Fatal Error: " . $ex->getMessage());
        }
        OfficeGestDBModel::setOption("officegest_stock_sync_time", (int)$runningAt);
        return true;
    }










    /**
     * Sync Cron Jobs in Queue (officegest_cron_jobs)
     *
     */
    public static function SyncOfficeGestQueue(){
        $runningAt = time();
        try {
            self::requires();
            if (!Start::login()) {
                Log::write("Não foi possível estabelecer uma ligação ao OfficeGest");
                return false;
            }
            $sync_time = OfficeGestDBModel::getOption('officegest_queue_time');
            if ($sync_time==null) {
                $sync_time =  (int)(time() - 600);
                OfficeGestDBModel::setOption('officegest_queue_time',$sync_time);
            }
            (new SyncOfficeGestQueue())->run();
        } catch (Exception $ex) {
            Log::write("Fatal Errror: " . $ex->getMessage());
        }
        OfficeGestDBModel::setOption("officegest_queue_time", (int)$runningAt);
    }

    /**
     * Sync OfficeGest articles
     *
     * @return false
     */
    public static function syncOfficeGestArticles(){
        $runningAt = time();
        try {
            self::requires();
            if (!Start::login()) {
                Log::write("Não foi possível estabelecer uma ligação ao OfficeGest");
                return false;
            }
            $item_sync = OfficeGestDBModel::getOption('officegest_sync_articles');
            if ($item_sync==1) {
                $sync_time = OfficeGestDBModel::getOption('officegest_articles_time');
                if ($sync_time==null) {
                    $sync_time =  (int)(time() - 600);
                    OfficeGestDBModel::setOption('officegest_articles_time',$sync_time);
                }
                (new SyncOfficeGestArticles())->run();
            } else {
                Log::write("Sincronizacação de Artigos desactivada no plugin");
            }
        } catch (Exception $ex) {
            Log::write("Fatal Errror: " . $ex->getMessage());
        }
        OfficeGestDBModel::setOption("officegest_articles_time", (int)$runningAt);
    }

    /**
     * Sync Ecoauto parts
     *
     * @return false
     */
    public static function syncEcoautoParts(){
        $runningAt = time();
        try {
            self::requires();
            if (!Start::login()) {
                Log::write("Não foi possível estabelecer uma ligação ao OfficeGest");
                return false;
            }
            $item_sync = OfficeGestDBModel::getOption('ecoauto_sync_parts');
            if ($item_sync==1) {
                $sync_time = OfficeGestDBModel::getOption('ecoauto_parts_time');
                if ($sync_time==null) {
                    $sync_time =  (int)(time() - 600);
                    OfficeGestDBModel::setOption('ecoauto_parts_time',$sync_time);
                }
                (new SyncEcoautoParts())->run();
            } else {
                Log::write("Sincronizacação de Peças desactivada no plugin");
            }
        } catch (Exception $ex) {
            Log::write("Fatal Errror: " . $ex->getMessage());
        }
        OfficeGestDBModel::setOption("ecoauto_parts_time", (int)$runningAt);
    }

    /**
     * Sync Ecoauto processes
     *
     * @return false
     */
    public static function syncEcoautoProcesses(){
        $runningAt = time();
        try {
            self::requires();
            if (!Start::login()) {
                Log::write("Não foi possível estabelecer uma ligação ao OfficeGest");
                return false;
            }
            $item_sync = OfficeGestDBModel::getOption('ecoauto_sync_processes');
            if ($item_sync==1) {
                $sync_time = OfficeGestDBModel::getOption('ecoauto_processes_time');
                if ($sync_time==null) {
                    $sync_time =  (int)(time() - 600);
                    OfficeGestDBModel::setOption('ecoauto_processes_time',$sync_time);
                }
                (new SyncEcoautoProcesses())->run();
            } else {
                Log::write("Sincronizacação de Viaturas desactivada no plugin");
            }
        } catch (Exception $ex) {
            Log::write("Fatal Errror: " . $ex->getMessage());
        }
        OfficeGestDBModel::setOption("ecoauto_processes_time", (int)$runningAt);
    }

    /**
     * Sync Ecoauto parts images
     *
     * @return false
     */
    public static function syncImages(){
        global $wpdb;
        $runningAt = time();
        try {
            self::requires();
            if (!Start::login()) {
                Log::write("Não foi possível estabelecer uma ligação ao OfficeGest");
                return false;
            }
            $stock_sync = OfficeGestDBModel::getOption('ecoauto_sync_imagens');
            if ($stock_sync==1) {
                $sync_time = OfficeGestDBModel::getOption('ecoauto_imagens_time');
                if ($sync_time==null) {
                    $sync_time =  (int)(time() - 600);
                    OfficeGestDBModel::setOption('ecoauto_imagens_time',$sync_time);
                }
                (new SyncEcoautoImages())->run();
            } else {
                Log::write("Sincronizacação de Imagens desactivada no plugin");
            }
        } catch (Exception $ex) {
            Log::write("Fatal Errror: " . $ex->getMessage());
        }
        OfficeGestDBModel::setOption("ecoauto_imagens_time", (int)$runningAt);
    }

    /**
     * Force clear parts function (officegest_cron_jobs)
     *
     */
    public static function SyncEcoautoForceClearParts(){
        $runningAt = time();
        try {
            self::requires();
            if (!Start::login()) {
                Log::write("Não foi possível estabelecer uma ligação ao OfficeGest");
                return false;
            }
            $sync_time = OfficeGestDBModel::getOption('officegest_clear_parts_time');
            if ($sync_time==null) {
                $sync_time =  (int)(time() - 600);
                OfficeGestDBModel::setOption('officegest_clear_parts_time',$sync_time);
            }
            (new SyncEcoautoForceClearParts())->run();
        } catch (Exception $ex) {
            Log::write("Fatal Errror: " . $ex->getMessage());
        }
        OfficeGestDBModel::setOption("officegest_clear_parts_time", (int)$runningAt);
    }
}