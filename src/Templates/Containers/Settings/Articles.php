<?php

use OfficeGest\OfficeGestCurl;

$exemptionReasons=$exemptionReasonsShipping = OfficeGestCurl::getExemptionReasons();
use OfficeGest\OfficeGestDBModel;
$configuracao = OfficeGestDBModel::getOption('general_configuration');
$exemptionReason = OfficeGestDBModel::getOption('exemption_reason');
$article_portes = OfficeGestDBModel::getOption('article_portes');
$exemption_reason_shipping =OfficeGestDBModel::getOption('exemption_reason_shipping');
$articles_taxa = OfficeGestDBModel::getOption('articles_taxa');
$campos_extra_ficha_artigos = OfficeGestDBModel::getOption('campos_extra_ficha_artigos');
$artigos_ean =  OfficeGestDBModel::getOption('artigos_ean');
$artigos_marca =  OfficeGestDBModel::getOption('artigos_marca');
$articles_service = OfficeGestDBModel::getOption('articles_service');
$articles_web_only = OfficeGestDBModel::getOption('articles_web_only');
$pontos_de_recolha = OfficeGestDBModel::getOption('pontos_de_recolha');
$articles_sync_limit = OfficeGestDBModel::getOption('articles_sync_limit');
$article_name = OfficeGestDBModel::getOption('article_name');
$article_description = OfficeGestDBModel::getOption('article_description');
$articles_sync_images_limit = OfficeGestDBModel::getOption('articles_sync_images_limit');
$sync_article_images = OfficeGestDBModel::getOption('sync_article_images');

