<?php
namespace OfficeGest\Controllers;
use OfficeGest\OfficeGestDBModel;

class SyncPecas
{

	private $found = 0;
	/**
	 * Run the sync operation
	 * @return SyncPecas
	 * @throws \ErrorException
	 */
	public function run()
	{
		global $wpdb;
		ini_set( 'memory_limit',-1);
		ini_set( 'max_execution_time',-1);
		$has_ecoauto = OfficeGestDBModel::getOption('officegest_ecoauto')==1;
		$params = [
			'tipos_pecas'=>OfficeGestDBModel::getOption('ecoauto_tipos_pecas'),
			'imagens'=>OfficeGestDBModel::getOption('ecoauto_imagens'),
		];
		if ($has_ecoauto) {
			$filtros = OfficeGestDBModel::getAllEcoautoPartsDB( true, $params );

			if ( ! empty( $filtros ) ) {
				$parent_term = term_exists( 'pecas', 'product_cat' );
				if ( ! isset( $parent_term['term_id'] ) ) {
					$data['description'] = 'Peças';
					OfficeGestDBModel::create_product_category( $data, false );
				}
				$parent_term    = term_exists( 'pecas', 'product_cat' );
				$parent_term_id = $parent_term['term_id'];
			}

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

				$categoria_term = term_exists( $value['category'], 'product_cat', $model_term['term_id'] );
				if (!isset($categoria_term['term_id'])) {

					OfficeGestDBModel::create_product_category( [
						'description' => $value['category'],
						'parent' => $model_term['term_id']
					], true );

					$categoria_term = term_exists( $value['category'], 'product_cat', $model_term['term_id'] );
				}

				$componente_term = term_exists( $value['component'], 'product_cat', $categoria_term['term_id'] );
				if (!isset($componente_term['term_id'])) {
					OfficeGestDBModel::create_product_category( [
						'description' => $value['component'],
						'parent' => $categoria_term['term_id']
					], true );
				}

			}

			$filtros = OfficeGestDBModel::getAllEcoautoPartsDB( false, $params );
			$contador = OfficeGestDBModel::cria_peca($filtros);
			OfficeGestDBModel::limpapecas();
			$this->found = $contador;
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