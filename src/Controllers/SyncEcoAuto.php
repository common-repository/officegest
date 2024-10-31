<?php

namespace OfficeGest\Controllers;

use OfficeGest\ArraySearcher;
use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;

class SyncEcoAuto
{

    private $found = 0;
	/**
	 * Run the sync operation
	 * @return SyncEcoAuto
	 * @throws \ErrorException
	 */
    public function run()
    {
		$has_ecoauto = OfficeGestDBModel::getOption('officegest_ecoauto')==1;
		$has_syncpecas = OfficeGestDBModel::getOption('ecoauto_sync_pecas')==1;
	    if ($has_ecoauto){
			if ($has_syncpecas){
				$params = [
					'tipos_pecas'=>OfficeGestDBModel::getOption('ecoauto_tipos_pecas'),
					'imagens'=>OfficeGestDBModel::getOption('ecoauto_imagens'),
				];
				$filtros= OfficeGestDBModel::getAllEcoautoPartsDB(true,$params);
				$contador = OfficeGestDBModel::cria_peca($filtros);
				OfficeGestDBModel::limpapecas();
				$this->found = $contador;
			}
	    }
        return $this;
    }

    /**
     * Get the amount of records found
     * @return int
     */
    public function countFoundRecord()
    {
        return $this->found;
    }

}
