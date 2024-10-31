<?php
namespace OfficeGest\Controllers;

use OfficeGest\ArraySearcher;
use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;

class SyncViaturas
{

	private $found = 0;
	/**
	 * Run the sync operation
	 * @return SyncViaturas
	 * @throws \ErrorException
	 */
	public function run()
	{
		global $wpdb;
		$has_ecoauto = OfficeGestDBModel::getOption('officegest_ecoauto')==1;
		$has_ecoauto_viaturas = OfficeGestDBModel::getOption('ecoauto_viaturas')==1;
		$ecoauto_tipos_viaturas = OfficeGestDBModel::getOption('ecoauto_tipos_viaturas');
		if ($has_ecoauto && $has_ecoauto_viaturas) {
			OfficeGestDBModel::getAllProcesses();
			$parent_term = term_exists( 'viaturas', 'product_cat');
			if (!isset($parent_term['term_id'])){
				$data['description'] ='Viaturas';
				OfficeGestDBModel::create_product_category( $data, false );
			}
			$parent_term = term_exists( 'viaturas', 'product_cat');
			$parent_term_id = $parent_term['term_id'];
			if ($ecoauto_tipos_viaturas==0){
				$states = "'PDISM','ABR'";
			}
			else{
				$states = "'PDISM','ABR','PREN'";
			}
			$sql = "SELECT * from ".TABLE_OFFICEGEST_ECO_PROCESSES." where state in (".$states.")";
			$filtros = $wpdb->get_results($sql,ARRAY_A);
			foreach($filtros as $key => $value) {
				$brand_term = term_exists( $value['brand'], 'product_cat', $parent_term_id );
				if (!isset($brand_term['term_id'])) {
					OfficeGestDBModel::create_product_category( [
						'description' => $value['brand'],
						'parent' => $parent_term_id
					], true );
					$brand_term = term_exists( $value['brand'], 'product_cat', $parent_term_id );
				}
				$model_term = term_exists( $value['model'], 'product_cat', $brand_term['term_id'] );
				if (!isset($model_term['term_id'])) {
					OfficeGestDBModel::create_product_category( [
						'description' => $value['model'],
						'parent' => $brand_term['term_id']
					], true );
					$model_term = term_exists( $value['model'], 'product_cat', $brand_term['term_id'] );
				}
			}
			$sql_pecas = "SELECT * from ".TABLE_OFFICEGEST_ECO_PROCESSES." where (woo_id is null or woo_id=0) and state in ($states)  order by id";
			$pecas = $wpdb->get_results($sql_pecas,ARRAY_A);
			if (sizeof($pecas)>0){
				$contador = OfficeGestDBModel::cria_viatura($pecas);
				$this->found++;
			}
			else{
				$sql_pecas = "SELECT * from ".TABLE_OFFICEGEST_ECO_PROCESSES." where (woo_id is not null or woo_id!=0) and state in ($states)  order by id";
				$pecas = $wpdb->get_results($sql_pecas,ARRAY_A);
				if (sizeof($pecas)>0){
					$contador = OfficeGestDBModel::cria_viatura($pecas);
					$this->found++;
				}
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