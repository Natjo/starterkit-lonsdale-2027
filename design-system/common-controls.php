<?php
/** Controls belong to the design-system header, never the site demonstration. */
$navigation_descriptions = [
    'simple' => 'Un seul niveau de liens, sans sous-menu.',
    'dropdown' => 'Des sous-menus déroulants, avec plusieurs niveaux possibles.',
    'mega' => 'Un panneau pleine largeur affiche les sous-pages (2 niveaux).',
    'mega-columns' => 'Un panneau en colonnes : rubriques au niveau 2 et liens au niveau 3.',
];
?>
<section class="forge-common-controls" aria-label="Configuration du header et du footer">
    <form action="<?= esc_url(forge_ds_page('common')) ?>" method="get" data-common-choices>
        <?php if ($selected_common !== '') : ?><input type="hidden" name="forge_common" value="<?= esc_attr($selected_common) ?>"><?php endif; ?>
        <fieldset class="forge-common-config-column"><legend>Header Nav</legend>
        <?php if ($configurable) : ?>
            <label><select name="header_navigation" aria-label="Type de navigation" aria-describedby="common-navigation-description">
                <?php foreach (['simple'=>'Simple','dropdown'=>'Flyout','mega'=>'Reveal','mega-columns'=>'Mega'] as $value => $label) : ?>
                    <option value="<?= esc_attr($value) ?>" data-description="<?= esc_attr($navigation_descriptions[$value]) ?>"<?= selected($header_config['navigation'], $value, false) ?>><?= esc_html($label) ?></option>
                <?php endforeach; ?>
            </select></label>
            <p class="forge-common-description" id="common-navigation-description" data-common-navigation-description aria-live="polite"><?= esc_html($navigation_descriptions[$header_config['navigation']]) ?></p>
            <?php foreach (['search'=>'Recherche','buttons'=>'Actions','links'=>'Menu secondaire'] as $key=>$label) : ?>
                <label class="forge-common-switch"><input type="checkbox" role="switch" name="header_<?= esc_attr($key) ?>" value="1"<?= checked($header_config[$key], true, false) ?>><span class="forge-common-switch-track" aria-hidden="true"></span><span><?= esc_html($label) ?></span></label>
            <?php endforeach; ?>
        <?php endif; ?>
        </fieldset>
        <fieldset class="forge-common-config-column"><legend>Footer</legend>
        <?php if ($footer_configurable) : ?>
        <input type="hidden" name="forge_footer" value="footer">
        <input type="hidden" name="footer_config" value="1">
        <label><select name="footer_type" aria-label="Type de footer" aria-describedby="common-footer-description">
            <option value="simple"<?= selected($footer_config['type'],'simple',false) ?>>Simple</option>
            <option value="columns"<?= selected($footer_config['type'],'columns',false) ?>>Columns</option>
        </select></label>
        <p class="forge-common-description" id="common-footer-description" data-common-footer-description aria-live="polite"><?= $footer_config['type']==='simple'?'Un footer compact avec ses liens sur un niveau.':'Jusqu’à trois colonnes : titres au premier niveau, liens au second.' ?></p>
        <?php foreach (['socials'=>'Liens sociaux','secondaryMenu'=>'Menu footer secondaire','copyright'=>'Copyright','text'=>'Texte','newsletter'=>'Newsletter','search'=>'Recherche'] as $key=>$label) :
            $supported = !(in_array($key,['text','newsletter','search'],true) && $footer_config['type'] === 'simple'); ?>
            <label class="forge-common-switch" data-footer-option="<?= esc_attr($key) ?>"<?= $supported?'':' hidden' ?>><input type="checkbox" role="switch" name="footer_<?= esc_attr($key) ?>" value="1"<?= checked($footer_config[$key],true,false) ?><?= $supported?'':' disabled' ?>><span class="forge-common-switch-track" aria-hidden="true"></span><span><?= esc_html($label) ?></span></label>
        <?php endforeach; ?>
        <?php else : ?>
        <label>Version<select name="forge_footer"<?= $footers === [] ? ' disabled' : '' ?>>
            <?php if ($footers === []) : ?><option>Aucun footer disponible</option><?php endif; ?>
            <?php foreach ($footers as $name => $definition) : ?>
                <option value="<?= esc_attr($name) ?>"<?= selected($name, $footer, false) ?>><?= esc_html($name) ?></option>
            <?php endforeach; ?>
        </select></label>
        <?php endif; ?>
        </fieldset>
        <p class="forge-common-feedback" role="status" hidden data-common-feedback></p>
        <button type="submit" data-common-apply>Appliquer</button>
    </form>
</section>
