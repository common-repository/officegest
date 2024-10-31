<?php

namespace OfficeGest\Controllers;

use OfficeGest\Error;
use OfficeGest\Log;
use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;
use WC_Order;

class OrderCustomer
{
	/**
	 * @var WC_Order
	 */
	private $order;

	private $customer_id = false;
	private $vat = '999999990';
	private $email = '';
	private $name = 'Cliente';
	private $contactName = '';
	private $zipCode = '1000-100';
	private $address = 'Desconhecida';
	private $city = 'Desconhecida';
	private $languageId = 1;
	private $countryId = 1;


	/**
	 * List of some invalid vat numbers
	 * @var array
	 */
	private $invalidVats = [
		'999999999',
		'000000000',
		'111111111'
	];

	/**
	 * Documents constructor.
	 * @param WC_Order $order
	 */
	public function __construct($order)
	{
		$this->order = $order;
	}

	/**
	 * @return bool|int
	 * @throws Error
	 * @throws \ErrorException
	 */
	public function create()
	{
		$this->vat = $this->getVatNumber();
		$this->email = $this->order->get_billing_email();
		$values['name'] = $this->getCustomerName();
		$values['address'] = $this->getCustomerBillingAddress();
		$values['zipcode'] = $this->getCustomerZip();
		$values['city'] = $this->getCustomerBillingCity();
		$values['country'] = $this->getCustomerCountryId();
		$values['email'] = $this->order->get_billing_email();
		$values['mobilephone'] = $this->order->get_billing_phone();
		$values['vendorcode'] = OfficeGestDBModel::getOption('CLIENTES_SELLER');
		$values['classifcode'] = OfficeGestDBModel::getOption('CLIENTES_CLASSIFICACAO');
		$customerExists = $this->searchForCustomer();
		$cliente_id=null;
		if (!$customerExists) {
			Log::write('Customer is not found in Officegest. Creating customer...');
			$values['customertaxid'] = $this->vat;
			$result = OfficeGestCurl::createCustomer($values);
			$cliente_id = $result['customer_id'];
		} else {
			Log::write('Customer found in Officegest. Updating customer...');
			$cliente_id = $customerExists;
		}
		if (isset($cliente_id)) {
			$this->customer_id = $cliente_id;
		} else {
			throw new Error(__('Atenção, houve um erro ao inserir o cliente.'));
		}
		return $this->customer_id;
	}

	/**
	 * Get the vat number of an order
	 * Get it from a custom field and validate if Portuguese
	 * @return string
	 */
	public function getVatNumber()
	{
		$vat = '999999990';

		if (defined('VAT_FIELD')) {
			$metaVat = trim($this->order->get_meta(VAT_FIELD));
			if (!empty($metaVat)) {
				$vat = $metaVat;
			}
		}

		$billingCountry = $this->order->get_billing_country();


		// Do some more verifications if the vat number is Portuguese
		if ($billingCountry === 'PT') {
			// Remove the PT part from the beginning
			if (stripos($vat, strtoupper('PT')) === 0) {
				$vat = str_ireplace('PT', '', $vat);
			}

			// Check if the vat is one of this
			if (empty($vat) || in_array($vat, $this->invalidVats, false)) {
				$vat = '999999990';
			}
		}

		$this->vat = $vat;
		return $this->vat;
	}

	/**
	 * Checks if the company name is set
	 * If the order has a company we issue the document to the company
	 * And add the name of the person to the contact name
	 * @return string
	 */
	public function getCustomerName()
	{
		$billingName = $this->order->get_billing_first_name();
		$billingLastName = $this->order->get_billing_last_name();
		if (!empty($billingLastName)) {
			$billingName .= ' ' . $this->order->get_billing_last_name();
		}

		$billingCompany = trim($this->order->get_billing_company());
		if (!empty($billingCompany)) {
			$this->name = $billingCompany;
			$this->contactName = $billingName;
		} elseif (!empty($billingName)) {
			$this->name = $billingName;
		}


		return $this->name;
	}

	/**
	 * Create a customer billing a address
	 * @return string
	 */
	public function getCustomerBillingAddress()
	{
		$billingAddress = trim($this->order->get_billing_address_1());
		$billingAddress2 = $this->order->get_billing_address_2();
		if (!empty($billingAddress2)) {
			$billingAddress .= ' ' . trim($billingAddress2);
		}

		if (!empty($billingAddress)) {
			$this->address = $billingAddress;
		}

		return $this->address;
	}

	/**
	 * Create a customer billing City
	 * @return string
	 */
	public function getCustomerBillingCity()
	{
		$billingCity = trim($this->order->get_billing_city());
		if (!empty($billingCity)) {
			$this->city = $billingCity;
		}

		return $this->city;
	}

	/**
	 * Gets the zip code of a customer
	 * If the customer is Portuguese validate the Vat Number
	 * @return string
	 */
	public function getCustomerZip()
	{
		$zipCode = $this->order->get_billing_postcode();

		if ($this->order->get_billing_country() === 'PT') {
			$zipCode = Tools::zipCheck($zipCode);
		}

		$this->zipCode = $zipCode;
		return $this->zipCode;
	}

	/**
	 * Get the country_id based on a ISO value
	 * @return int
	 * @throws Error
	 */
	public function getCustomerCountryId()
	{
		$countryCode = $this->order->get_billing_country();
		$this->countryId = Tools::getCountryIdFromCode($countryCode);
		return $this->countryId;
	}

	/**
	 * If the country of the customer is one of the available we set it to Portuguese
	 */
	public function getCustomerLanguageId()
	{
		$this->languageId = in_array( $this->countryId, [ 1 ], true ) ? 1 : 2;
		return $this->languageId;
	}

	/**
	 * Search for a customer based on $this->vat or $this->email
	 * @return bool
	 * @throws \ErrorException
	 */
	public function searchForCustomer()
	{
		$result = false;
		if ($this->vat !== '999999990') {
			$searchResult = @OfficeGestCurl::getCustomerByVat($this->vat);
			if ($searchResult!=false) {
				$result = $searchResult['id'];
			}
		} else if (!empty($this->email)) {
			$searchResult = @OfficeGestCurl::getCustomerByEmail($this->email);
			if ($searchResult!=false){
				$result = $searchResult['id'];
			}
		}
		return $result;
	}
}