?>
<table class="form-table">
	<tbody>
        <tr>
            <th scope="row" class="titledesc">
                <label for="exemption_reason"><?= __("Razão de Isenção") ?></label>
            </th>
            <td>
                <select id="exemption_reason" name='opt[exemption_reason]' class='officegest_select2 inputOut'>
                    <option value='' <?= $exemptionReason == '' ? 'selected' : '' ?>><?= __("Nenhuma") ?></option>
                    <?php if (is_array($exemptionReasons)): ?>
                        <?php foreach ($exemptionReasons as $k=>$v) : ?>
                            <option value='<?= $v['codexemptionreasons'] ?>' <?= $exemptionReason == $v['codexemptionreasons'] ? 'selected' : '' ?>><?= $v['codexemptionreasons'] . " - " . $v['description'] ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <p class='description'><?= __('Será usada se os artigos não tiverem uma taxa de IVA') ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row" class="titledesc">
                <label for="article_portes"><?= __("Artigo para Portes") ?></label>
            </th>
            <td>
                <input id="article_portes" name="opt[article_portes]" type="text"  value="<?= $article_portes ?>" style="width: 330px;">
                <p class='description'><?= __('Será usada para os portes no cliente') ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row" class="titledesc">
                <label for="exemption_reason_shipping"><?= __("Razão de Isenção de Portes") ?></label>
            </th>
            <td>
                <select id="exemption_reason_shipping" name='opt[exemption_reason_shipping]' class='officegest_select2 inputOut'>
                    <option value='' <?= $exemption_reason_shipping == '' ? 'selected' : '' ?>><?= __("Nenhuma") ?></option>
	                <?php if (is_array($exemptionReasonsShipping)): ?>
		                <?php foreach ($exemptionReasonsShipping as $k=>$v) : ?>
                            <option value='<?= $v['codexemptionreasons'] ?>' <?= $exemption_reason_shipping == $v['codexemptionreasons'] ? 'selected' : '' ?>><?= $v['codexemptionreasons'] . " - " . $v['description'] ?></option>
		                <?php endforeach; ?>
	                <?php endif; ?>
                </select>
                <p class='description'><?= __('Será usada se os portes não tiverem uma taxa de IVA') ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row" class="titledesc">
                <label for="pontos_de_recolha"><?= __("Pontos de Recolha") ?></label>
            </th>
            <td>
                <select id="pontos_de_recolha" name='opt[pontos_de_recolha]' class='officegest_select2 inputOut'>
                    <option value='0' <?= ($pontos_de_recolha == "0" ? "selected" : "") ?>><?= __('Não') ?></option>
                    <option value='1' <?= ($pontos_de_recolha == "1" ? "selected" : "") ?>><?= __("Sim") ?></option>
                </select>
                <p class='description'><?= __('Usa Pontos de Recolha') ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row" class="titledesc">
                <label for="articles_taxa"><?= __("Artigos c\ Iva Incluido") ?></label>
            </th>
            <td>
                <select id="articles_taxa" name='opt[articles_taxa]' class='officegest_select2 inputOut'>
                    <option value='0' <?= ($articles_taxa == "0" ? "selected" : "") ?>><?= __('Não') ?></option>
                    <option value='1' <?= ($articles_taxa == "1" ? "selected" : "") ?>><?= __("Sim") ?></option>
                </select>
                <p class='description'><?= __('Preço de Artigos com IVA incluido') ?></p>
            </td>
        </tr>
        <?php if ($configuracao >0) {?>
        <tr>
            <th scope="row" class="titledesc">
                <label for="campos_extra_ficha_artigos"><?= __("Campos Extra Ficha de  Artigos") ?></label>
            </th>
            <td>
                <select id="campos_extra_ficha_artigos" name='opt[campos_extra_ficha_artigos]' class='officegest_select2 inputOut'>
                    <option value='0' <?= ($campos_extra_ficha_artigos == "0" ? "selected" : "") ?>><?= __('Não') ?></option>
                    <option value='1' <?= ($campos_extra_ficha_artigos == "1" ? "selected" : "") ?>><?= __("Sim") ?></option>
                </select>
                <p class='description'><?= __('Adiciona campos EAN e MARCA na ficha de Artigos') ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row" class="titledesc">
                <label for="artigos_ean"><?= __("Campo EAN Ficha Artigos") ?></label>
            </th>
            <td>
                <input id="artigos_marca" name='opt[artigos_ean]' class='inputOut' value="<?=$artigos_ean?>">
                <p class='description'><?= __('Campo EAN Ficha de Artigos') ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row" class="titledesc">
                <label for="artigos_marca"><?= __("Campo Marca Ficha Artigos") ?></label>
            </th>
            <td>
                <input id="artigos_marca" name='opt[artigos_marca]' class='inputOut' value="<?=$artigos_marca?>">
                <p class='description'><?= __('Campo EAN Ficha de Artigos') ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row" class="titledesc">
                <label for="article_name"><?= __("Nome do Artigo") ?></label>
            </th>
            <td class="forminp">
                <fieldset>
                    <legend class="screen-reader-text"><span>Titulo do Artigo</span></legend>
                    <textarea rows="3" cols="20" class="input-text wide-input " type="textarea" name="opt[article_name]" id="article_name" style="" placeholder=""><?=$article_name?></textarea>
                    <p class='description'><?= __("Tags Disponiveis: {descricao} {codigo_artigo} {marca}") ?></p>
                </fieldset>
            </td>
        </tr>
        <tr>
            <th scope="row" class="titledesc">
                <label for="article_description"><?= __("Descrição") ?></label>
            </th>
            <td class="forminp">
                <fieldset>
                    <legend class="screen-reader-text"><span>Texto Genérico da Descrição</span></legend>
                    <textarea rows="3" cols="20" class="input-text wide-input " type="textarea" name="opt[article_description]" id="article_description" style="" placeholder=""><?=$article_description?></textarea>
                    <p class='description'><?= __("Tags Disponiveis: {descricao} {codigo_artigo} {marca}") ?></p>
                </fieldset>
            </td>
        </tr>
        <tr>
            <th scope="row" class="titledesc">
                <label for="articles_sync_limit"><?= __("Limite de artigos por sincronização") ?></label>
            </th>
            <td>
                <select id="articles_sync_limit" name='opt[articles_sync_limit]' class='officegest_select2 inputOut'>
                    <option value='20' <?= $articles_sync_limit == '20' ? 'selected' : '20' ?>>20</option>
                    <option value='40' <?= $articles_sync_limit == '40' ? 'selected' : '40' ?>>40</option>
                    <option value='60' <?= $articles_sync_limit == '60' ? 'selected' : '60' ?>>60</option>
                    <option value='80' <?= $articles_sync_limit == '80' ? 'selected' : '80' ?>>80</option>
                    <option value='100' <?= $articles_sync_limit == '100' ? 'selected' : '100' ?>>100</option>
                    <option value='120' <?= $articles_sync_limit == '120' ? 'selected' : '120' ?>>120</option>
                    <option value='140' <?= $articles_sync_limit == '140' ? 'selected' : '140' ?>>140</option>
                    <option value='160' <?= $articles_sync_limit == '160' ? 'selected' : '160' ?>>160</option>
                    <option value='180' <?= $articles_sync_limit == '180' ? 'selected' : '180' ?>>180</option>
                    <option value='200' <?= $articles_sync_limit == '200' ? 'selected' : '200' ?>>200</option>
                </select>
                <p class='description'><?= __('Limite de artigos por sincronização') ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row" class="titledesc">
                <label for="sync_article_images"><?= __("Sincronizar imagens de artigos") ?></label>
            </th>
            <td>
                <select id="sync_article_images" name='opt[sync_article_images]' class='officegest_select2 inputOut'>
                    <option value='0' <?= $sync_article_images == '0' ? 'selected' : '0' ?>>Não</option>
                    <option value='1' <?= $sync_article_images == '1' ? 'selected' : '1' ?>>Sim</option>
                </select>
                <p class='description'><?= __('Sincronizar imagens de artigos') ?></p>
            </td>
        </tr>

            <?php if ($sync_article_images >0) {?>
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="articles_sync_images_limit"><?= __("Limite de imagens de artigos por sincronização") ?></label>
                    </th>
                    <td>
                        <select id="articles_sync_images_limit" name='opt[articles_sync_images_limit]' class='officegest_select2 inputOut'>
                            <option value='20' <?= $articles_sync_images_limit == '20' ? 'selected' : '20' ?>>20</option>
                            <option value='40' <?= $articles_sync_images_limit == '40' ? 'selected' : '40' ?>>40</option>
                            <option value='60' <?= $articles_sync_images_limit == '60' ? 'selected' : '60' ?>>60</option>
                            <option value='80' <?= $articles_sync_images_limit == '80' ? 'selected' : '80' ?>>80</option>
                            <option value='100' <?= $articles_sync_images_limit == '100' ? 'selected' : '100' ?>>100</option>
                            <option value='120' <?= $articles_sync_images_limit == '120' ? 'selected' : '120' ?>>120</option>
                            <option value='140' <?= $articles_sync_images_limit == '140' ? 'selected' : '140' ?>>140</option>
                            <option value='160' <?= $articles_sync_images_limit == '160' ? 'selected' : '160' ?>>160</option>
                            <option value='180' <?= $articles_sync_images_limit == '180' ? 'selected' : '180' ?>>180</option>
                            <option value='200' <?= $articles_sync_images_limit == '200' ? 'selected' : '200' ?>>200</option>
                        </select>
                        <p class='description'><?= __('Limite de imagens de artigos por sincronização') ?></p>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
	</tbody>
</table>
