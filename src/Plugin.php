<?php

namespace OfficeGest;

use OfficeGest\Controllers\Documents;
use OfficeGest\Controllers\ProductsList;
use OfficeGest\Controllers\SyncEcoautoForceClearParts;
use WC_Countries;
use WC_Order;

/**
 * Main constructor
 * Class Plugin
 * @package OfficeGest
 */
class Plugin
{
    /**
     * Plugin constructor.
     */
    public function __construct()
    {
        $this->defines();
        $this->actions();
        $this->crons();
    }

    /**
     * Create actions
     */
    private function actions()
    {
	    new Menus\Admin($this);
	    new Hooks\OrderView($this);
	    new Hooks\OrderPaid($this);
	    new Hooks\ProductView($this);
	    new Hooks\ProductList($this);
	    new Ajax($this);
	    new Nif($this);
	    new PickingPoints($this);
    }

    /**
     * Setting up the crons if needed
     */
    private function crons()
    {
	    add_filter( 'woocommerce_rest_check_permissions', '__return_true' );
        add_filter('cron_schedules', '\OfficeGest\Crons::addCronInterval');

        add_action('officegestProductsSync', '\OfficeGest\Crons::productsSync');

        if (!wp_next_scheduled('officegestProductsSync')) {
            wp_schedule_event(time(), 'hourly', 'officegestProductsSync');
        }


        /**
         * Schedule ecoauto parts images import
         */
        add_action('syncImages', '\OfficeGest\Crons::syncImages');
        if (!wp_next_scheduled('syncImages')) {
            wp_schedule_event(time(), 'hourly', 'syncImages');
        }
        /**
         * Schedule sync jobs in queue (officegest_cron_jobs)
         */
        add_action('SyncOfficeGestQueue', '\OfficeGest\Crons::SyncOfficeGestQueue');
        if (!wp_next_scheduled('SyncOfficeGestQueue')) {
            wp_schedule_event(time(), 'every_minutes', 'SyncOfficeGestQueue');
        }
        /**
         * Schedule ecoauto processes import
         */
        add_action('syncEcoautoProcesses', '\OfficeGest\Crons::syncEcoautoProcesses');
        if (!wp_next_scheduled('syncEcoautoProcesses')) {
            wp_schedule_event(time(), 'daily', 'syncEcoautoProcesses');
        }
        /**
         * Schedule ecoauto parts import
         */
        add_action('syncEcoautoParts', '\OfficeGest\Crons::syncEcoautoParts');
        if (!wp_next_scheduled('syncEcoautoParts')) {
            wp_schedule_event(time(), 'daily', 'syncEcoautoParts');
        }
        /**
         * Schedule officegest articles import
         */
        add_action('syncOfficeGestArticles', '\OfficeGest\Crons::syncOfficeGestArticles');
        if (!wp_next_scheduled('syncOfficeGestArticles')) {
            wp_schedule_event(time(), 'daily', 'syncOfficeGestArticles');
        }

        /**
         * Schedule sync jobs in queue (officegest_cron_jobs)
         */
        add_action('SyncEcoautoForceClearParts', '\OfficeGest\Crons::SyncEcoautoForceClearParts');
        if (!wp_next_scheduled('SyncEcoautoForceClearParts')) {
            wp_schedule_event(time(), 'hourly', 'SyncEcoautoForceClearParts');
        }
    }

    /**
     * Define some table params
     * Load scripts and CSS as needed
     */
    private function defines()
    {
	    add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_global_scripts' ) );
	    if (isset($_REQUEST['page']) && !wp_doing_ajax() && sanitize_text_field($_REQUEST['page']) === 'officegest') {
		    add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts_and_styles' ) );
	    };
	    if (isset($_REQUEST['page']) && !wp_doing_ajax() && sanitize_text_field($_REQUEST['page']) === 'ecoauto') {
		    add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts_and_styles' ) );
	    };
    }

