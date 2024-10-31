<?php

use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
$configuracao = OfficeGestDBModel::getOption('general_configuration');
$sync_article_images = OfficeGestDBModel::getOption('sync_article_images');
?>
<br>
<table class="wc_status_table wc_status_table--utils widefat">
    <tbody class="utils">
        <?php if ($configuracao >0) {?>
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Actualização de Familias (Woocommerce -> Officegest)') ?></strong>
                <p class='description'><?= __('Sincronizar todas as familias de artigos para o Officegest do Woocommerce') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=officegest&tab=utils&action=syncfamiliestoog') ?>'>
			        <?= __('Sincronizar Familias (Woocommerce -> Officegest)') ?>
                </a>
            </td>
        </tr>
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Actualização de Familias de Artigos') ?></strong>
                <p class='description'><?= __('Sincronizar todas as familias de artigos do Officegest') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=officegest&tab=utils&action=syncofficegestarticlefamilies') ?>'>
                    <?= __('Sincronizar Familias de Artigos)') ?>
                </a>
            </td>
        </tr>
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Criação de Artigos') ?></strong>
                <p class='description'><?= __('Sincronizar todas os artigos para o Officegest do Woocommerce') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=officegest&tab=utils&action=syncarticles') ?>'>
			        <?= __('Forçar Criação de Artigos') ?>
                </a>
            </td>
        </tr>
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Actualização de Stocks') ?></strong>
                <p class='description'><?= __('Sincronizar todas os stocks dos artigos inseridos no Woocommerce') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=officegest&tab=utils&action=syncstocks') ?>'>
			        <?= __('Actualização de Stocks') ?>
                </a>
            </td>
        </tr>
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Actualização de Artigos') ?></strong>
                <p class='description'><?= __('Actualiza a tabela de artigos do Officegest com novos artigos') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=officegest&tab=utils&action=syncarticlesfromog') ?>'>
			        <?= __('Actualiza a tabela de artigos do Officegest com novos artigos') ?>
                </a>
            </td>
        </tr>
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Artigos') ?></strong>
                <p class='description'><?= __('Força a importação dos artigos') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=officegest&tab=utils&action=forcesyncarticles') ?>'>
                    <?= __('Importação dos Artigos') ?>
                </a>
            </td>
        </tr>
            <?php if ($sync_article_images >0) {?>
                <tr>
                    <th style="padding: 2rem">
                        <strong class="name"><?= __('Imagens de Artigos') ?></strong>
                        <p class='description'><?= __('Força a importacao das Imagens dos Artigos') ?></p>
                    </th>
                    <td class="run-tool" style="padding: 2rem; text-align: right">
                        <a class="button button-large"
                           href='<?= admin_url('admin.php?page=officegest&tab=utils&action=syncarticleimages') ?>'>
                            <?= __('Importação das Imagens') ?>
                        </a>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>