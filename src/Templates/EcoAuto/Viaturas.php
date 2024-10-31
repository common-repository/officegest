<?php

use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;

$brands        = OfficeGestDBModel::getCarBrands();
$parts_cats    = OfficeGestDBModel::DBEcoautoCategories();
$viatura_status   = OfficeGestDBModel::getProcessesStates();
$part_types    = OfficeGestDBModel::getAllEcoautoPartsTypes();

?>

<style>
    table.dataTable tbody td.select-checkbox::before, table.dataTable tbody td.select-checkbox::after, table.dataTable tbody th.select-checkbox::before, table.dataTable tbody th.select-checkbox::after {
        left: 90% !important;
    }
</style>
<div class="wrap">
    <h3><?= __( "Actualização/Criação de Viaturas Usadas" ) ?></h3>
    <div class="tablenav top">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text"></label>
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

            <select name="apenas_nao_integrados" id="apenas_nao_integrados" class="officegest-select2">
                <option value="-1"><?= __( "Apenas Não Integrados" ) ?></option>
                <option value="NOPE"><?= __( "Sim" ) ?></option>
                <option value="OK"><?= __( "Não" ) ?></option>
            </select>

            <input type="button" id="carros_usados_doactionfilter" class="button action" value="Filtrar">
        </div>
        <div class="alignright actions bulk-actions">
            <input type="button" id="carros_usados_doactionupdate" class="button action" value="Actualizar">
        </div>
    </div>
    <hr>
    <table class='wp-list-table widefat fixed striped' id="carros_usados">
        <thead>
        <tr>
	        <th  style="width: 180px;"><a><?= __("Viatura") ?></a>&nbsp;&nbsp;<input type="button" id="select_all" class="button action" value="Todos"></th>
            <th><a>Processo</a></th>
            <th><a>Codigo de Barras</a></th>
            <th><a>Matricula</a></th>
            <th><a>Marca</a></th>
            <th><a>Modelo</a></th>
            <th><a>Combustivél</a></th>
            <th><a>Estado</a></th>
            <th><a>Observações</a></th>
	        <th><a><?= __("Integrado") ?></a></th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
