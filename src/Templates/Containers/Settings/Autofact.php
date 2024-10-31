<?php

use OfficeGest\OfficeGestDBModel;
$configuracao = OfficeGestDBModel::getOption('general_configuration');
$invoice_auto = OfficeGestDBModel::getOption('invoice_auto');
$officegest_stock_sync = OfficeGestDBModel::getOption('officegest_stock_sync');

?>
<table class="form-table">
	<tbody>
	<?php if ($configuracao >0) { ?>
	<tr>
        <th scope="row" class="titledesc">
            <label for="officegest_stock_sync"><?= __('Sincronizar stocks automaticamente') ?> </label>
        </th>
		<td>
			<select id="officegest_stock_sync" name='opt[officegest_stock_sync]' class='officegest_select2 inputOut'>
				<option value='0' <?= ($officegest_stock_sync == "0" ? "selected" : "") ?>><?= __('Não') ?></option>
				<option value='1' <?= ($officegest_stock_sync == "1" ? "selected" : "") ?>><?= __("Sim") ?></option>
			</select>
			<p class='description'><?= __('Sincronização de stocks automática (corre a cada 5 minutos e actualiza o stock dos artigos com base no OfficeGest)') ?></p>
		</td>
	</tr>
    <?php } ?>
