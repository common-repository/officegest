<?php

namespace OfficeGest\Hooks;

use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Plugin;

/**
 * Class OrderView
 * Add a OfficeGest Windows to when user is in the order view
 * There they can create a document for that order or check the document if it was already created
 * @package OfficeGest\Hooks
 */
class OrderView
{

    public $parent;
    public $pontos_recolha;

    /** @var array */
    private $allowedStatus = [ 'wc-processing', 'wc-completed' ];

    /**
     * @param Plugin $parent
     */
    public function __construct($parent)
    {
        $this->parent = $parent;
	    $this->pontos_recolha = OfficeGestDBModel::getOption('pontos_de_recolha');
        add_action('add_meta_boxes', [$this, 'officegest_add_meta_box']);
    }

    public function officegest_add_meta_box()
    {
        add_meta_box('officegest_add_meta_box', 'OfficeGest', [$this, 'showOfficeGestView'], 'shop_order', 'side', 'core');

	    if ($this->pontos_recolha==1) {
		    add_meta_box( 'officegest_deliveries', 'Ponto de Recolha', [
			    $this,
			    'showDeliveries'
		    ], 'shop_order', 'side', 'core' );
	    }
    }

    public function showOfficeGestView($post)
    {
        if (in_array($post->post_status, $this->allowedStatus)) : ?>
            <?php
                $documentId = get_post_meta($post->ID, '_officegest_sent', true);
                $doctype = get_post_meta($post->ID, '_officegest_doctype', true);
                $docstatus = get_post_meta($post->ID, '_officegest_docstatus', true);

            ?>
            <?php if ((int)$documentId > 0) : ?>

                <?= __('O documento já foi gerado como '.$docstatus.' no OfficeGest') ?>
                <a type="button"
                   class="button button-primary"
                   target="_BLANK"
                   href="<?= admin_url("admin.php?page=officegest&action=getInvoice&id=".$post->ID) ?>"
                   style="margin-top: 10px; float:right;"
                >
                    <?= __('Ver documento') ?>
                </a>
                <div style="clear:both"></div>
                <?php if ($docstatus==='draft') {  ?>
                <a type="button"
                   class="button"
                   target="_BLANK"
                   href="<?= admin_url("admin.php?page=officegest&action=genInvoice&id=" . $post->ID) ?>"
                   style="margin-top: 10px; float:right;"
                >
                    <?= __('Gerar novamente') ?>
                </a>
                <?php } ?>
            <?php elseif ($documentId == -1) : ?>
                <?= __('O documento foi marcado como gerado.') ?>
                <br>
		        <?php if ($docstatus==='draft') {  ?>
                    <a type="button"
                       class="button"
                       target="_BLANK"
                       href="<?= admin_url( 'admin.php?page=officegest&action=getInvoice&id='. $post->ID) ?>"
                       style="float:right"
                    >
				        <?= __('Gerar novamente') ?>
                    </a>
		        <?php } ?>

            <?php else: ?>
                <a type="button"
                   class="button button-primary"
                   target="_BLANK"
                   href="<?= admin_url("admin.php?page=officegest&action=genInvoice&id=" . $post->ID) ?>"
                   style="float:right"
                >
                    <?= __("Gerar Documento Officegest") ?>
                </a>
            <?php endif; ?>
            <div style="clear:both"></div>
        <?php else : ?>
            <?= __("A encomenda tem que ser dada como paga para poder ser gerada.") ?>
        <?php endif;
    }

    public function showDeliveries($post){
	    $billing_pp = get_post_meta($post->ID, '_billing_pp', true);
	    $pp = OfficeGestCurl::getPickupPoints();
	    $ponto_recolha = array_filter($pp, static function ($value) use ($billing_pp) {
		    return ($value["id"] == $billing_pp);
	    });
	    $html = '';
	    if (!empty($ponto_recolha)){
		    $pr = array_values($ponto_recolha)[0];
		    $html  = '['.$pr['id'].']</br> <b>'.$pr['name'] . '</b></br>' . $pr['address'].'</br>'.$pr['zipcode']. '</br>'.$pr['city'].'</br>'.$pr['country_desc'];
	    }
	    ?>
	    <?= __($html) ?>
        <?php


    }



}