    /**
     * Register scripts and styles
     */
    public function admin_register_scripts_and_styles(){
	    wp_enqueue_style('jquery-modal', plugins_url('assets/external/jquery.modal.min.css', OFFICEGEST_PLUGIN_FILE));
	    wp_enqueue_script('jquery-modal', plugins_url('assets/external/jquery.modal.min.js', OFFICEGEST_PLUGIN_FILE));
	    wp_enqueue_style('officegest-styles', plugins_url('assets/css/officegest.css', OFFICEGEST_PLUGIN_FILE));
	    wp_enqueue_script('officegest-scripts',  plugins_url( 'assets/js/officegest.js', OFFICEGEST_PLUGIN_FILE), array( 'jquery', 'select2') );
	    wp_localize_script( 'officegest-scripts', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
	    wp_enqueue_script('officegest-actions-bulk-documentes-js', plugins_url('assets/js/BulkDocuments.js', OFFICEGEST_PLUGIN_FILE),array( 'jquery'));
	    wp_enqueue_style( 'officegest-data-tables', plugins_url('assets/css/jquery.dataTables.css', OFFICEGEST_PLUGIN_FILE), OFFICEGEST_PLUGIN_FILE );
	    wp_enqueue_style( 'officegest-data-tables-select',  plugins_url('assets/css/select.dataTables.css', OFFICEGEST_PLUGIN_FILE), OFFICEGEST_PLUGIN_FILE );
	    wp_enqueue_script( 'officegest-datatables',plugins_url('assets/js/jquery.dataTables.js', OFFICEGEST_PLUGIN_FILE), array('jquery') );
	    wp_enqueue_script( 'officegest-datatables-select', plugins_url('assets/js/datatables.select.js', OFFICEGEST_PLUGIN_FILE), array('jquery') );
    }

    /**
     * Register global scripts
     */
	public function admin_register_global_scripts(){
		wp_enqueue_style('officegest-select2',  plugins_url( 'assets/css/select2.css', OFFICEGEST_PLUGIN_FILE));
		wp_enqueue_script('officegest-select2', plugins_url( 'assets/js/select2.js', OFFICEGEST_PLUGIN_FILE), array('jquery') );
		wp_enqueue_script('officegest-scripts',  plugins_url( 'assets/js/global.js', OFFICEGEST_PLUGIN_FILE), array( 'jquery', 'select2') );
	}

	/**
     * Main function
     * This will run when accessing the page "officegest" and the routing shoud be done here with and $_GET['action']
     */
    public function run()
    {
        try {
            /** If the user is not logged in show the login form */
            if (Start::login(false)) {
	            $action = isset($_REQUEST['action']) ? sanitize_text_field(strtolower($_REQUEST['action'])) : '';
                switch ($action) {
                    case 'reminvoice':
                        $this->removeOrder((int)$_GET['id']);
                        break;

	                case 'send_article_og':
	                	$this->send_to_og_admin_notice();
	                	break;

                    case 'reminvoiceall':
                        $this->removeOrdersAll();
                        break;

                    case 'geninvoice':
	                    $orderId = (int)sanitize_text_field($_REQUEST['id']);
	                    /** @var Documents $document intended */
	                    /** @noinspection PhpUnusedLocalVariableInspection */
	                    $document = $this->createDocument($orderId);
                        break;

	                case 'remlogs':
		                Log::removeLogs();
		                add_settings_error('officegest', 'officegest-rem-logs', __('A limpeza de logs foi concluída.'), 'updated');
		                break;
	                case 'synctaxes':
		                $this->syncTaxes();
		                break;
	                case 'syncstocks':
		                $this->syncStocks();
		                break;
	                case 'syncfamiliestoog':
		                $this->syncFamiliestoOG();
		                break;
	                case 'syncofficegestarticlefamilies':
		                $this->syncOfficeGestArticleFamilies();
		                break;
	                case 'syncarticles':
		                $this->syncArticles();
		                break;
	                case 'syncarticlesfromog':
	                	$this->syncDB();
	                	break;
	                case 'getinvoice':
		                $document = false;
		                $documentId = (int)sanitize_text_field($_REQUEST["id"]);
		                $document = Documents::showDocument($documentId);
		                break;
	                case 'deubug':
		                include OFFICEGEST_TEMPLATE_DIR . 'DEBUG.php';
		                die();
		                break;

                    case 'forcesyncarticles':
                        $this->forceSyncArticles();
                        break;
                    case 'syncarticleimages':
                        $this->syncArticleImages();
                        break;
                    case 'runofficegestqueue':
                        $this->forceOfficegestQueue();
                        break;


                }
	            if (!wp_doing_ajax()) {
		            include OFFICEGEST_TEMPLATE_DIR . 'MainContainer.php';
	            }
            }
        } catch (Error $error) {
            $error->showError();
        }
    }

    /**
     * This will run when accessing the page "ecoauto" and the routing shoud be done here with and $_GET['action']
     */
	public function run_ecoauto()
	{
		try {
			if (Start::login(false)) {
				$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( strtolower( $_REQUEST['action'] ) ) : '';
				switch ($action) {

					case 'forcesyncparts':
						$this->forceSyncParts();
						break;

                    case 'forcesyncprocesses':
                        $this->forceSyncProcesses();
                        break;

					case 'syncimages':
						$this->syncEcoautoPartsImages();
						break;

                    case 'forcesyncpartsinventory':
                        $this->forceSyncPartsInventory();
                        break;

                    case 'syncclearparts':
                        $this->forceEcoautoClearParts();
                        break;

					case 'deubug':
						include OFFICEGEST_TEMPLATE_DIR . 'DEBUG.php';
						die();
						break;
				}
				if (!wp_doing_ajax()) {
					include OFFICEGEST_TEMPLATE_DIR . 'EcoAutoContainer.php';
				}
			}
		}
		catch (Error $error) {
			$error->showError();
		}
	}

    /**
     * @param $orderId
     * @return Documents
     * @throws Error|\ErrorException
     */
    private function createDocument($orderId)
    {
        $document = new Documents($orderId);
        $document->createDocument();
        if ($document->document_id) {
            $viewUrl = ' <a href="' . admin_url("admin.php?page=officegest&action=getInvoice&id=" . $orderId) . '" target="_BLANK">Ver documento</a>';
            add_settings_error('officegest', 'officegest-document-created-success', __('O documento foi gerado!') . $viewUrl, 'updated');
        }

        return $document;
    }

    /**
     * @param int $orderId
     */
    private function removeOrder($orderId)
    {
	    if (isset($_GET['confirm']) && sanitize_text_field($_GET['confirm']) === 'true') {
		    add_post_meta($orderId, '_officegest_sent', '-1', true);
		    delete_post_meta($orderId,'_officegest_doctype');
		    delete_post_meta($orderId,'_officegest_docstatus');
		    add_settings_error('officegest', 'officegest-order-remove-success', __('A encomenda ' . $orderId . ' foi marcada como gerada!'), 'updated');
	    } else {
		    add_settings_error(
			    'officegest',
			    'officegest-order-remove',
			    __('Confirma que pretende marcar a encomenda ' . $orderId . " como paga? <a href='" . admin_url('admin.php?page=officegest&action=remInvoice&confirm=true&id=' . $orderId) . "'>Sim confirmo!</a>")
		    );
	    }
    }



	private function syncFamiliesToOG()
	{
		ini_set( 'memory_limit',-1);
		ini_set( 'max_execution_time',-1);
		$syncFamiliesResult = (new Controllers\SyncFamiliesToOG())->run();
		if ($syncFamiliesResult->getCreated() > 0) {
			add_settings_error('officegest', 'officegest-sync-families-updated', __('Foram criados ' . $syncFamiliesResult->getCreated() . " familias."), 'familias');
		}

		if ($syncFamiliesResult->countEqual() > 0) {
			add_settings_error('officegest', 'officegest-sync-families-equal', __('Existem ' . $syncFamiliesResult->countEqual() . ' familias iguais.' ), 'updated');
		}

		if ($syncFamiliesResult->countFoundRecord() > 0) {
			add_settings_error('officegest', 'officegest-sync-families-not-found', __('Foram encontrados no WooCommerce ' . $syncFamiliesResult->countFoundRecord() . ' artigos.' ));
		}

	}

	private function syncStocks()
	{
		ini_set( 'memory_limit',-1);
		ini_set( 'max_execution_time',-1);
		Log::write('A sincronizar stock artigos');
		$syncStocksResult = (new Controllers\SyncStocks())->run();

		if ($syncStocksResult->countUpdated() > 0) {
			add_settings_error('officegest', 'officegest-sync-products-updated', __('Foram actualizados ' . $syncStocksResult->countUpdated() . " artigos."), 'updated');
		}

		if ($syncStocksResult->countEqual() > 0) {
			add_settings_error('officegest', 'officegest-sync-products-updated', __('Existem ' . $syncStocksResult->countEqual() . " artigos com stock igual."), 'updated');
		}

		if ($syncStocksResult->countNotFound() > 0) {
			add_settings_error('officegest', 'officegest-sync-products-not-found', __('Não foram encontrados no WooCommerce ' . $syncStocksResult->countNotFound() . " artigos."));
		}
	}

	private function syncArticles()
	{
		ini_set( 'memory_limit',-1);
		ini_set( 'max_execution_time',-1);
		Log::write('A sincronizar produtos');
		$syncStocksResult = (new Controllers\SyncArticles())->run();
		if ($syncStocksResult->countFoundRecord() > 0) {
			add_settings_error('officegest', 'officegest-sync-products-updated', __('Foram encontrados ' . $syncStocksResult->countFoundRecord() . ' artigos.' ), 'updated');
		}
		if ($syncStocksResult->countUpdated() > 0) {
			add_settings_error('officegest', 'officegest-sync-products-updated', __('Foram actualizados ' . $syncStocksResult->countUpdated() . ' artigos igual.' ), 'updated');
		}
		if ($syncStocksResult->countCreated() > 0) {
			add_settings_error('officegest', 'officegest-sync-products-not-found', __('Foram criados no WooCommerce ' . $syncStocksResult->countCreated() . ' artigos.' ));
		}
	}

	public function send_to_og_admin_notice() {
			$total      = $_REQUEST['count'];
			$created    = $_REQUEST['created'];
			$updated    = $_REQUEST['updated'];
			$with_error = $_REQUEST['error'];
			Notice::addMessageCustom(printf(_n( 'Foi enviado %s artigos para o Officegest.',
				        'Foram enviados %s artigos  para o Officegest.',
				        $total,
				        'officegest' ) , $total));
			if ( $created > 0 ) {
				Notice::addMessageSuccess(printf(
				        _n( 'Foi criado %s artigo  no Officegest.',
					        'Foram criados %s artigos no Officegest.',
					        $created,
					        'officegest'
				        ), $created ));
			}
			if ( $updated > 0 ) {
				Notice::addMessageSuccess(printf(
				        _n( 'Foi actualizado %s artigo  no Officegest.',
					        'Foram actualizados %s artigos  no Officegest.',
					        $updated,
					        'officegest'
				        ) , $updated ));
			}
			if ( $with_error > 0 ) {
				Notice::addMessageError(printf(
				        _n( 'Ocurreu um erro em %s artigo  no Officegest.',
					        'Ocurreu um erro em %s artigos no Officegest.',
					        $with_error,
					        'officegest'
				        ) . $with_error ));
			}
    }

	private function syncDB() {
		ini_set( 'memory_limit',-1);
		ini_set( 'max_execution_time',-1);
		Log::write('A sincronizar DB');
		$data = Tools::updateArticles();
		if ($data['updated'] > 0) {
			add_settings_error('officegest', 'officegest-sync-products-updated', __('Foram actualizados ' . $data['updated'] . ' artigos.' ), 'updated');
		}

		if ($data['inserted'] > 0) {
			add_settings_error('officegest', 'officegest-sync-products-not-found', __('Foram inseridos ' . $data['inserted'] . ' artigos.' ));
		}
	}












    /**
     * Sync article families from officegest
     */
    private function syncOfficeGestArticleFamilies() {
        ini_set( 'memory_limit',-1);
        ini_set( 'max_execution_time',-1);
        $syncFamiliesResult = (new Controllers\SyncOfficeGestArticleFamilies())->run();
        if ($syncFamiliesResult->countCreated() > 0) {
            add_settings_error('officegest', 'officegest-sync-families-woo-updated', __('Foram criadas ' . $syncFamiliesResult->getCreated() . ' familias.' ), 'updated');
        }
        if ($syncFamiliesResult->countUpdated() > 0) {
            add_settings_error('officegest', 'officegest-sync-families-created', __('Foram actualizadas ' . $syncFamiliesResult->getUpdated() . ' familias.' ),'updated');
        }
    }

    /**
     * Sync ecoauto parts images
     *
     * @throws \ErrorException
     */
    private function syncEcoautoPartsImages() {
        ini_set( 'memory_limit',-1);
        ini_set( 'max_execution_time',-1);
        $syncImages = (new Controllers\SyncEcoautoImages())->run();
        if ($syncImages->countFoundRecord() > 0) {
            add_settings_error('officegest', 'officegest-sync-pecas-woo-updated', __('Foram criadas ' . $syncImages->countFoundRecord() . ' imagens.' ), 'updated');
        }
    }

    /**
     * Remove pending orders
     */
    private function removeOrdersAll()
    {
        if (isset($_GET['confirm']) && sanitize_text_field($_GET['confirm']) === 'true') {
            $allOrders = Controllers\PendingOrders::getAllAvailable();
            if (!empty($allOrders) && is_array($allOrders)) {
                foreach ($allOrders as $order) {
                    add_post_meta($order['id'], '_officegest_sent', '-1', true);
                    delete_post_meta($order['id'],'_officegest_doctype');
                    delete_post_meta($order['id'],'_officegest_docstatus');
                }
                add_settings_error('officegest', 'officegest-order-all-remove-success', __('Todas as encomendas foram marcadas como geradas!'), 'updated');
            } else {
                add_settings_error('officegest', 'officegest-order-all-remove-not-found', __('Não foram encontradas encomendas por gerar'));
            }
        } else {
            add_settings_error(
                'officegest', 'officegest-order-remove', __("Confirma que pretende marcar todas as encomendas como já geradas? <a href='" . admin_url('admin.php?page=officegest&action=remInvoiceAll&confirm=true') . "'>Sim confirmo!</a>")
            );
        }
    }

    /**
     * Sync taxes from  officegest to woocommerce
     */
    private function syncTaxes() {
        ini_set( 'memory_limit',-1);
        ini_set( 'max_execution_time',-1);
        Log::write('A sincronizar Taxas');
        $taxas = (new Controllers\SyncTaxes())->run();
        if ($taxas->countFoundRecord() > 0) {
            add_settings_error('officegest', 'officegest-sync-products-updated', __('Foram actualizados ' . $taxas->countFoundRecord() . " taxas de IVA."), 'updated');
        }
        if ($taxas->countUpdated() > 0) {
            add_settings_error('officegest', 'officegest-sync-products-updated', __('Foram actualizados ' . $taxas->countUpdated() . " taxas de IVA."), 'updated');
        }
        if ($taxas->countNotFound() > 0) {
            add_settings_error('officegest', 'officegest-sync-products-not-found', __('Não foram encontrados no WooCommerce ' . $taxas->countNotFound() . " taxas."));
        }
    }

    /**
     * Force to run officegest ecoauto parts sync
     */
    private function forceSyncParts() {
        ini_set( 'memory_limit',-1);
        ini_set( 'max_execution_time',-1);
        $forceSyncParts = (new Controllers\SyncEcoautoParts())->run();
        if ($forceSyncParts->countFoundRecord() > 0) {
            add_settings_error('officegest', 'officegest-sync-pecas-woo-updated', __('Foram criadas ' . $forceSyncParts->countFoundRecord() . ' peças.' ), 'updated');
        }
    }

    /**
     * Force to sync woocommerce part with ecoauto last inventory
     */
    private function forceSyncPartsInventory() {
        ini_set( 'memory_limit',-1);
        ini_set( 'max_execution_time',-1);
        $forceSyncParts = (new Controllers\SyncEcoautoPartsInventory())->run();
        if ($forceSyncParts->countFoundRecord() > 0) {
            add_settings_error('officegest', 'officegest-sync-pecas-woo-updated', __('Foram criadas ' . $forceSyncParts->countFoundRecord() . ' peças.' ), 'updated');
        }
    }

    /**
     * Force to sync woocommerce part with ecoauto last inventory
     */
    private function forceEcoautoClearParts() {
        ini_set( 'memory_limit',-1);
        ini_set( 'max_execution_time',-1);
        $forceClearParts = (new Controllers\SyncEcoautoForceClearParts())->run();
        if ($forceClearParts->countFoundRecord() > 0) {
            add_settings_error('officegest', 'officegest-sync-pecas-woo-updated', __('Foram limpas ' . $forceClearParts->countFoundRecord() . ' peças.' ), 'updated');
        }
    }

    /**
     * Force to run officegest ecoauto processes sync
     */
    private function forceSyncProcesses() {
        ini_set( 'memory_limit',-1);
        ini_set( 'max_execution_time',-1);
        $forceSyncProcesses = (new Controllers\SyncEcoautoProcesses())->run();
        if ($forceSyncProcesses->countFoundRecord() > 0) {
            add_settings_error('officegest', 'officegest-sync-pecas-woo-updated', __('Foram criadas ' . $forceSyncProcesses->countFoundRecord() . ' viaturas.' ), 'updated');
        }
    }

    /**
     * Force to run officegest articles sync
     */
    private function forceSyncArticles() {
        ini_set( 'memory_limit',-1);
        ini_set( 'max_execution_time',-1);
        $forceSyncArticles = (new Controllers\SyncOfficeGestArticles())->run();
        if ($forceSyncArticles->countFoundRecord() > 0) {
            add_settings_error('officegest', 'officegest-sync-pecas-woo-updated', __('Foram criados ' . $forceSyncArticles->countFoundRecord() . ' artigos.' ), 'updated');
        }
    }

    /**
     * Force to run officegest articles sync
     */
    private function syncArticleImages() {
        ini_set( 'memory_limit',-1);
        ini_set( 'max_execution_time',-1);
        $forceSyncArticleImagess = (new Controllers\SyncOfficegestArticleImages())->run();
        if ($forceSyncArticleImagess->countFoundRecord() > 0) {
            add_settings_error('officegest', 'officegest-sync-pecas-woo-updated', __('Foram criados ' . $forceSyncArticleImagess->countFoundRecord() . ' imagens.' ), 'updated');
        }
    }

    /**
     * Force to run officegest queue
     */
    private function forceOfficegestQueue() {
        ini_set( 'memory_limit',-1);
        ini_set( 'max_execution_time',-1);
        $forceQueue = (new Controllers\SyncOfficeGestQueue())->run();
        if ($forceQueue->countFoundRecord() > 0) {
            add_settings_error('officegest', 'officegest-force-queue', __('Foram corridos ' . $forceQueue->countFoundRecord() . ' eventos.' ), 'updated');
        }
    }
}
