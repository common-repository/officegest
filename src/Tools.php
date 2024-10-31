<?php

namespace OfficeGest;

/**
 * Multiple tools for handling recurring tasks
 * Class Tools
 * @package OfficeGest
 */
class Tools
{
	/**
	 * Debug Plugin
	 * @param $log
	 */
	public static function debug($log){
		echo "<pre>". print_r($log,true)."</pre>";
	}

	public static function object_to_array($data)
	{
		if ((! is_array($data)) && (! is_object($data))) {
			return $data;
		}

		$result = array();

		$data = (array) $data;
		foreach ($data as $key => $value) {
			if (is_object($value)) {
				$value = (array) $value;
			}
			if (is_array($value)) {
				$result[ $key ] = self::object_to_array( $value );
			}
			else {
				$result[ $key ] = $value;
			}
		}
		return $result;
	}

    /**
     * @param string $string
     * @param int $productId
     * @param int $variationId
     * @return string
     */
	public static function createReferenceFromString($string, $productId = 0, $variationId = 0)
	{
		$reference = '';
		$name = explode(' ', $string);

		foreach ($name as $word) {
			$reference .= '_' . mb_substr($word, 0, 3);
		}

		if ((int)$productId > 0) {
			$reference .= '_' . $productId;
		}

		if ((int)$variationId > 0) {
			$reference .= '_' . $variationId;
		}

		return mb_substr($reference, 0, 30);
	}

    public static function format_number($valor){
	    return number_format($valor,2);

    }



	/**
	 * Get a tax id given a tax rate
	 * As a fallback if we don't find a tax with the same rate we return the company default
	 *
	 * @param $taxRate
	 *
	 * @return mixed
	 * @throws \ErrorException
	 */
    public static function getTaxIdFromRate($taxRate)
    {
        $defaultTax = 'N';
        $taxesList = OfficeGestCurl::getVats();

	    foreach ($taxesList['vats'] as $tax) {
		    if ((float)$tax['value'] == (float)$taxRate) {
			    return $tax['id'];
		    }
	    }
        return $defaultTax;
    }

	public static function FDate($date, $type = 1) {
		global $l;
		if ($date = strtotime($date)) {
			switch ($type) {
				case 2:
					return date("d/m/Y @ H:i:s", $date);
					break;
				case 3:
					return date("d/m/Y", $date);
					break;
				case 4:
					return date("Y-m-d", $date);
					break;
				case 5:
					return substr(date("c", $date), 0, 19);
					break;
				case 6:
					return date("n", $date);
					break;
				case 1:
				default:
					return date("Y-m-d H:i:s", $date);
					break;
			}
		} else {
			return "N/D";
		}
	}

	public static function slugify($string) {

		$table = array(
			'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
			'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
			'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
			'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
			'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
			'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b',
			'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', '/' => '_', ' ' => '_'
		);

		// -- Remove duplicated spaces
		$stripped = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $string);

