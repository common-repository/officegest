<?php

namespace OfficeGest\Hooks;

use Exception;
use OfficeGest\Controllers\Product;
use OfficeGest\Error;
use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Plugin;
use OfficeGest\Start;
use OfficeGest\Tools;
use WC_Product;

/**
 * Class OrderView
 * Add a OfficeGest Windows to when user is in the product view
 * @package OfficeGest\Hooks
 */
class ProductView
{
    public $parent;

    /** @var WC_Product */
    public $product;

    /** @var Product */
    public $officegestProduct;

	public $settings = array();

    private $allowedPostTypes = ["product"];

    /**
     * @param Plugin $parent
     */
    public function __construct($parent)
    {
        $this->parent = $parent;
        add_action('add_meta_boxes', [$this, 'officegest_add_meta_box']);
        if (OfficeGestDBModel::getOption('campos_extra_ficha_artigos')==1){
	        add_action('woocommerce_product_options_sku', array( $this,'custom_product_fields') );
	        add_action('woocommerce_process_product_meta', array( $this, 'woocommerce_process_product_meta' ) );
        }
	    if (OfficeGestDBModel::getOption('officegest_ecoauto')==1) {
		    add_filter( 'woocommerce_product_data_tabs', array( $this, 'custom_ecoauto_tabs') );
		    add_filter( 'woocommerce_product_data_panels', array( $this, 'ecoauto_options_product_tab_content') );
	    }

    }

	public function custom_ecoauto_tabs(  $original_tabs) {
		$new_tab['ecoauto'] = array(
			'label'		=> __( 'EcoAuto', 'officegest' ),
			'target'	=> 'ecoauto_options',
			'class'		=> array( 'show_if_simple', 'show_if_variable'  ),
		);

		$insert_at_position = 2; // This can be changed
		$tabs = array_slice( $original_tabs, 0, $insert_at_position, true ); // First part of original tabs
		$tabs = array_merge( $tabs, $new_tab ); // Add new
		$tabs = array_merge( $tabs, array_slice( $original_tabs, $insert_at_position, null, true ) ); // Glue the second part of original

		return $tabs;
	}

	/**
	 * Contents of the gift card options product tab.
	 */
	public function ecoauto_options_product_tab_content() {

		global $post;

		// Note the 'id' attribute needs to match the 'target' parameter set above
		?><div id='ecoauto_options' class='panel woocommerce_options_panel'>
        <div class='options_group'><?php
	    woocommerce_wp_hidden_input(
	            array( 'id'	=> '_ecoauto','value'=>1)
        );
        woocommerce_wp_hidden_input(
	        array( 'id'				=> '_ecoauto_brand_id' )
        );
		woocommerce_wp_hidden_input(
			array( 'id'				=> '_ecoauto_model_id' )
		);
		woocommerce_wp_hidden_input(
			array( 'id'				=> '_ecoauto_version_id' )
		);
		woocommerce_wp_hidden_input(
			array( 'id'				=> '_ecoauto_fuel_id' )
		);
		woocommerce_wp_text_input( array(
			'id'				=> '_ecoauto_brand',
			'label'				=> __( 'Marca', 'officegest' ),
			'desc_tip'			=> 'true',
			'description'		=> __( 'Marca', 'officegest' ),
			'type' 				=> 'text',
			'custom_attributes'	=> array(
				'readonly'	=> 'readonly'
			),
		) );
        woocommerce_wp_text_input( array(
            'id'				=> '_ecoauto_codoem',
            'label'				=> __( 'OEM', 'officegest' ),
            'desc_tip'			=> 'true',
            'description'		=> __( 'OEM', 'officegest' ),
            'type' 				=> 'text',
            'custom_attributes'	=> array(
                'readonly'	=> 'readonly'
            ),
        ) );
		woocommerce_wp_text_input( array(
			'id'				=> '_ecoauto_model',
			'label'				=> __( 'Modelo', 'officegest' ),
			'desc_tip'			=> 'true',
			'description'		=> __( 'Modelo', 'officegest' ),
			'type' 				=> 'text',
			'custom_attributes'	=> array(
				'readonly'	=> 'readonly',
                'size'=>6,
			),
		) );
		woocommerce_wp_text_input( array(
			'id'				=> '_ecoauto_version',
			'label'				=> __( 'Versão', 'officegest' ),
			'desc_tip'			=> 'true',
			'description'		=> __( 'Versão', 'officegest' ),
			'type' 				=> 'text',
			'custom_attributes'	=> array(
				'readonly'	=> 'readonly',
				'size'=>6
			),
		) );
		woocommerce_wp_text_input( array(
			'id'				=> '_ecoauto_year',
			'label'				=> __( 'Ano', 'officegest' ),
			'desc_tip'			=> 'true',
			'description'		=> __( 'Ano', 'officegest' ),
			'type' 				=> 'text',
			'custom_attributes'	=> array(
				'readonly'	=> 'readonly'
			),
		) );
		woocommerce_wp_text_input( array(
			'id'				=> '_ecoauto_fuel',
			'label'				=> __( 'Combustivél', 'officegest' ),
			'desc_tip'			=> 'true',
			'description'		=> __( 'Combustivél', 'officegest' ),
			'type' 				=> 'text',
			'custom_attributes'	=> array(
				'readonly'	=> 'readonly'
			),
		) );
		woocommerce_wp_text_input( array(
			'id'				=> '_ecoauto_type_desc',
			'label'				=> __( 'Tipo', 'officegest' ),
			'desc_tip'			=> 'true',
			'description'		=> __( 'Tipo', 'officegest' ),
			'type' 				=> 'text',
			'custom_attributes'	=> array(
				'readonly'	=> 'readonly'
			),
		) );
		woocommerce_wp_text_input( array(
			'id'				=> '_ecoauto_status_desc',
			'label'				=> __( 'Estado', 'officegest' ),
			'desc_tip'			=> 'true',
			'description'		=> __( 'Estado', 'officegest' ),
			'type' 				=> 'text',
			'custom_attributes'	=> array(
				'readonly'	=> 'readonly'
			),
		) );
        woocommerce_wp_text_input( array(
            'id'				=> '_ecoauto_engine_num',
            'label'				=> __( 'Motor', 'officegest' ),
            'desc_tip'			=> 'true',
            'description'		=> __( 'Motor', 'officegest' ),
            'type' 				=> 'text',
            'custom_attributes'	=> array(
                'readonly'	=> 'readonly'
            ),
        ) );
		?></div>
        </div><?php
	}

