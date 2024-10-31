<?php

use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;

$brands        = OfficeGestDBModel::getDBBrands();
$parts_cats    = OfficeGestDBModel::DBEcoautoCategories();
$part_status   = OfficeGestDBModel::getAllEcoautoPartsStatus();
$part_types    = OfficeGestDBModel::getAllEcoautoPartsTypes();

?>

<style>
    table.dataTable tbody td.select-checkbox::before, table.dataTable tbody td.select-checkbox::after, table.dataTable tbody th.select-checkbox::before, table.dataTable tbody th.select-checkbox::after {
        left: 90% !important;
    }
</style>
<div class="wrap">
    <h3><?= __( "Actualização/Criação de Peças Usadas" ) ?></h3>
    <div class="tablenav top">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text"></label>
            <select name="category" id="article_category" class="officegest-select2">
                <option value="-1"><?= __( "Categoria" ) ?></option>
				<?php foreach ( $parts_cats as $k => $v ) { ?>
                    <option value="<?= $v['id'] ?>"><?= $v['description'] ?></option>
				<?php } ?>
            </select>
			<?php if ( count( $brands ) > 0 ) { ?>
                <select name="articles_brand" id="articles_brand" class="officegest-select2">
                    <option value="-1"><?= __( "Marca" ) ?></option>
					<?php foreach ( $brands as $k => $v ) { ?>
                        <option value="<?= $v['id'] ?>"><?= $v['description'] ?></option>
					<?php } ?>
                </select>
			<?php } else { ?>
                <input type="hidden" id="articles_brand" value="-1">
			<?php } ?>
            <select name="part_status" id="part_status" class="officegest-select2">
                <option value="-1"><?= __( "Estado" ) ?></option>
				<?php foreach ( $part_status as $k => $v ) { ?>
                    <option value="<?= $v['description'] ?>"><?= $v['description'] ?></option>
				<?php } ?>
            </select>

            <select name="part_types" id="part_types" class="officegest-select2">
                <option value="-1"><?= __( "Tipo" ) ?></option>
				<?php foreach ( $part_types as $k => $v ) { ?>
                    <option value="<?= $v['description'] ?>"><?= $v['description'] ?></option>
				<?php } ?>
            </select>

            <select name="apenas_nao_integrados" id="apenas_nao_integrados" class="officegest-select2">
                <option value="-1"><?= __( "Apenas Não Integrados" ) ?></option>
                <option value="NOPE"><?= __( "Sim" ) ?></option>
                <option value="OK"><?= __( "Não" ) ?></option>
            </select>

            <input type="button" id="ecoauto_doactionfilter" class="button action" value="Filtrar">
        </div>
        <div class="alignright actions bulk-actions">
            <input type="button" id="ecoauto_doactionupdate" class="button action" value="Actualizar">
        </div>
    </div>
    <hr>
    <table class='wp-list-table widefat fixed striped' id="pecas_usadas">
        <thead>
        <tr>
	        <th  style="width: 180px;"><a><?= __("Peça") ?></a>&nbsp;&nbsp;<input type="button" id="select_all" class="button action" value="Todos"></th>
            <th><a>Processo</a></th>
            <th><a>Descrição</a></th>
            <th><a>Estado</a></th>
            <th><a>Data</a></th>
            <th><a>Peças</a></th>
            <th><a>OEM</a></th>
            <th><a>Preço Venda</a></th>
            <th><a>Tipo</a></th>
            <th><a>Imagens</a></th>
            <th><a>Observações</a></th>
	        <th><a><?= __("Integrado") ?></a></th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
