<?php

namespace OfficeGest;
use Curl\Curl;
use WC_Tax;

class OfficeGestCurl {

	private static $debug = false;
	/** @var array Hold the request log */
	private static $logs = [];

	/**
	 * Hold a list of methods that can be cached
	 * @var array
	 */
	private static $allowedCachedMethods = [
		'utils/company',
		'tables/countries',
		'tables/vates',
		'utils/parameters'
	];

	/**
	 * Save a request cache
	 * @var array
	 */
	private static $cache = [];

	public static function simple( $action, $values = false ) {

		if ( in_array( $action, self::$allowedCachedMethods ) ) {
			if ( isset( self::$cache[ $action ] ) ) {
				return self::$cache[ $action ];
			}
		}

//        if (is_array($values)) {
//            $values['company_id'] = OFFICEGEST_COMPANY_ID;
//        }

		//Model::defineValues();
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/' . $action;
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );

		$log = [
			'url'      => $url,
			'sent'     => $values,
			'received' => $res
		];

		self::$logs[] = $log;

		if ( self::$debug ) {
			echo "<pre>";
			print_r( $log );
			echo "</pre>";
		}

		if ( $res['result'] == "ok" ) {
			if ( in_array( $action, self::$allowedCachedMethods ) ) {
				if ( ! isset( self::$cache[ $action ] ) ) {
					self::$cache[ $action ] = $res;
				}
			}

			return $res;
		}
		throw new Error( __( "Ups, foi encontrado um erro..." ), $log );
	}

	public static function post( $action, $values = false ) {
		if ( in_array( $action, self::$allowedCachedMethods ) ) {
			if ( isset( self::$cache[ $action ] ) ) {
				return self::$cache[ $action ];
			}
		}

		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/' . $action;
		$curl->setHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
		$curl->post( $url, $values );
		$parsed = json_decode( $curl->response->json, true );
		Log::write( json_encode( $parsed, true ) );

		$log = [
			'url'      => $url,
			'sent'     => $values,
			'received' => $parsed
		];

		self::$logs[] = $log;

		if ( self::$debug ) {
			echo "<pre>";
			print_r( $log );
			echo "</pre>";
		}

		if ( $parsed['result'] == "ok" ) {
			if ( in_array( $action, self::$allowedCachedMethods ) ) {
				if ( ! isset( self::$cache[ $action ] ) ) {
					self::$cache[ $action ] = $parsed;
				}
			}

			return $parsed;
		}
		throw new Error( __( "Ups, foi encontrado um erro..." ), $log );
	}

	public static function get( $action, $values = false ) {

		if ( in_array( $action, self::$allowedCachedMethods ) ) {
			if ( isset( self::$cache[ $action ] ) ) {
				return self::$cache[ $action ];
			}
		}

		//Model::defineValues();
        $tokensRow = OfficeGestDBModel::getTokensRow();
        $curl      = new Curl();
        $curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
        $url = $tokensRow['domain'] . '/api/' . $action;
		$curl->get( $url );
		$raw    = json_encode( $curl->response );
		$parsed = json_decode( $raw, true );

		$log = [
			'url'      => $url,
			'sent'     => $values,
			'received' => $parsed
		];

		self::$logs[] = $log;

		if ( self::$debug ) {
			echo "<pre>";
			print_r( $log );
			echo "</pre>";
		}

		if ( $parsed['result'] == "ok" ) {
			if ( in_array( $action, self::$allowedCachedMethods ) ) {
				if ( ! isset( self::$cache[ $action ] ) ) {
					self::$cache[ $action ] = $parsed;
				}
			}

			return $parsed;
		}
		throw new Error( __( "Ups, foi encontrado um erro..." ), $log );
	}

	/**
	 * Do a login request to the API
	 *
	 * @param $domain
	 * @param $user
	 * @param $pass
	 * @return array|bool|mixed|object
	 * @throws Error
     */
	public static function login( $domain, $user, $pass ) {
		$curl = new Curl();
		$curl->setBasicAuthentication( $user, $pass );
		$url = $domain . '/api/';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		Log::write( json_encode( $res, true ) );
		if ( isset( $res['result'] ) ) {
			if ( isset( $res['hash'] ) ) {
				return $res;
			}

			return false;
		}
		throw new Error( __( "Ups, foi encontrado um erro...", "A combinação de utilizador/password está errada" ) );
	}

	/**
	 * Get Articles Normals
     *
     * @param bool $variants
	 * @return bool|mixed
     */
	public static function getArticlesNormals( $variants = false ) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/articles?filter[activeforweb]=T&filter[active]=T&filter[articletype]=N';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['total'] > 0 ) {
			return $res['articles'];
		}
		return false;
	}

	public static function getExemptionReasons(){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/tables/exemptionreasons';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] === "ok" && $res['total'] > 0 ) {
			return $res['exemptionreasons'];
		}
		return [];
	}

	public static function getArticlesList($data=null) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		if ($data==null){
			$url = $tokensRow['domain'] . '/api/stocks/articles';
		}
		else{
			$url = $tokensRow['domain'] . '/api/stocks/articles?filter[start_date_change]='.$data;
		}
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] === "ok" && $res['total'] > 0 ) {
			return $res['articles'];
		}
		return [];
	}

	/**
	 * Get Articles Web
     *
     * @param bool $variants
	 * @return bool|mixed
     */
	public static function getArticlesWeb( $variants = false ) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/articles?filter[active]=T&filter[activeforweb]=T&filter[articletype]=N';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['total'] > 0 ) {
			return $res['articles'];
		}
		return false;
	}

    /**
     * Get Articles Web and Services
     *
     * @param bool $variants
     * @return bool|mixed
     */
	public static function getArticlesWebandServices( $variants = false ) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/articles?filter[active]=T&filter[activeforweb]=T';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['total'] > 0 ) {
			return $res['articles'];
		}
		return false;
	}

	public static function getArticlesSubFams() {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/subfamilies';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['total'] > 0 ) {
			return $res['subfamilies'];
		}
		return false;
	}

	/**
	 * Get Article by Reference
     * /api/stocks/articles/ + reference
	 *
	 * @param $reference
	 * @return bool|mixed
     */
	public static function getArticle( $reference ) {
		$result = false;
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/articles/' . base64_encode($reference);
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			$result =  $res['article'];
		}
		return $result;
	}

	public static function createCustomer( $data ) {
		Log::write( 'Data for Customer: ' . json_encode( $data, true ) );
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/entities/customers';
		$curl->post( $url, $data );
		$res = json_decode( json_encode( $curl->response ), true );
		$log = [
			'url'      => $url,
			'sent'     => $data,
			'received' => $res
		];
		self::$logs[] = $log;
		Log::write( 'Create Customer:' . json_encode( $res) );
		if ( $res['result'] === "ok" && $res['code'] == 1000 ) {
			return $res;
		}
		return false;
	}

	public static function updateCustomer( $customer, $data ) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . 'api/entities/customers/' . $customer;
		$curl->post( $url, $data );
		$parsed = json_decode( json_encode( $curl->response ), true );
		Log::write( 'Update existing Customer:' . json_encode( $parsed, true ) );
		if ( $parsed['result'] == "ok" && $parsed['code'] == 1000 ) {
			return $customer;
		}
		return false;
	}

	/**
	 * Get Customer by Vat
     * /api/entities/customers?filter[customertaxid]=
	 *
	 * @param $vat
	 * @return bool|mixed
     */
	public static function getCustomerByVat( $vat ) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/entities/customers?filter[customertaxid]=' . $vat;
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			if ( count( $res['total'] ) > 0 ) {
				return reset( $res['customers'] );
			}
			return reset( $res['customers'] );
		}
		return false;
	}

	/**
	 *  Get Customer by Email
	 *
	 * @param $email
	 * @return bool|mixed
     */
	public static function getCustomerByEmail( $email ) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/entities/customers?filter[email]=' . $email;
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			if ( count( $res['customers'] ) > 0 ) {
				return reset( $res['customers'] );
			}
			return reset( $res['customers'] );
		}
		return false;
	}

	/**
	 * Create document
	 *
	 * @param $documentType
	 * @param $data_to_post
	 * @return array|bool
     */
	public static function criaDocumento( $documentType, $data_to_post ) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		if ( OfficeGestDBModel::getOption( 'document_status' ) === 'draft' ) {
			$res_tipo_doc = 'draft';
			$res_field    = 'draft_number';
			$url          = $tokensRow['domain'] . '/api/sales/drafts/' . $documentType;
		} else {
			$res_tipo_doc = 'document';
			$res_field    = 'document_number';
			$url          = $tokensRow['domain'] . '/api/sales/documents/' . $documentType;
		}

		$curl->post( $url, $data_to_post );
		$res = json_decode( json_encode( $curl->response ), true );
		$log = [
			'url'      => $url,
			'sent'     => $data_to_post,
			'received' => $res
		];
		Log::write( json_encode( $log, true ) );
		self::$logs[] = $log;
		if ( self::$debug ) {
			Tools::debug( $log );
		}
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return [
				'result'   => true,
				'numdoc'   => $res[ $res_field ],
				'document' => $res[ $res_tipo_doc ],
				'lines'    => $res['lines']
			];
		}

		return false;
	}

	public static function criaArtigonoOG($data_to_post){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url  = $tokensRow['domain'] . '/api/stocks/articles';
		$curl->post( $url, $data_to_post );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return [
				'result'   => true,
				'article_id'   => $res['article_id'],
				'article' => $res['article']
			];
		}
		return [
			'result'   => true,
			'article_id'=> null,
			'article' => null
		];
	}

	/**
	 * Get officegest params
     * /api/utils/parameters
	 *
	 * @param string $filter
	 * @return mixed
     */
	public static function getParams( $filter = '' ) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/utils/parameters';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			$res = $filter == '' ? $res['parameters'] : $res['parameters'][ $filter ];
		}
		return $res;
	}

	/**
	 * Get company
     * /api/utils/company
	 *
	 * @param string $filter
	 * @return mixed
     */
	public static function getCompany( $filter = '' ) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/utils/company';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			$res = $filter == '' ? $res['company'] : $res['company'][ $filter ];
		}
		return $res;
	}

	/**
	 * Returns the last curl request made from the logs
	 * @return array
	 */
	public static function getLog() {
		return end( self::$logs );
	}

	/**
	 * Create Basic Auth Header
	 *
	 * @param $apiKey
	 * @param $password
	 *
	 * @return string
	 */
	public static function createBasicAuthHeader( $apiKey, $password ) {
		$input = "{$apiKey}:{$password}";
		$input = base64_encode( $input );
		return "Basic {$input}";
	}

	/**
	 * Get documents
	 *
	 * @param $documentType
	 * @param $numdoc
	 *
	 * @return array
     */
	public static function getDocument( $documentType, $numdoc ) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		if ( OfficeGestDBModel::getOption( 'document_status' ) === 'draft' ) {
			$res_tipo_doc = 'draft';
			$res_field    = 'draft_number';
			$url          = $tokensRow['domain'] . '/api/sales/drafts/' . $documentType . '/' . $numdoc;
		} else {
			$res_tipo_doc = 'document';
			$res_field    = 'document_number';
			$url          = $tokensRow['domain'] . '/api/sales/documents/' . $documentType . '/' . $numdoc;
		}
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		$log = [
			'url'      => $url,
			'received' => $res
		];
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return [
				'result'          => true,
				'document_status' => OfficeGestDBModel::getOption( 'document_status' ),
				'document_type'   => $documentType,
				'numdoc'          => $res[ $res_field ],
				'document'        => $res[ $res_tipo_doc ],
				'lines'           => $res['lines']
			];
		}

		return [ 'result' => false ];
	}

	public static function ecoauto_parts_categories(){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/ecoauto/part_categories';
		$curl->get($url);
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return [
				'categories'=>$res['categories'],
				'total'=>$res['total']
			];
		}
		return [];
	}

	public static function ecoauto_part_components(){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/ecoauto/part_components';
		$curl->get($url);
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return [
				'components'=>$res['components'],
				'total'=>$res['total']
			];
		}
		return [];
	}

	public static function ecoauto_part_types(){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/ecoauto/lpc_types';
		$curl->get($url);
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return [
				'types'=>$res['types'],
				'total'=>$res['total']
			];
		}
		return [];
	}

	public static function ecoauto_part_status(){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/ecoauto/lpc_status';
		$curl->get($url);
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return [
				'status'=>$res['status'],
				'total'=>$res['total']
			];
		}
		return [];
	}

	public static function findDocument( $documentType, $numdoc,$type ) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		if ( $type === 'draft' ) {
			$res_tipo_doc = 'draft';
			$res_field    = 'draft_number';
			$url          = $tokensRow['domain'] . '/api/sales/drafts/' . $documentType . '/' . $numdoc.'?print=O';
		} else {
			$res_tipo_doc = 'document';
			$res_field    = 'document_number';
			$url          = $tokensRow['domain'] . '/api/sales/documents/' . $documentType . '/' . $numdoc.'?print=O';
		}
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return [
				'result'          => true,
				'document_status' => OfficeGestDBModel::getOption( 'document_status' ),
				'document_type'   => $documentType,
				'numdoc'          => $res[ $res_field ],
				'document'        => $res[ $res_tipo_doc ],
				'lines'           => $res['lines']
			];
		}

		return [ 'result' => false ];
	}

	/**
	 * Get countries from officegest
	 * @return array|mixed
	 * @throws \ErrorException
	 */
	public static function getCountries() {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/tables/countries';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return $res['countries'];
		}
		return false;
	}

	public static function getDocumentsTypes() {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/tables/documentstypes?filter[create_api]=T&filter[purchase]=F&filter[visiblemenu]=T&filter[receipt]=F';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return $res['documentstypes'];
		}
		return false;
	}

	public static function getVendors() {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/entities/commercials';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return $res['commercials'];
		}
		return false;
	}

	public static function getClassifications() {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/tables/classifications';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return $res['classifications'];
		}
		return false;
	}

	public static function getStocksFamilies() {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/families';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return $res['families'];
		}
		return false;
	}

	public static function getPricesTables(){
		$tabela_precos=[];
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/prices_table';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			$tabela_precos = $res['prices'];
		}
		return $tabela_precos;
	}

	public static function calculatePrices( $id, $classificacao) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/prices_table_for_article';
		$data=[
			'idarticle'=>$id,
			'classifcode'=>$classificacao,
			'paymentmethod'=>'PP'
		];
		$curl->post( $url,$data );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			$tabela_precos = $res['prices_table_for_article'];
			return $res['prices_table_for_article']['pvp_web'] <= 0 ? $res['prices_table_for_article']['article_pvp'] : $res['prices_table_for_article']['pvp_web'];
		}
	}

	public static function getPickupPoints(){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/addon/delivery/pickup_points';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return $res['pickup_points'];
		}
		return false;
	}

	public static function createFamily($id,$description){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/families';
		$curl->post( $url,[
			'id'=>$id,
			'description'=>$description
		] );
		$res = json_decode( json_encode( $curl->response ), true );
		return $res['result'] == "ok" && $res['code'] == 1000;
	}

	public static function createSubFamily($family_id,$id,$description){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/subfamilies';
		$curl->post( $url,[
			'id'=>$id,
			'familyid'=>$family_id,
			'description'=>$description
		] );
		$res = json_decode( json_encode( $curl->response ), true );
		return $res['result'] == "ok" && $res['code'] == 1000;
	}

	public static function getFamily($description){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/families?filter[description]='.$description;
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		return $res;

	}

	public static function getSubFamily($description){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/subfamilies?filter[description]='.$description;
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		return $res;
	}

	public static function createProduct($product,$family,$subfamily){
		Log::write('Creating Product: '.$product->get_id());
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/stocks/articles';

		$sku = $product->get_sku();
		$barcode = $product->get_sku();
		if (empty($sku)){
			$sku = Tools::createReferenceFromString($product->get_name());
		}
		else{
			$barcode = $product->get_sku();
		}


		if (!OfficeGestCurl::getArticle($sku)){
			$productTaxes = $product->get_tax_class();
			$taxRates = WC_Tax::get_base_tax_rates($productTaxes);
			$id = OfficeGestDBModel::findTaxByValue(array_values($taxRates)[0]['rate']);
			$data=[
				'id'=>$sku,
				'description'=>$product->get_name(),
				'vatid'=>$id,
				'articletype'=>'N',
				'unit'=>'UN',
				'barcode'=>$barcode,
				'sellingprice'=>$product->get_price()
			];
			if (!empty($family)){
				$data['family'] = $family;
			}
			if (!empty($subfamily)){
				$data['$subfamily'] = $subfamily;
			}
			$curl->post( $url,$data);
			$res = json_decode( json_encode( $curl->response ), true );
			if ($res['code']!=1000){
				$status = 'error';
			}
			else{
				$status = 'created';
			}

		}
		else{
			$url = $tokensRow['domain'] . '/api/stocks/articles/'.base64_encode($sku);
			$data = [
				'articletype'=>'N',
				'sellingprice'=>$product->get_price()
			];
			if (!empty($family)){
				$data['family'] = $family;
			}
			if (!empty($subfamily)){
				$data['$subfamily'] = $subfamily;
			}
			$curl->post( $url,$data );
			$res = json_decode( json_encode( $curl->response ), true );
			if ($res['code']!=1000){
				$status = 'error';
			}
			else{
				$status = 'updated';
			}

		}
		Log::write('Result: SKU:'.$sku.' Status:'.$status.' Response: '.json_encode($res));
		return ['sku'=>$sku,'status'=>$status, 'response'=> $res];
	}

	public static function getEcoAutoPhotos( $id ) {
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'] . '/api/ecoauto/parts/photos?filter[component_id]='.$id;
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		return $res['photos'];
	}

	public static function getWorkShopBrands(){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'].'/api/workshop/brands';
		$curl->get( $url );
		$res = json_decode( json_encode( $curl->response ), true );
		if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
			return $res['brands'];
		}
	}

    /*****************************************************************************************************************/

    /*****************************************************************************************************************/

    /*****************************************************************************************************************/

    /*****************************************************************************************************************/

    /*****************************************************************************************************************/

    /*****************************************************************************************************************/

    /*****************************************************************************************************************/

    /**
     * Get all taxes from officegest API
     * /api/tables/vats
     *
     * @return array|mixed
     */
    public static function getVats() {
        $tokensRow = OfficeGestDBModel::getTokensRow();
        $curl      = new Curl();
        $curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
        $url = $tokensRow['domain'] . '/api/tables/vats';
        $curl->get( $url );
        $res = json_decode( json_encode( $curl->response ), true );
        if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
            return $res['vats'];
        }
        return [ 'result' => false ];
    }

    /**
     * Get all officegest article families
     *
     * @return false|mixed
     */
    public static function getArticleFamilies() {
        $tokensRow = OfficeGestDBModel::getTokensRow();
        $curl      = new Curl();
        $curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
        $url = $tokensRow['domain'] . '/api/stocks/families';
        $curl->get( $url );
        $res = json_decode( json_encode( $curl->response ), true );
        if ( $res['result'] == "ok" && $res['total'] > 0 ) {
            return $res['families'];
        }
        return false;
    }

    /**
     * Get all subfamilies from a parent category
     * /api/stocks/subfamilies?filter[familyid]=
     *
     * @param $fam
     * @return array|false
     */
    public static function getArticlesSubFamiliesByFamily( $fam ) {
        $tokensRow = OfficeGestDBModel::getTokensRow();
        $curl      = new Curl();
        $curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
        $url = $tokensRow['domain'] . '/api/stocks/subfamilies?filter[familyid]=' . $fam;
        $curl->get( $url );
        $res = json_decode( json_encode( $curl->response ), true );
        if ( $res['result'] === "ok" && $res['total'] > 0 ) {
            return array_values($res['subfamilies']);
        }
        return false;
    }

    /**
     * Get all stock brands from OfficeGest API;
     * - All brands -> /api/stocks/brands;
     *
     * @return array|mixed
     */
    public static function getStocksBrands() {
        $tokensRow = OfficeGestDBModel::getTokensRow();
        $curl      = new Curl();
        $curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
        $url = $tokensRow['domain'] . '/api/stocks/brands';
        $curl->get( $url );
        $res = json_decode( json_encode( $curl->response ), true );
        if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
            return $res['brands'];
        }
        return [];
    }

    /**
     * Get all ecoauto parts from OfficeGest API;
     * - All parts -> /api/ecoauto/search/parts/inventory;
     *
     * @param $offset
     * @param $limit
     * @param bool $do_limit
     * @return array
     */
    public static function ecoautoPartsInventory($offset,$limit,$do_limit=true){
        $continue = false;
        $tokensRow = OfficeGestDBModel::getTokensRow();
        $curl      = new Curl();
        $curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
        $url = $tokensRow['domain'] . '/api/ecoauto/search/parts/inventory';
        if ($do_limit){
            $args=[
                'limit'=>"$offset,$limit"
            ];
        }
        else{
            $args=[
                'limit'=>0
            ];
        }
        $curl->post($url,$args);
        $res = json_decode( json_encode( $curl->response ), true );

        if ( $res['result'] == "ok" && $res['code'] == 1000 ) {
            return [
                'parts'=>$res['parts'],
                'total'=>$res['total'] ?? 0
            ];
        }
        return [
            'parts'=>[],
            'total'=>0
        ];
    }

    /**
     * Get all ecoauto processes from OfficeGest API;
     * - All processes -> /api/ecoauto/processes;
     *
     * @param $offset
     * @param $limit
     * @return mixed
     */
	public static function getEcoAutoProcesses($offset,$limit){
		$tokensRow = OfficeGestDBModel::getTokensRow();
		$curl      = new Curl();
		$curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
		$url = $tokensRow['domain'].'/api/ecoauto/processes';
		$args=[
			'limit'=>"$offset,$limit"
		];
		$curl->get( $url,$args );
		return json_decode( json_encode( $curl->response ), true );
	}

    /**
     * Get all active and active for web articles from OfficeGest API;
     * - All articles -> /api/stocks/articles;
     * - Filtered active articles -> /api/stocks/articles?filter[active]=T&filter[activeforweb]=T
     *
     * @param $offset
     * @param $limit
     * @return mixed
     */
    public static function getArticles($offset,$limit) {
        $tokensRow = OfficeGestDBModel::getTokensRow();
        $curl      = new Curl();
        $curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
        $url = $tokensRow['domain'] . '/api/stocks/articles?filter[active]=T&filter[activeforweb]=T';
        $args=[
            'limit'=>"$offset,$limit"
        ];
        $curl->get( $url,$args );

        return json_decode( json_encode( $curl->response ), true );
    }

    /**
     * Get all ecoauto parts images
     * /api/ecoauto/search/parts/photos
     *
     * @param $offset
     * @param $limit
     * @param bool $do_limit
     * @return mixed
     */
    public static function getAllEcoautoPartsPhotos($offset,$limit,$do_limit=true){
        $args=[];
        $tokensRow = OfficeGestDBModel::getTokensRow();
        $curl      = new Curl();
        $curl->setBasicAuthentication( $tokensRow['username'], $tokensRow['api_key'] );
        $url = $tokensRow['domain'] . '/api/ecoauto/search/parts/photos';
        if ($do_limit){
            $args=[
                'limit'=>"$offset,$limit",
                'direct_link'=>1
            ];
        }
        else{
            $args=[
                'limit'=>0
            ];
        }
        $curl->post( $url,$args );
        return json_decode( json_encode( $curl->response ), true );
    }

    /**
     * Get all officegest article images
     * /api/
     *
     * @param $article
     * @return mixed
     */
    public static function getAllOfficeGestArticlePhotos($article){
        $tokensRow = OfficeGestDBModel::getTokensRow();
        $photo = $tokensRow['domain'].'/download/?aib='.base64_encode($article['id']);
        return $photo;
    }
}
