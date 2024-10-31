<?php

use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
$configuracao = OfficeGestDBModel::getOption('general_configuration');
?>

<br>
<table class="wc_status_table wc_status_table--tools widefat">
    <tbody class="tools">
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Forçar Sincronização de Taxas de IVA') ?></strong>
                <p class='description'><?= __('Sincronizar todas as taxas de IVA para o Woocommerce') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=officegest&tab=tools&action=synctaxes') ?>'>
			        <?= __('Forçar sincronização de Taxas de IVA') ?>
                </a>
            </td>
        </tr>
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Limpar encomendas pendentes') ?></strong>
                <p class='description'><?= __('Remover todas as encomendas da listagem de encomendas') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=officegest&tab=tools&action=reminvoiceall') ?>'>
                       <?= __('Limpar encomendas pendentes') ?>
                </a>
            </td>
        </tr>
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Limpar logs') ?></strong>
                <p class='description'><?= __('Apagar todos os ficheiros de logs de dias anteriores') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=officegest&tab=tools&action=remlogs') ?>'>
			        <?= __('Apagar logs diários') ?>
                </a>
            </td>
        </tr>
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Sair da empresa') ?></strong>
                <p class='description'><?= __('Iremos manter os dados referentes aos documentos já emitidos') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large button-primary"
                   href='<?= admin_url('admin.php?page=officegest&tab=tools&action=logout') ?>'>
                       <?= __('Sair da empresa') ?>
                </a>
            </td>
        </tr>
    </tbody>
</table>