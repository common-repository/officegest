<?php

use OfficeGest\OfficeGestDBModel;
$configuracao = OfficeGestDBModel::getOption('general_configuration');
$officegest_ecoauto = OfficeGestDBModel::getOption('officegest_ecoauto');
$ecoauto_obs = OfficeGestDBModel::getOption('ecoauto_obs');
$ecoauto_nome_peca = OfficeGestDBModel::getOption('ecoauto_nome_peca');
$ecoauto_tipos_pecas = OfficeGestDBModel::getOption('ecoauto_tipos_pecas');
$ecoauto_imagens= OfficeGestDBModel::getOption('ecoauto_imagens');
$ecoauto_sync_pecas= OfficeGestDBModel::getOption('ecoauto_sync_pecas');
$ecoauto_sync_arvore= OfficeGestDBModel::getOption('ecoauto_sync_arvore');
$ecoauto_sync_imagens =  OfficeGestDBModel::getOption('ecoauto_sync_imagens');
$ecoauto_viaturas= OfficeGestDBModel::getOption('ecoauto_viaturas');
$ecoauto_tipos_viaturas= OfficeGestDBModel::getOption('ecoauto_tipos_viaturas');
$ecoauto_pecas_description= OfficeGestDBModel::getOption('ecoauto_pecas_description');
$ecoauto_viaturas_description= OfficeGestDBModel::getOption('ecoauto_viaturas_description');

$ecoauto_sync_imagens_limit = OfficeGestDBModel::getOption('ecoauto_sync_imagens_limit');
$ecoauto_sync_parts_limit = OfficeGestDBModel::getOption('ecoauto_parts_sync_limit');
$ecoauto_sync_processes_limit = OfficeGestDBModel::getOption('ecoauto_processes_sync_limit');

