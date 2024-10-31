<?php

namespace OfficeGest\Menus;

use OfficeGest\OfficeGestDBModel;
use OfficeGest\Plugin;

class Admin
{

    public $parent;

    /**
     *
     * @param Plugin $parent
     */
    public function __construct($parent)
    {
        $this->parent = $parent;
        add_action('admin_menu', [$this, 'admin_menu'], 55.5);
        add_action('admin_notices', '\OfficeGest\Notice::showMessages');
    }

    public function admin_menu()
    {
        if (current_user_can('manage_woocommerce')) {
            $logoDir = OFFICEGEST_IMAGES_URL . 'small_logo.png';
            add_menu_page(__('OfficeGest', 'OfficeGest'), __('OfficeGest', 'OfficeGest'), 'manage_woocommerce', 'officegest', [$this->parent, 'run'], $logoDir, 55.5);
	        $officegest_ecoauto = OfficeGestDBModel::getOption('officegest_ecoauto');
	        if ($officegest_ecoauto==1){
		        add_submenu_page('officegest',__('EcoAuto', 'OfficeGest'), __('EcoAuto', 'OfficeGest'), 'manage_woocommerce', 'ecoauto', [$this->parent, 'run_ecoauto']);
	        }
        }
    }
}
