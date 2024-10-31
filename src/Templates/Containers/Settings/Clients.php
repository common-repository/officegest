<?php

use OfficeGest\OfficeGestDBModel;
use OfficeGest\OfficeGestCurl;

$vendedores= OfficeGestCurl::getVendors();
$classificacoes= OfficeGestCurl::getClassifications();
$vat_field = OfficeGestDBModel::getOption('vat_field');
$clientes_seller = OfficeGestDBModel::getOption('clientes_seller');
$clientes_classificacao = OfficeGestDBModel::getOption('clientes_classificacao');
?>
<table class="form-table">
	<tbody>
        <tr>
            <th scope="row" class="titledesc">
                <label for="vat_field"><?= __('Contribuinte do cliente') ?> </label>
            </th>
            <td>
                <input id="vat_field" name="opt[vat_field]" type="text"  value="<?= $vat_field ?>" style="width: 330px;">
                <p class='description'><?= __('Custom field associado ao contribuinte do cliente') ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row" class="titledesc">
                <label for="clientes_seller"><?= __('Vendedor (por omissão)') ?> </label>
            </th>
            <td>
                <select id="clientes_seller" name='opt[clientes_seller]' class='officegest_select2 inputOut'>
                    <option value='' <?= $clientes_seller == '' ? 'selected' : '' ?>><?= __( 'Escolha uma opção' ) ?></option>
	                <?php foreach ($vendedores as $k=>$v) : ?>
                        <option value='<?= $v['id'] ?>' <?= $clientes_seller == $v['id'] ? 'selected' : '' ?>><?= $v['name'] ?></option>
	                <?php endforeach; ?>
                </select>
                <p class='description'><?= __('Vendedor associado ao cliente quando este é criado no OfficeGest') ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row" class="titledesc">
                <label for="clientes_classificacao"><?= __('Classificação (por omissão)') ?> </label>
            </th>
            <td>
                <select id="clientes_classificacao" name='opt[clientes_classificacao]' class='officegest_select2 inputOut'>
                    <option value='' <?= $clientes_classificacao == '' ? 'selected' : '' ?>><?= __( 'Escolha uma opção' ) ?></option>
			        <?php foreach ($classificacoes as $k=>$v) : ?>
                        <option value='<?= $v['id'] ?>' <?= $clientes_classificacao == $v['id'] ? 'selected' : '' ?>><?= $v['description'] ?></option>
			        <?php endforeach; ?>
                </select>
                <p class='description'><?= __('Classificação associado ao cliente quando este é criado no OfficeGest') ?></p>
            </td>
        </tr>

	</tbody>
</table>
