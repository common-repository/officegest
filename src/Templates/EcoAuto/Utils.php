<br>
<table class="wc_status_table wc_status_table--utils widefat">
    <tbody class="utils">
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Peças') ?></strong>
                <p class='description'><?= __('Força a importação das Peças') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=ecoauto&tab=utils&action=forcesyncparts') ?>'>
			        <?= __('Importação das Peças') ?>
                </a>
            </td>
        </tr>
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Inventário') ?></strong>
                <p class='description'><?= __('Atualizar Inventário de Peças do Woocommerce') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=ecoauto&tab=utils&action=forcesyncpartsinventory') ?>'>
                    <?= __('Atualizar peças Woocommerce') ?>
                </a>
            </td>
        </tr>
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Viaturas') ?></strong>
                <p class='description'><?= __('Força a importacao das Viaturas') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=ecoauto&tab=utils&action=forcesyncprocesses') ?>'>
			        <?= __('Importação das Viaturas') ?>
                </a>
            </td>
        </tr>
        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Imagens') ?></strong>
                <p class='description'><?= __('Força a importacao das Imagens') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=ecoauto&tab=utils&action=syncimages') ?>'>
			        <?= __('Importação das Imagens') ?>
                </a>
            </td>
        </tr>

        <tr>
            <th style="padding: 2rem">
                <strong class="name"><?= __('Limpar Peças') ?></strong>
                <p class='description'><?= __('Forçar limpeza de peças vendidas') ?></p>
            </th>
            <td class="run-tool" style="padding: 2rem; text-align: right">
                <a class="button button-large"
                   href='<?= admin_url('admin.php?page=ecoauto&tab=utils&action=syncclearparts') ?>'>
                    <?= __('Limpar Peças') ?>
                </a>
            </td>
        </tr>

    </tbody>
</table>