	public function woocommerce_process_product_meta( $post_id ) {
		$meta = array();
		//EAN
		$meta['_officegest_ean'] =  ! empty( $_POST['_officegest_ean'] ) ? wc_clean( $_POST['_officegest_ean'] ) : '';
		//Brand
		$meta['_officegest_brand'] = ! empty( $_POST['_officegest_brand'] ) ? wc_clean( $_POST['_officegest_brand'] ) : '';
		$meta = apply_filters( 'officegest_process_product_meta', $meta );
		//Update meta - CRUD
		$product = wc_get_product( $post_id );
		foreach ( $meta as $key => $value ) {
			$product->update_meta_data( $key, $value );
		}
		$product->save();
	}

	public function get_setting( $setting ) {
		if ( isset( $this->settings[ $setting ] ) ) {
			return $this->settings[ $setting ];
		}
		return '';
	}

	public function custom_product_fields() {
		//EAN - It should be by variation...
		woocommerce_wp_text_input( array(
			'id'			=> '_officegest_ean',
			'label'			=> __( 'EAN', 'officegest' ),
			'placeholder'	=> __( 'Código de Barras', 'officegest' ),
			'desc_tip'      =>__('Mostra o Codigo de Barras associado no Officegest','officegest')
		) );
		//Brand
		woocommerce_wp_text_input( array(
			'id'			=> '_officegest_brand',
			'label'			=> __( 'Marca', 'officegest' ),
			'placeholder'	=> __( 'Marca', 'officegest' ),
		) );
	}

    public function officegest_add_meta_box($post_type)
    {
        if ( in_array( $post_type, $this->allowedPostTypes, true ) ) {
            add_meta_box('woocommerce_product_options_general_product_data', 'OfficeGest', [$this, 'showOfficeGestView'], null, 'side');
        }
    }

    /**
     * @return null|void
     */
    public function showOfficeGestView()
    {
        try {
            if (Start::login()) {
                $this->product = wc_get_product(get_the_ID());
                if (!$this->product) {
                    return null;
                }
                $this->officegestProduct = new Product($this->product);
                try {
                    if (!$this->officegestProduct->loadByReference()) {
                        echo sprintf(__( 'Artigo com a referência %s não encontrado' ), $this->officegestProduct->reference);
                        return null;
                    }

                    $this->showProductDetails();
                } catch (Error $e) {
                    return null;
                }
            } else {
                echo __( 'Login OfficeGest inválido' );
            }
        } catch (Exception $exception) {

        }
    }

    private function showProductDetails()
    {
        ?>
        <div>
            <p>
                <b><?= __( 'Referência: ' ) ?></b> <?= $this->officegestProduct->reference ?><br>
                <b><?= __( 'Preço de Compra : ' ) ?></b> <?= Tools::format_number($this->officegestProduct->purchasing) ?> €<br>
                <b><?= __( 'Preço : ' ) ?></b> <?=  Tools::format_number($this->officegestProduct->price) ?> €<br>
                <b><?= __( 'PVP : ' ) ?></b> <?=  Tools::format_number($this->product->get_price()) ?> €<br>
                <?php if ($this->officegestProduct->has_stock === true) : ?>
                    <b><?= __( 'Stock: ' ) ?></b> <?= $this->officegestProduct->stock ?>
                <?php else: ?>
                    <b><?= __( 'Stock: ' ) ?></b> 0
	            <?php endif; ?>

                <a type="button" class="button button-primary" target="_BLANK" href="<?= OFFICEGEST_DOMAIN ?>/stocks/artigos/view/<?= base64_encode($this->officegestProduct->product_id) ?>" style="margin-top: -15px; float:right;"> Ver Artigo </a>
            </p>
        </div>
        <?php
    }
}
