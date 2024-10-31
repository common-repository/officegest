<?php use \OfficeGest\OfficeGestCurl; ?>

<?php
    $company = OfficeGestCurl::get( 'utils/company', []);
    $sep = isset($_GET['sep']) ? sanitize_text_field($_GET['sep']) : 'documents';
    if (!in_array($sep,['documents','articles','clients','autofact','ecoauto'])){
        $sep = 'documents';
    }
?>

<form method='POST' action='<?= admin_url('admin.php?page=officegest&tab=settings') ?>' id='formOpcoes'>
    <input type='hidden' value='save' name='action'>
    <div>
        <ul class="subsubsub">
            <li>
                <a href="<?= admin_url('admin.php?page=officegest&tab=settings&sep=documents') ?>" class="<?= ($sep === 'documents' || $sep=='') ? 'current' : '' ?>">
			    <?= __('Documentos') ?> |
                </a>
            </li>
            <li>
                <a  href="<?= admin_url('admin.php?page=officegest&tab=settings&sep=articles') ?>" class="nav-tab-small <?= ($sep === 'articles') ? 'current' : '' ?>">
		        <?= __('Artigos') ?> |
                </a>
            </li>
            <li>
                <a href="<?= admin_url('admin.php?page=officegest&tab=settings&sep=clients') ?>" class="nav-tab-small <?= ($sep === 'clients') ? 'current' : '' ?>">
		        <?= __('Clientes') ?> |
                </a>
            </li>
            <li>
                <a href="<?= admin_url('admin.php?page=officegest&tab=settings&sep=autofact') ?>" class="nav-tab-small <?= ($sep === 'autofact') ? 'current' : '' ?>">
			        <?= __('Automatismos') ?> |
                </a>
            </li>
            <li>
                <a href="<?= admin_url('admin.php?page=officegest&tab=settings&sep=ecoauto') ?>" class="nav-tab-small <?= ($sep === 'ecoauto') ? 'current' : '' ?>">
			        <?= __('Ecoauto') ?>
                </a>
            </li>
        </ul>
        <br class="clear">
	    <?php
            switch ($sep) {
                default:
                case 'documents':
                    include OFFICEGEST_TEMPLATE_DIR . 'Containers/Settings/Documents.php';
                    break;
                case 'articles':
                    include OFFICEGEST_TEMPLATE_DIR . 'Containers/Settings/Articles.php';
                    break;
                case 'clients':
                    include OFFICEGEST_TEMPLATE_DIR . 'Containers/Settings/Clients.php';
                    break;
	            case 'autofact':
		            include OFFICEGEST_TEMPLATE_DIR . 'Containers/Settings/Autofact.php';
		            break;
	            case 'ecoauto':
		            include OFFICEGEST_TEMPLATE_DIR . 'Containers/Settings/EcoAuto.php';
		            break;
            }
	    ?>
            <tr>
                <th></th>
                <td>
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?= __( 'Guardar alterações' ) ?>">
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</form>