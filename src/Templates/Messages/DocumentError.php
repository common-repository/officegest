<div>
    <div id="message" class="updated error is-dismissible">
        <p><?= $message ?></p>
        <a onclick="showOfficeGestErrors()" style="cursor: pointer;">
            <p><?= __("Clique aqui para mais informações") ?></p>
        </a>

        <div id="OfficeGestConsoleLogError" style="display: none;">           
            <b><?= __("Endpoint") ?>: </b> <?= $url ?>
            <br>
            
            <b><?= __("Resposta recebida: ") ?></b>
            <br/>
            <pre><?= /** @var array $received */
                print_r($received, true) ?>
            </pre>

            <b><?= __("Dados enviados: ") ?></b>
            <br/>
            <pre><?= /** @var array $sent */
                print_r($sent, true) ?>
            </pre>
        </div>
    </div>
</div>

<script>
    function showOfficeGestErrors() {
        var errorConsole = document.getElementById("OfficeGestConsoleLogError");
        errorConsole.style['display'] = errorConsole.style['display'] === 'none' ? 'block' : 'none';
    }
</script>