<?php
use OfficeGest\Controllers\Documents;
use OfficeGest\Controllers\GeneratedOrders;
use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;
$orders = GeneratedOrders::getAllAvailable();
$vat_field = OfficeGestDBModel::getOption('vat_field');
?>

<div class="wrap">

    <?php if (isset($document) && $document instanceof Documents && $document->getError()) : ?>
        <?php $document->getError()->showError(); ?>
    <?php endif; ?>
    <h3><?= __( 'Aqui pode consultar as encomendas já geradas no OfficeGest' ) ?></h3>
    <table class='wp-list-table widefat fixed striped posts'>
        <thead>
        <tr>
            <th style="width: 80px;"><a><?= __( 'Encomenda' ) ?></a></th>
            <th><a><?= __( 'Cliente' ) ?></a></th>
            <th><a><?= __( 'Contribuinte' ) ?></a></th>
            <th><a><?= __( 'Total' ) ?></a></th>
            <th><a><?= __( 'Estado' ) ?></a></th>
            <th><a><?= __( 'Data de Pagamento' ) ?></a></th>
            <th><a><?= __( 'Documento OfficeGest' ) ?></a></th>
        </tr>
        </thead>

        <?php if (!empty($orders) && is_array($orders)) : ?>

            <!-- Lets draw a list of all the available orders -->
            <?php foreach ($orders as $order) : ?>

                <tr>
                    <td>
                        <a href=<?= admin_url('post.php?post=' . $order['id'] . '&action=edit') ?>>#<?= $order['number'] ?></a>
                    </td>
                    <td>
                        <?php
                        if (isset($order['info']['_billing_first_name']) && !empty($order['info']['_billing_first_name'])) {
                            echo $order['info']['_billing_first_name'] . ' ' . $order['info']['_billing_last_name'];
                        } else {
                            echo __( 'Desconhecido' );
                        }
                        ?></td>
                    <td><?= (isset($order['info'][$vat_field]) && !empty($order['info'][$vat_field])) ? $order['info'][$vat_field] : '999999990' ?></td>
                    <td><?= $order['info']['_order_total'] . $order['info']['_order_currency'] ?></td>
                    <td><?= $order['status'] ?></td>
                    <td><?= isset($order['info']['_completed_date'])?$order['info']['_completed_date']:'N/D' ?></td>
                    <td><?= '['.$order['info']['_officegest_docstatus'].'] '.Tools::getDocName($order['info']['_officegest_doctype']).' nº '. $order['info']['_officegest_sent'] ?></td>
                </tr>
            <?php endforeach; ?>

        <?php else : ?>
            <tr>
                <td colspan="7">
                    <?= __( 'Não foram geradas ainda encomendas!' ) ?>
                </td>
            </tr>

        <?php endif; ?>

        <tfoot>
        <tr>
            <th><a><?= __( 'Encomenda' ) ?></a></th>
            <th><a><?= __( 'Cliente' ) ?></a></th>
            <th><a><?= __( 'Contribuinte' ) ?></a></th>
            <th><a><?= __( 'Total' ) ?></a></th>
            <th><a><?= __( 'Estado' ) ?></a></th>
            <th><a><?= __( 'Data de Pagamento' ) ?></a></th>
            <th><a><?= __( 'Documento OfficeGest' ) ?></a></th>
        </tr>
        </tfoot>
    </table>
</div>