		// -- Returns the slug
		return strtolower(strtr($string, $table));

	}

    /**
     * @param $countryCode
     * @return string
     */
    public static function getCountryIdFromCode($countryCode)
    {
        $countriesList = OfficeGestCurl::getCountries();
	    foreach ($countriesList as $country) {
		    if (strtoupper($country["saftid"]) == strtoupper($countryCode)) {
			    return $country['saftid'];
			    break;
		    }
	    }
        return '1';
    }

	/**
	 * @param string $currencyCode
	 * @return int
	 */
	public static function getCurrencyIdFromCode($currencyCode)
	{
		$currenciesList = OfficeGestCurl::getCountries();
		foreach ($currenciesList as $country) {
			if (strtoupper($country["coin"]) == strtoupper($currencyCode)) {
				return $country['coin'];
				break;
			}
		}

		return 1;
	}
	


	public static function validaNIFPortugal($nif, $ignoreFirst=true) {
		//Limpamos eventuais espaços a mais
		$nif = trim( $nif );
		//Verificamos se é numérico e tem comprimento 9
		if ( !is_numeric( $nif ) || strlen( $nif ) != 9 ) {
			return false;
		} else {
			$nifSplit = str_split( $nif );
			//O primeiro digíto tem de ser 1, 2, 5, 6, 8 ou 9
			//Ou não, se optarmos por ignorar esta "regra"
			if (
				in_array( $nifSplit[0], array( 1, 2, 5, 6, 8, 9 ) )
				||
				$ignoreFirst
			) {
				//Calculamos o dígito de controlo
				$checkDigit=0;
				for( $i=0; $i<8; $i++ ) {
					$checkDigit += $nifSplit[$i] * ( 10-$i-1 );
				}
				$checkDigit = 11 - ( $checkDigit % 11 );
				//Se der 10 então o dígito de controlo tem de ser 0
				if( $checkDigit >= 10 ) $checkDigit = 0;
				//Comparamos com o último dígito
				if ( $checkDigit == $nifSplit[8] ) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}


    /**
     * @param $input
     * @return string
     */
    public static function zipCheck($input)
    {
	    $zipCode = trim(str_replace(' ', '', $input));
	    $zipCode = preg_replace('/[^0-9]/', '', $zipCode);
	    if (strlen($zipCode) == 7) {
		    $zipCode = $zipCode[0] . $zipCode[1] . $zipCode[2] . $zipCode[3] . '-' . $zipCode[4] . $zipCode[5] . $zipCode[6];
	    }
	    if (strlen($zipCode) == 6) {
		    $zipCode = $zipCode[0] . $zipCode[1] . $zipCode[2] . $zipCode[3] . '-' . $zipCode[4] . $zipCode[5] . '0';
	    }
	    if (strlen($zipCode) == 5) {
		    $zipCode = $zipCode[0] . $zipCode[1] . $zipCode[2] . $zipCode[3] . '-' . $zipCode[4] . '00';
	    }
	    if (strlen($zipCode) == 4) {
		    $zipCode = $zipCode . '-' . '000';
	    }
	    if (strlen($zipCode) == 3) {
		    $zipCode = $zipCode . '0-' . '000';
	    }
	    if (strlen($zipCode) == 2) {
		    $zipCode = $zipCode . '00-' . '000';
	    }
	    if (strlen($zipCode) == 1) {
		    $zipCode = $zipCode . '000-' . '000';
	    }
	    if (strlen($zipCode) == 0) {
		    $zipCode = '1000-100';
	    }
	    if (self::finalCheck($zipCode)) {
		    return $zipCode;
	    }
	    return '1000-100';
    }

    /**
     * Validate a Zip Code format
     * @param string $zipCode
     * @return bool
     */
    private static function finalCheck($zipCode)
    {
        $regexp = "/[0-9]{4}\-[0-9]{3}/";
        if (preg_match($regexp, $zipCode)) {
            return (true);
        }

        return (false);
    }

	public static function checkIfDomainisAlive($domain){
	    $file = $domain;
	    $file_headers = @get_headers($file);
		return ! ( ! $file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found' );
	}

	public static function checkDomain( $domain ) {
		return rtrim($domain, '/');
	}

	public static function validaTab($tab){
		$tab_get = isset($_GET['tab']) ? $_GET['tab'] : '';
		return $tab_get == $tab;
	}

	public static function getDocName( $_officegest_doctype ) {
		$documentSets = OfficeGestCurl::get( 'tables/documentstypes?filter[create_api]=T&filter[purchase]=F&filter[visiblemenu]=T&filter[receipt]=F', []);
		foreach ($documentSets['documentstypes'] as $k=>$v) {
    		if ($v['codabbreviated']==$_officegest_doctype){
    			return $v['description'];
		    }
	    }
    	return '';
	}

	public static function filtrar($array,$serch_field,$search_value,$search_op){
		return array_values(array_filter($array, new ArraySearcher($serch_field, $search_op, $search_value,'','','')));
	}

	public static function get_category( $id ) {
		return get_term_by( 'id', $id, 'product_cat');
	}

	public static function updateArticles(){
		global $wpdb;
		$updated  = 0;
		$inserted=0;

		$updatedProducts = OfficeGestDBModel::getAllOfficeGestProducts();

		foreach ( $updatedProducts as $product ) {
			$cat = $wpdb->get_row( 'SELECT * FROM ' . TABLE_OFFICEGEST_ARTICLES . ' where id="' . $product['id'] . '"', ARRAY_A );

			if ( ! empty( $cat ) ) {
				$wpdb->update( TABLE_OFFICEGEST_ARTICLES, $product, [
					'id' => $product['id']
				] );
				$updated++;

			} else {
				$wpdb->insert( TABLE_OFFICEGEST_ARTICLES, $product );
				$inserted++;
			}
		}
		return [
			'updated'=>$updated,
			'inserted'=>$inserted
		];
	}

}

class ArraySearcher {

	const OP_EQUALS = '==';
	const OP_GREATERTHAN = '>';
	const OP_GREATEREQUALTHAN = '>=';
	const OP_LOWEREQUALTHAN = '<=';
	const OP_LOWERTHAN = '<';
	const OP_NOT = '!=';

	private $_field;
	private $_field2;
	private $_operation;
	private $_operation2;
	private $_val;
	private $_val2;

	public function __construct($field, $operation, $num, $field2, $operation2, $num2) {
		$this->_field = $field;
		$this->_field2 = !empty($field2)?$field2:'';
		$this->_operation = $operation;
		$this->_operation2 =  !empty($operation2)?$operation2:'';
		$this->_val = $num;
		$this->_val2 =  !empty($num2)?$num2:'';
	}

	function __invoke($i) {
		switch ($this->_operation) {
			case '==':
				return $i[$this->_field] == $this->_val;
			case '>':
				return $i[$this->_field] > $this->_val;
			case '<':
				return $i[$this->_field] < $this->_val;
			case '>=':
				return $i[$this->_field] >= $this->_val;
			case '<=':
				return $i[$this->_field] <= $this->_val;

			case '!=':
				return $i[$this->_field] != $this->_val;
		}
		if (!empty($this->_operation2)){
			switch ($this->_operation2) {
				case '==':
					return $i[$this->_field2] == $this->_val2;

				case '>':
					return $i[$this->_field2] > $this->_val2;

				case '>=':
					return $i[$this->_field2] >= $this->_val2;
				case '<=':
					return $i[$this->_field2] <= $this->_val2;

				case '<':
					return $i[$this->_field2] < $this->_val2;

				case '!=':
					return $i[$this->_field2] != $this->_val2;
			}
		}

	}

}