?>
<table class="form-table">
	<tbody>
	<tr>
        <th scope="row" class="titledesc">
            <label for="officegest_ecoauto"><?= __('Ecoauto') ?> </label>
        </th>
		<td>
			<select id="officegest_ecoauto" name='opt[officegest_ecoauto]' class='officegest_select2 inputOut'>
				<option value='0' <?= ($officegest_ecoauto == "0" ? "selected" : "") ?>><?= __('Não') ?></option>
				<option value='1' <?= ($officegest_ecoauto == "1" ? "selected" : "") ?>><?= __("Sim") ?></option>
			</select>
			<p class='description'><?= __('Usa EcoAuto)') ?></p>
		</td>
	</tr>
	<?php if ($officegest_ecoauto == 1) {?>

	<tr>
		<th scope="row" class="titledesc">
			<label for="ecoauto_nome_peca"><?= __("Nome da Peça") ?></label>
		</th>
		<td class="forminp">
			<fieldset>
				<legend class="screen-reader-text"><span>Titulo da Peça</span></legend>
				<textarea rows="3" cols="20" class="input-text wide-input " type="textarea" name="opt[ecoauto_nome_peca]" id="ecoauto_nome_peca" style="" placeholder=""><?=$ecoauto_nome_peca?></textarea>
				<p class='description'><?= __("Tags Disponiveis: {componente} {marca} {modelo} {versao} {combustivel} {cor} {ano_fabrico} {codigo_barras} {oem}") ?></p>
			</fieldset>
		</td>
	</tr>
    <tr>
        <th scope="row" class="titledesc">
            <label for="ecoauto_obs"><?= __("Texto Genérico de Observações") ?></label>
        </th>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text"><span>Texto Genérico de Observações</span></legend>
                <textarea rows="3" cols="20" class="input-text wide-input " type="textarea" name="opt[ecoauto_obs]" id="ecoauto_obs" style="" placeholder=""><?=$ecoauto_obs?></textarea>
                <p class='description'><?= __("Tags Disponiveis: {componente} {marca} {modelo} {versao} {combustivel} {cor} {ano_fabrico} {codigo_barras} {oem}") ?></p>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row" class="titledesc">
            <label for="ecoauto_imagens"><?= __("Integrar Apenas Peças com Imagem") ?></label>
        </th>
        <td>
            <select id="ecoauto_imagens" name='opt[ecoauto_imagens]' class='officegest_select2 inputOut'>
                <option value='1' <?= $ecoauto_imagens == '1' ? 'selected' : '1' ?>><?= __("Sim") ?></option>
                <option value='2' <?= $ecoauto_imagens == '2' ? 'selected' : '2' ?>><?= __("Não") ?></option>
            </select>
            <p class='description'><?= __('Metodo de Integração das Categorias') ?></p>
        </td>
    </tr>
	<tr>
		<th scope="row" class="titledesc">
			<label for="ecoauto_tipos_pecas"><?= __("Tipos de Peças a Integrar") ?></label>
		</th>
		<td>
			<select id="ecoauto_tipos_pecas" name='opt[ecoauto_tipos_pecas]' class='officegest_select2 inputOut'>
				<option value='0' <?= $ecoauto_tipos_pecas == '0' ? 'selected' : '0' ?>><?= __("Todas") ?></option>
				<option value='1' <?= $ecoauto_tipos_pecas == '1' ? 'selected' : '1' ?>><?= __("Desmanteladas") ?></option>
				<option value='2' <?= $ecoauto_tipos_pecas == '2' ? 'selected' : '2' ?>><?= __("Em Parque") ?></option>
				<option value='3' <?= $ecoauto_tipos_pecas == '3' ? 'selected' : '3' ?>><?= __("Desmanteladas e em Parque") ?></option>
			</select>
			<p class='description'><?= __('Tipos de Peça a Integrar no Woocommerce') ?></p>
		</td>
	</tr>
    <tr>
        <th scope="row" class="titledesc">
            <label for="ecoauto_pecas_description"><?= __("Observacoes Pecas") ?></label>
        </th>
        <td>
            <select id="ecoauto_pecas_description" name='opt[ecoauto_pecas_description]' class='officegest_select2 inputOut'>
                <option value='0' <?= $ecoauto_pecas_description == 0 ? 'selected' : '0' ?>><?= __("Não") ?></option>
                <option value='1' <?= $ecoauto_pecas_description == 1 ? 'selected' : '1' ?>><?= __("Sim") ?></option>

            </select>
            <p class='description'><?= __('Observacoes Pecas') ?></p>
        </td>
    </tr>
	<tr>
		<th scope="row" class="titledesc">
			<label for="ecoauto_sync_arvore"><?= __("Sincronizar Arvore") ?></label>
		</th>
		<td>
			<select id="ecoauto_sync_arvore" name='opt[ecoauto_sync_arvore]' class='officegest_select2 inputOut'>
				<option value='1' <?= $ecoauto_sync_arvore == '1' ? 'selected' : '1' ?>><?= __("Sim") ?></option>
				<option value='2' <?= $ecoauto_sync_arvore == '2' ? 'selected' : '2' ?>><?= __("Não") ?></option>
			</select>
			<p class='description'><?= __('Sincronizar Arvore') ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row" class="titledesc">
			<label for="ecoauto_sync_pecas"><?= __("Sincronizar Peças Automaticamente") ?></label>
		</th>
		<td>
			<select id="ecoauto_sync_pecas" name='opt[ecoauto_sync_pecas]' class='officegest_select2 inputOut'>
				<option value='1' <?= $ecoauto_sync_pecas == '1' ? 'selected' : '1' ?>><?= __("Sim") ?></option>
				<option value='2' <?= $ecoauto_sync_pecas == '2' ? 'selected' : '2' ?>><?= __("Não") ?></option>
			</select>
			<p class='description'><?= __('Sincronizar Peças Automaticamente') ?></p>
		</td>
    </tr>
    <tr>
		<th scope="row" class="titledesc">
			<label for="ecoauto_sync_imagens"><?= __("Sincronizar Imagens Automaticamente") ?></label>
		</th>
		<td>
			<select id="ecoauto_sync_imagens" name='opt[ecoauto_sync_imagens]' class='officegest_select2 inputOut'>
				<option value='1' <?= $ecoauto_sync_imagens == '1' ? 'selected' : '1' ?>><?= __("Sim") ?></option>
				<option value='2' <?= $ecoauto_sync_imagens == '2' ? 'selected' : '2' ?>><?= __("Não") ?></option>
			</select>
			<p class='description'><?= __('Sincronizar Imagens Automaticamente') ?></p>
		</td>
    </tr>
    <tr>
        <th scope="row" class="titledesc">
            <label for="ecoauto_viaturas"><?= __("Integra Viaturas") ?></label>
        </th>
        <td>
            <select id="ecoauto_viaturas" name='opt[ecoauto_viaturas]' class='officegest_select2 inputOut'>
                <option value='0' <?= $ecoauto_viaturas == '0' ? 'selected' : '0' ?>><?= __("Não") ?></option>
                <option value='1' <?= $ecoauto_viaturas == '1' ? 'selected' : '1' ?>><?= __("Sim") ?></option>
            </select>
            <p class='description'><?= __('Integra Viaturas') ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row" class="titledesc">
            <label for="ecoauto_viaturas_description"><?= __("Observacoes Viaturas") ?></label>
        </th>
        <td>
            <select id="ecoauto_viaturas_description" name='opt[ecoauto_viaturas_description]' class='officegest_select2 inputOut'>
                <option value='0' <?= $ecoauto_viaturas_description == 0 ? 'selected' : '0' ?>><?= __("Não") ?></option>
                <option value='1' <?= $ecoauto_viaturas_description == 1 ? 'selected' : '1' ?>><?= __("Sim") ?></option>

            </select>
            <p class='description'><?= __('Observacoes Pecas') ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row" class="titledesc">
            <label for="ecoauto_tipos_viaturas"><?= __("Tipos de Viaturas a Integrar") ?></label>
        </th>
        <td>
            <select id="ecoauto_tipos_viaturas" name='opt[ecoauto_tipos_viaturas]' class='officegest_select2 inputOut'>
                <option value='0' <?= $ecoauto_tipos_viaturas == '0' ? 'selected' : '0' ?>><?= __("Apenas em Parque") ?></option>
                <option value='1' <?= $ecoauto_tipos_viaturas == '1' ? 'selected' : '1' ?>><?= __("Todas ") ?></option>
            </select>
            <p class='description'><?= __('Tipos de Peça a Integrar no Woocommerce') ?></p>
        </td>
    </tr>

    <tr>
        <th scope="row" class="titledesc">
            <label for="ecoauto_sync_imagens_limit"><?= __("Limite de Imagens por sincronização") ?></label>
        </th>
        <td>
            <select id="ecoauto_sync_imagens_limit" name='opt[ecoauto_sync_imagens_limit]' class='officegest_select2 inputOut'>
                <option value='20' <?= $ecoauto_sync_imagens_limit == '20' ? 'selected' : '20' ?>>20</option>
                <option value='40' <?= $ecoauto_sync_imagens_limit == '40' ? 'selected' : '40' ?>>40</option>
                <option value='60' <?= $ecoauto_sync_imagens_limit == '60' ? 'selected' : '60' ?>>60</option>
                <option value='80' <?= $ecoauto_sync_imagens_limit == '80' ? 'selected' : '80' ?>>80</option>
                <option value='100' <?= $ecoauto_sync_imagens_limit == '100' ? 'selected' : '100' ?>>100</option>
                <option value='120' <?= $ecoauto_sync_imagens_limit == '120' ? 'selected' : '120' ?>>120</option>
                <option value='140' <?= $ecoauto_sync_imagens_limit == '140' ? 'selected' : '140' ?>>140</option>
                <option value='160' <?= $ecoauto_sync_imagens_limit == '160' ? 'selected' : '160' ?>>160</option>
                <option value='180' <?= $ecoauto_sync_imagens_limit == '180' ? 'selected' : '180' ?>>180</option>
                <option value='200' <?= $ecoauto_sync_imagens_limit == '200' ? 'selected' : '200' ?>>200</option>
            </select>
            <p class='description'><?= __('Limite de Imagens por sincronização') ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row" class="titledesc">
            <label for="ecoauto_sync_parts_limit"><?= __("Limite de Peças por sincronização") ?></label>
        </th>
        <td>
            <select id="ecoauto_sync_parts_limit" name='opt[ecoauto_parts_sync_limit]' class='officegest_select2 inputOut'>
                <option value='20' <?= $ecoauto_sync_parts_limit == '20' ? 'selected' : '20' ?>>20</option>
                <option value='40' <?= $ecoauto_sync_parts_limit == '40' ? 'selected' : '40' ?>>40</option>
                <option value='60' <?= $ecoauto_sync_parts_limit == '60' ? 'selected' : '60' ?>>60</option>
                <option value='80' <?= $ecoauto_sync_parts_limit == '80' ? 'selected' : '80' ?>>80</option>
                <option value='100' <?= $ecoauto_sync_parts_limit == '100' ? 'selected' : '100' ?>>100</option>
                <option value='120' <?= $ecoauto_sync_parts_limit == '120' ? 'selected' : '120' ?>>120</option>
                <option value='140' <?= $ecoauto_sync_parts_limit == '140' ? 'selected' : '140' ?>>140</option>
                <option value='160' <?= $ecoauto_sync_parts_limit == '160' ? 'selected' : '160' ?>>160</option>
                <option value='180' <?= $ecoauto_sync_parts_limit == '180' ? 'selected' : '180' ?>>180</option>
                <option value='200' <?= $ecoauto_sync_parts_limit == '200' ? 'selected' : '200' ?>>200</option>
            </select>
            <p class='description'><?= __('Limite de Peças por sincronização') ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row" class="titledesc">
            <label for="ecoauto_processes_sync_limit"><?= __("Limite de Viaturas por sincronização") ?></label>
        </th>
        <td>
            <select id="ecoauto_processes_sync_limit" name='opt[ecoauto_processes_sync_limit]' class='officegest_select2 inputOut'>
                <option value='20' <?= $ecoauto_sync_processes_limit == '20' ? 'selected' : '20' ?>>20</option>
                <option value='40' <?= $ecoauto_sync_processes_limit == '40' ? 'selected' : '40' ?>>40</option>
                <option value='60' <?= $ecoauto_sync_processes_limit == '60' ? 'selected' : '60' ?>>60</option>
                <option value='80' <?= $ecoauto_sync_processes_limit == '80' ? 'selected' : '80' ?>>80</option>
                <option value='100' <?= $ecoauto_sync_processes_limit == '100' ? 'selected' : '100' ?>>100</option>
                <option value='120' <?= $ecoauto_sync_processes_limit == '120' ? 'selected' : '120' ?>>120</option>
                <option value='140' <?= $ecoauto_sync_processes_limit == '140' ? 'selected' : '140' ?>>140</option>
                <option value='160' <?= $ecoauto_sync_processes_limit == '160' ? 'selected' : '160' ?>>160</option>
                <option value='180' <?= $ecoauto_sync_processes_limit == '180' ? 'selected' : '180' ?>>180</option>
                <option value='200' <?= $ecoauto_sync_processes_limit == '200' ? 'selected' : '200' ?>>200</option>
            </select>
            <p class='description'><?= __('Limite de Viaturas por sincronização') ?></p>
        </td>
    </tr>

    <?php } ?>