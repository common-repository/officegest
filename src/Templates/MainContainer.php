<?php

use OfficeGest\Log;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\Tools;

$configuracao = OfficeGestDBModel::getOption('general_configuration');
?>
<div class="header">
    <img src="<?= OFFICEGEST_IMAGES_URL ?>logo.png" width='300px' alt="OfficeGest">
</div>
<?php
    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
    settings_errors();
?>

<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
    <a href="<?= admin_url('admin.php?page=officegest') ?>"
       class="nav-tab <?= ( Tools::validaTab('')==true) ? 'nav-tab-active':'' ?>">
        <?= __('Encomendas Pendentes') ?>
    </a>

    <a href="<?= admin_url('admin.php?page=officegest&tab=in_og') ?>"
       class="nav-tab <?= (Tools::validaTab('in_og')==true) ? 'nav-tab-active':'' ?>">
		<?= __('Documentos Gerados') ?>
    </a>
    <a href="<?= admin_url('admin.php?page=officegest&tab=settings') ?>"
       class="nav-tab <?= (Tools::validaTab('settings')==true) ? 'nav-tab-active' : '' ?>">
        <?= __('Configurações') ?>
    </a>
<?php if ($configuracao >0) { ?>
    <a href="<?= admin_url('admin.php?page=officegest&tab=articles') ?>"
       class="nav-tab <?= (Tools::validaTab('articles')==true) ? 'nav-tab-active' : '' ?>">
		<?= __('Artigos') ?>
    </a>
    <a href="<?= admin_url('admin.php?page=officegest&tab=utils') ?>"
       class="nav-tab <?= (Tools::validaTab('utils')==true) ? 'nav-tab-active' : '' ?>">
		<?= __('Utilitários') ?>
    </a>
<?php } ?>

    <a href="<?= admin_url('admin.php?page=officegest&tab=tools') ?>"
       class="nav-tab <?= (Tools::validaTab('tools')==true) ? 'nav-tab-active' : '' ?>">
        <?= __('Ferramentas') ?>
    </a>

    <a href="<?= admin_url('admin.php?page=officegest&tab=crons') ?>"
       class="nav-tab <?= (Tools::validaTab('crons')==true) ? 'nav-tab-active' : '' ?>">
        <?= __('Crons') ?>
    </a>

</nav>

<?php
$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
switch ($tab) {
	case 'in_og':
		include OFFICEGEST_TEMPLATE_DIR . "Containers/ProcessedOrders.php";
		break;
	case 'articles':
		include OFFICEGEST_TEMPLATE_DIR . "Containers/Articles.php";
		break;
    case 'tools':
        include OFFICEGEST_TEMPLATE_DIR . "Containers/Tools.php";
        break;
	case 'utils':
		include OFFICEGEST_TEMPLATE_DIR . "Containers/Utils.php";
		break;
    case 'settings':
        include OFFICEGEST_TEMPLATE_DIR . "Containers/Settings.php";
        break;
    case 'crons':
        include OFFICEGEST_TEMPLATE_DIR . "Containers/Crons.php";
        break;
    default:
        include OFFICEGEST_TEMPLATE_DIR . "Containers/PendingOrders.php";
        break;
}

