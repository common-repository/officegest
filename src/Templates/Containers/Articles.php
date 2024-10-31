<?php
use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;
$brands = OfficeGestDBModel::getDBBrands();
$families = OfficeGestDBModel::getCategories(true);
$classificacao = OfficeGestCurl::getClassifications();

?>

<style>
    table.dataTable tbody td.select-checkbox::before, table.dataTable tbody td.select-checkbox::after, table.dataTable tbody th.select-checkbox::before, table.dataTable tbody th.select-checkbox::after {
        left: 90%!important;
    }
</style>
<div class="wrap">
    <h3><?= __("Actualização/Criação de Artigos") ?></h3>
    <div class="tablenav top">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text"></label>
                <select name="familia" id="articles_family" class="officegest-select2">
                    <option value="-1"><?= __("Familia") ?></option>
                    <?php foreach ($families as $k=>$v) { ?>
                        <option value="<?=$v['id']?>"><?= $v['description'] ?></option>
                    <?php } ?>
                </select>
                <?php if (count($brands)>0) {?>
                <select name="classificacao" id="articles_brand" class="officegest-select2">
                    <option value="-1"><?= __("Marca") ?></option>
	                <?php foreach ($brands as $k=>$v) { ?>
                        <option value="<?=$v['id']?>"><?= $v['description'] ?></option>
	                <?php } ?>
                </select>
                <?php } else { ?>
                    <input type="hidden" id="articles_brand" value="-1">
                <?php } ?>
                <select name="apenas_nao_integrados" id="apenas_nao_integrados" class="officegest-select2">
                    <option value="-1"><?= __("Apenas Não Integrados") ?></option>
                    <option value="NOPE"><?= __("Sim") ?></option>
                    <option value="OK"><?= __("Não") ?></option>
                </select>
            <label class="label" for="articles_family">Familia</label>
            <input type="button" id="officegest_doactionfilter" class="button action" value="Filtrar">
        </div>
        <div class="alignright actions bulk-actions">
            <select name="tipo_actualizacao" id="tipo_actualizacao" class="officegest-select2">
                <option value="-1">Actualizar Preço por:</option>
                <option value="PRECO_FICHA"><?= __("Preço da Ficha") ?></option>
                <option value="CLASSIFICACAO"><?= __("Classificação") ?></option>
            </select>
            <select name="classificacao" id="tipo_classificacao" class="officegest-select2">
                <option value="-1"><?= __("Classificação") ?></option>
		        <?php foreach ($classificacao as $k=>$v) { ?>
                    <option value="<?=$v['id']?>"><?= $v['description'] ?></option>
		        <?php } ?>
            </select>
            <input type="button" id="doactionupdate" class="button action" value="Actualizar">
        </div>
    </div>
    <hr>
    <table class='wp-list-table widefat fixed striped' id="articles">
        <thead>
        <tr>
            <th  style="width: 180px;"><a><?= __("Artigo") ?></a>&nbsp;&nbsp;<input type="button" id="select_all" class="button action" value="Todos"></th>
            <th style="width: 150px;"><a><?= __("Descrição") ?></a></th>
            <th><a><?= __("Marca") ?></a></th>
            <th><a><?= __("EAN") ?></a></th>
            <th><a><?= __("Familia") ?></a></th>
            <th><a><?= __("PVP") ?></a></th>
            <th><a><?= __("PVP + IVA") ?></a></th>
            <th><a><?= __("Tipo de Artigo") ?></a></th>
            <th><a><?= __("Integrado") ?></a></th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
