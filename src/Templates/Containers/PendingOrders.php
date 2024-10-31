<?php use \OfficeGest\Controllers\Documents; ?>
<?php use \OfficeGest\Controllers\PendingOrders;
use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel; ?>

<?php
    $orders = PendingOrders::getAllAvailable();
    $vat_field = OfficeGestDBModel::getOption('vat_field');

?>

<div class="wrap">

    <?php if (isset($document) && $document instanceof Documents && $document->getError()) : ?>
        <?php $document->getError()->showError(); ?>
    <?php endif; ?>

    <h3><?= __("Aqui pode consultar todas as encomendas que tem por gerar no OfficeGest") ?></h3>
    <div class="tablenav top">
        <div class="tablenav-pages">
			<?= PendingOrders::getPagination() ?>
        </div>
    </div>
    <table class='wp-list-table widefat fixed striped posts'>
        <thead>
        <tr>
            <th style="width: 80px;"><a><?= __("Encomenda") ?></a></th>
            <th><a><?= __("Cliente") ?></a></th>
            <th><a><?= __("Contribuinte") ?></a></th>
            <th><a><?= __("Total") ?></a></th>
            <th><a><?= __("Estado") ?></a></th>
            <th><a><?= __("Data de Pagamento") ?></a></th>
            <th style="width: 350px;"></th>
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
                            echo $order['info']['_billing_first_name'] . " " . $order['info']['_billing_last_name'];
                        } else {
                            echo __("Desconhecido");
                        }

                        ?>
                    <td><?= (isset($order['info'][$vat_field]) && !empty($order['info'][$vat_field])) ? $order['info'][$vat_field] : '999999990' ?></td>
                    <td><?= $order['info']['_order_total'] . $order['info']['_order_currency'] ?></td>
                    <td><?= $order['status'] ?></td>
                    <td><?= isset($order['info']['_completed_date'])?$order['info']['_completed_date']:'N/D' ?></td>
                    <td class="order_status column-order_status" style="text-align: right">
                        <form action="<?= admin_url('admin.php') ?>">
                            <input type="hidden" name="page" value="officegest">
                            <input type="hidden" name="action" value="genInvoice">
                            <input type="hidden" name="id" value="<?= $order['id'] ?>">
                            <select name="document_type">
	                            <?php $documentSets = OfficeGestCurl::get( 'tables/documentstypes?filter[create_api]=T&filter[purchase]=F&filter[visiblemenu]=T&filter[receipt]=F', []);
	                            ?>

	                            <?php foreach ($documentSets['documentstypes'] as $k=>$v) : ?>
		                            <?php if ( $v['typesaft']=='NC' ){continue;} ?>
                                    <option value='<?= $v['codabbreviated'] ?>' <?= DOCUMENT_TYPE == $v['codabbreviated'] ? 'selected' : '' ?>><?= $v['description'] ?></option>
	                            <?php endforeach; ?>
                            </select>
                            <input type="submit" class="wp-core-ui button-primary"  value="<?= __('Gerar') ?>">
                            <a class="wp-core-ui button-secondary"
                               href="<?= admin_url('admin.php?page=officegest&action=remInvoice&id=' . $order['id']) ?>">
                                <?= __('Limpar') ?>
                            </a>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

        <?php else : ?>
            <tr>
                <td colspan="7">
                    <?= __("Não foram encontadas encomendas por gerar!") ?>
                </td>
            </tr>

        <?php endif; ?>

        <tfoot>
        <tr>
            <th><a><?= __("Encomenda") ?></a></th>
            <th><a><?= __("Cliente") ?></a></th>
            <th><a><?= __("Contribuinte") ?></a></th>
            <th><a><?= __("Total") ?></a></th>
            <th><a><?= __("Estado") ?></a></th>
            <th><a><?= __("Data de Pagamento") ?></a></th>
            <th></th>
        </tr>
        </tfoot>
    </table>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
			<?= PendingOrders::getPagination() ?>
        </div>
    </div>
</div>
