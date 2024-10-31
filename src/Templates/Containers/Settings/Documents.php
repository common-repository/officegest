<?php

use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;
$configuracao = OfficeGestDBModel::getOption('general_configuration');
$document_type = OfficeGestDBModel::getOption('document_type');
$document_status = OfficeGestDBModel::getOption('document_status');
?>
<table class="form-table">
	<tbody>
	<tr>
        <th scope="row" class="titledesc">
            <label for="company_slug"><?= __('Empresa') ?> </label>
        </th>
		<td>
			<input id="company_slug" name="opt[company_slug]" type="text"
			       value="<?= $company['company']['name'] ?>" readonly
			       style="width: 330px;">
		</td>
	</tr>
	<tr>
		<th>
			<label for="contribuinte"><?= __("Contribuinte") ?></label>
		</th>
		<td>
			<input id="contribuinte" name="opt[contribuinte]" type="text"
			       value="<?= $company['company']['vatnumber'] ?>" readonly
			       style="width: 330px;">
		</td>
	</tr>
    <tr>
        <th>
            <label for="general_configuration">Configurações Gerais</label>
        </th>
        <td>
            <select id="general_configuration" name='opt[general_configuration]' class='officegest_select2 inputOut'>
                <option value='0' <?= ($configuracao == "0" ? "selected" : "") ?>><?= __('Apenas Encomendas') ?></option>
                <option value='1' <?= ($configuracao == "1" ? "selected" : "") ?>><?= __("Encomendas e Artigos") ?></option>
                <option value='1' <?= ($configuracao == "2" ? "selected" : "") ?>><?= __("Tudo") ?></option>
            </select>
            <p class='description'><?= __('Obrigatório. Permite configurar como vai funcionar o plugin de forma global. Caso apenas encomendas vai ignorar stocks e artigos! ') ?></p>
        </td>

    </tr>
	<tr>
		<th>
			<label for="document_type"><?= __("Tipo de documento") ?></label>
		</th>
		<td>
			<select id="document_type" name='opt[document_type]' class='officegest_select2 inputOut'>
				<?php $documentSets = OfficeGestCurl::get( 'tables/documentstypes?filter[create_api]=T&filter[purchase]=F&filter[visiblemenu]=T&filter[receipt]=F', []);
				?>
				<?php foreach ($documentSets['documentstypes'] as $k=>$v) : ?>
				<?php if ( $v['typesaft']=='NC' ){continue;} ?>
					<option value='<?= $v['codabbreviated'] ?>' <?= $document_type == $v['codabbreviated'] ? 'selected' : '' ?>><?= $v['description'] ?></option>
				<?php endforeach; ?>
			</select>
			<p class='description'><?= __('Obrigatório') ?></p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="document_status"><?= __("Estado do documento") ?></label>
		</th>
		<td>
			<select id="document_status" name='opt[document_status]' class='officegest_select2 inputOut'>
				<option value='draft' <?= ( $document_status === 'draft' ? 'selected' : '' ) ?>><?= __('Rascunho') ?></option>
				<option value='normal' <?= ( $document_status === 'normal' ? 'selected' : '' ) ?>><?= __("Formalizado") ?></option>
			</select>
			<p class='description'><?= __('Obrigatório. Cria o documento como rascunho (com possibilidade de edição) ou cria o documento formalizado (sem possibilidade de edição)') ?></p>
		</td>
	</tr>
	</tbody>
</table>