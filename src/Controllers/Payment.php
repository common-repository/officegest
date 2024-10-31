<?php
namespace OfficeGest\Controllers;


use OfficeGest\OfficeGestCurl;
use OfficeGest\Error;

class Payment
{
    public $payment_method_id;
    public $name;
    public $value = 0;

    /**
     * Payment constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = trim($name);
    }

    /**
     * This method SHOULD be replaced by a productCategories/getBySearch
     * @throws Error
     */
    public function loadByName()
    {
        $paymentMethods = OfficeGestCurl::simple('paymentMethods/getAll', []);
        if (!empty($paymentMethods) && is_array($paymentMethods)) {
            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod['name'] === $this->name) {
                    $this->payment_method_id = $paymentMethod['payment_method_id'];
                    return $this;
                }
            }
        }

        return false;
    }


    /**
     * Create a Payment Methods based on the name
     * @throws Error
     */
    public function create()
    {
        $insert = OfficeGestCurl::simple('paymentMethods/insert', $this->mapPropsToValues());

        if (isset($insert['payment_method_id'])) {
            $this->payment_method_id = $insert['category_id'];
            return $this;
        }

        throw new Error(__('Erro ao inserir a método de pagamento') . $this->name);
    }


    /**
     * Map this object properties to an array to insert/update a officegest Payment Value
     * @return array
     */
    private function mapPropsToValues()
    {
        $values = [];

        $values['name'] = $this->name;

        return $values;
    }
}