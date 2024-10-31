<?php

use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;

$configuracao = OfficeGestDBModel::getOption('general_configuration');
?>
    <div class="header">
        <img class="img" src="<?= OFFICEGEST_IMAGES_URL ?>logo.png" width='300px' alt="OfficeGest">
    </div>
<?php
    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';

    settings_errors();
?>

<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
    <a href="<?= admin_url('admin.php?page=ecoauto') ?>"
       class="nav-tab <?= (Tools::validaTab('')==true) ? 'nav-tab-active' : '' ?>">
		<?= __('Inventário de Peças') ?>
    </a>

    <a href="<?= admin_url('admin.php?page=ecoauto&tab=utils') ?>"
       class="nav-tab <?= (Tools::validaTab('utils')==true) ? 'nav-tab-active' : '' ?>">
        <?= __('Utilitários') ?>
    </a>

</nav>

<?php
$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
switch ($tab) {
	case 'utils':
		include OFFICEGEST_TEMPLATE_DIR . "EcoAuto/Utils.php";
		break;
	case 'ecoauto':
    default:
        include OFFICEGEST_TEMPLATE_DIR . "EcoAuto/Ecoauto.php";
        break;
}

