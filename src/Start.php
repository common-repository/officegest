<?php

namespace OfficeGest;

/**
 * Class Start
 * This is one of the main classes of the module
 * Every call should pass here before
 * This will render the login form or the company form or it will return a bol
 * This will also handle the tokens
 * @package OfficeGest
 */
class Start
{
    /** @var bool */
    private static $ajax = false;

    public static function debug($log){
        echo '<pre>';
        print_r($log);
        echo '</pre>';
    }

	/**
	 * Handles session, login and settings
	 *
	 * @param bool $ajax
	 *
	 * @return bool
	 * @throws Error*@throws \ErrorException
	 * @throws \ErrorException
	 */
    public static function login($ajax = false)
    {
	    $action = isset($_REQUEST['action']) ? sanitize_text_field(trim($_REQUEST['action'])) : '';
	    $username = isset($_POST['user']) ? sanitize_text_field((trim($_POST['user']))) : '';
	    $password = isset($_POST['pass']) ? stripslashes(sanitize_text_field(trim($_POST['pass']))) : '';
	    $domain = isset($_POST['domain']) ? stripslashes(sanitize_text_field(trim($_POST['domain']))) : '';
	    if (isset($_POST['domain']) && $domain!=''){
	    	if (Tools::checkIfDomainisAlive($domain)==false){
			    self::loginForm(__("Não foi possivél ligar-se ao OfficeGest. Verifique se o seu servidor se encontra válido!"));
			    return false;
		    }
	    	$domain = Tools::checkDomain($domain);

	    }
        if ($ajax) {
            self::$ajax = true;
        }

        if (!empty($username) && !empty($password) && !empty($domain)) {
            $login = OfficeGestCurl::login($domain,$username, $password);
            if ($login==false){
                self::loginForm(__("Combinação de utilizador/password errados"));
                return false;
            }
            if (isset($login['hash'])) {
                OfficeGestDBModel::setTokens($domain,$username,$password,$login["hash"]);

            }
        }

        if ($action === 'logout') {
            OfficeGestDBModel::resetTokens();
        }

        if ($action === 'save') {
	        add_settings_error('general', 'settings_updated', __('Alterações guardadas.'), 'updated');
            $options = $_POST['opt'];

            foreach ($options as $option => $value) {
                $option = sanitize_text_field($option);
                $value = sanitize_text_field($value);
                OfficeGestDBModel::setOption($option, $value);
            }
        }

	    $tokensRow = OfficeGestDBModel::getTokensRow();
	    if (!empty($tokensRow['api_key']) && !empty($tokensRow['username']) && !empty($tokensRow['password'])) {
		    //OfficeGestDBModel::prepare_articles_external_tables();
		    OfficeGestDBModel::defineValues();
		    OfficeGestDBModel::defineConfigs();
		    return true;
	    }
	    self::loginForm();
	    return false;

    }


    /**
     * Shows a login form
     * @param bool|string $error Is used in include
     */
    public static function loginForm($error = false)
    {
        if (!self::$ajax) {
            include(OFFICEGEST_TEMPLATE_DIR . "LoginForm.php");
        }
    }

}
