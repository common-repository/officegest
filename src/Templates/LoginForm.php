<div id='formLogin'>
    <a href='https://officegest.pt' target='_BLANK'>
        <img src="<?= OFFICEGEST_IMAGES_URL ?>logo.png" width='300px' alt="OfficeGest">
    </a>
    <hr>
    <form id='formPerm' method='POST' action='<?= admin_url('admin.php?page=officegest') ?>'>
        <table>
            <tr>
                <td><label for='domain'><?= __("Dominio") ?></label></td>
                <td><input id="domain" type='text' name='domain'></td>
            </tr>

            <tr>
                <td><label for='username'><?= __("Utilizador") ?></label></td>
                <td><input id="username" type='text' name='user'></td>
            </tr>

            <tr>
                <td><label for='password'><?= __("Password") ?></label></td>
                <td><input id="password" type='password' name='pass'></td>
            </tr>

            <?php if ($error): ?>
                <tr>
                    <td></td>
                    <td style='text-align: center;'><?= $error ?></td>
                </tr>
            <?php endif; ?>

            <tr>
                <td></td>
                <td>
                    <div>

                        <input type='submit' name='submit' value='<?= __("Entrar") ?>'>
                    </div>
                </td>
            </tr>
        </table>
    </form>
</div>
