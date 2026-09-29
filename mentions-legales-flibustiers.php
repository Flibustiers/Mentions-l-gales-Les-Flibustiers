<?php
/*
Plugin Name: Mentions Légales
Description: Plugin pour gérer les mentions légales du site via un shortcode.
Version: 1.10.0
Author: Anthony REBOURS - Développeur web chez Les Flibustiers
*/

if (!defined('ABSPATH')) exit;

// Permet de vérifier la version pour MAJ auto du plugin
require plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php';

// Durée de conservation par défaut des données de contact
define('ML_DUREE_CONSERVATION_DEFAUT', '3 ans à compter de notre dernier échange');

// Fonction pour ajouter le menu dans l'admin
function ml_add_admin_menu() {
    add_menu_page('Mentions Légales', 'Mentions Légales', 'manage_options', 'mentions_legales', 'ml_settings_page', 'dashicons-info');
}
add_action('admin_menu', 'ml_add_admin_menu');

// Enregistrement des paramètres
function ml_register_settings() {
    $text = array('sanitize_callback' => 'sanitize_text_field');
    $url = array('sanitize_callback' => 'esc_url_raw');

    register_setting('ml_settings_group', 'ml_site_internet', $text);
    register_setting('ml_settings_group', 'ml_proprietaire', $text);
    register_setting('ml_settings_group', 'ml_siret', $text);
    register_setting('ml_settings_group', 'ml_contact_url', $url);
    register_setting('ml_settings_group', 'ml_responsable_publication', $text);
    register_setting('ml_settings_group', 'ml_hebergeur', $text);
    register_setting('ml_settings_group', 'ml_hebergeur_adresse', $text);
    register_setting('ml_settings_group', 'ml_hebergeur_telephone', $text);
    register_setting('ml_settings_group', 'ml_hebergeur_lien', $url);
    register_setting('ml_settings_group', 'ml_activite', $text);
    register_setting('ml_settings_group', 'ml_adresse', $text);
    register_setting('ml_settings_group', 'ml_telephone', $text);
    register_setting('ml_settings_group', 'ml_cookies_policy', $url);
    register_setting('ml_settings_group', 'ml_duree_conservation', $text);
}
add_action('admin_init', 'ml_register_settings');

// Interface d'administration
function ml_settings_page() {
    ?>
    <div class="wrap">
        <h1>Paramètres des Mentions Légales</h1>
        <form method="post" action="options.php">
            <?php settings_fields('ml_settings_group'); ?>
            <?php do_settings_sections('ml_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Site Internet</th>
                    <td><input type="text" name="ml_site_internet" value="<?php echo esc_attr(get_option('ml_site_internet')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Propriétaire</th>
                    <td><input type="text" name="ml_proprietaire" value="<?php echo esc_attr(get_option('ml_proprietaire')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">SIRET</th>
                    <td><input type="text" name="ml_siret" value="<?php echo esc_attr(get_option('ml_siret')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Activité</th>
                    <td><input type="text" name="ml_activite" value="<?php echo esc_attr(get_option('ml_activite')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Adresse</th>
                    <td><input type="text" name="ml_adresse" value="<?php echo esc_attr(get_option('ml_adresse')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Téléphone</th>
                    <td><input type="text" name="ml_telephone" value="<?php echo esc_attr(get_option('ml_telephone')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Lien vers la page de contact</th>
                    <td><input type="text" name="ml_contact_url" value="<?php echo esc_attr(get_option('ml_contact_url')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Lien vers la politique de cookies</th>
                    <td><input type="text" name="ml_cookies_policy" value="<?php echo esc_attr(get_option('ml_cookies_policy')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Durée de conservation des données de contact</th>
                    <td>
                        <input type="text" class="regular-text" name="ml_duree_conservation" value="<?php echo esc_attr(get_option('ml_duree_conservation')); ?>" placeholder="<?php echo esc_attr(ML_DUREE_CONSERVATION_DEFAUT); ?>" />
                        <p class="description">Laisser vide pour utiliser : « <?php echo esc_html(ML_DUREE_CONSERVATION_DEFAUT); ?> ».</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Responsable de publication</th>
                    <td><input type="text" name="ml_responsable_publication" value="<?php echo esc_attr(get_option('ml_responsable_publication')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Hébergeur du site</th>
                    <td><input type="text" name="ml_hebergeur" value="<?php echo esc_attr(get_option('ml_hebergeur')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Adresse de l'hébergeur</th>
                    <td><input type="text" class="regular-text" name="ml_hebergeur_adresse" value="<?php echo esc_attr(get_option('ml_hebergeur_adresse')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Téléphone de l'hébergeur</th>
                    <td><input type="text" name="ml_hebergeur_telephone" value="<?php echo esc_attr(get_option('ml_hebergeur_telephone')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Lien vers l'hébergeur du site</th>
                    <td><input type="text" name="ml_hebergeur_lien" value="<?php echo esc_attr(get_option('ml_hebergeur_lien')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <p>Pour intégrer vos mentions légales, vous pouvez utiliser le shortcode <code>[mentions_legales]</code>.</p>
    </div>
    <?php
}

// Contenu des mentions légales avec shortcode
function ml_generate_legal_mentions() {
    $site_internet = esc_html(get_option('ml_site_internet'));
    $proprietaire = esc_html(get_option('ml_proprietaire'));
    $siret = esc_html(get_option('ml_siret'));
    $adresse = esc_html(get_option('ml_adresse'));
    $telephone = esc_html(get_option('ml_telephone'));
    $contact_url = esc_url(get_option('ml_contact_url'));
    $cookies_policy = esc_url(get_option('ml_cookies_policy'));
    $responsable_publication = esc_html(get_option('ml_responsable_publication'));
    $hebergeur = esc_html(get_option('ml_hebergeur'));
    $hebergeur_adresse = esc_html(get_option('ml_hebergeur_adresse'));
    $hebergeur_telephone = esc_html(get_option('ml_hebergeur_telephone'));
    $hebergeur_url = esc_url(get_option('ml_hebergeur_lien'));
    $hebergeur_lien = esc_html(get_option('ml_hebergeur_lien'));
    $activite = esc_html(get_option('ml_activite'));
    $duree_conservation = esc_html(get_option('ml_duree_conservation') ?: ML_DUREE_CONSERVATION_DEFAUT);

    $adresse_field = $adresse ? "<p>Adresse : $adresse</p>" : '';
    $telephone_field = $telephone ? "<p>Téléphone : $telephone</p>" : '';
    $hebergeur_adresse_field = $hebergeur_adresse ? "<p>Adresse : $hebergeur_adresse</p>" : '';
    $hebergeur_telephone_field = $hebergeur_telephone ? "<p>Téléphone : $hebergeur_telephone</p>" : '';
    $hebergeur_lien_field = $hebergeur_url ? "<p>Plus d’informations sur l’hébergement : <a href='$hebergeur_url' target='_blank' rel='noopener'>$hebergeur_lien</a></p>" : '';
    $cookies_policy_field = $cookies_policy ? "<p>Pour connaître la liste des cookies utilisés, leur finalité et leur durée de conservation, et pour modifier vos choix, consultez notre <a href='$cookies_policy'>politique de cookies</a>.</p>" : '';

    return "
    <h2>Identification</h2>
    <p>Propriétaire : $proprietaire</p>
    $adresse_field
    $telephone_field
    <p>Adresse e-mail : <a href='$contact_url'>cliquez ici pour accéder au formulaire</a></p>
    <p>Responsable de publication : $responsable_publication</p>
    <p>Webmaster : <a href='https://les-flibustiers.fr' target='_blank' rel='noopener'>• Les Flibustiers •</a></p>
    <p>Créateur du site : <a href='https://les-flibustiers.fr' target='_blank' rel='noopener'>• Les Flibustiers •</a></p>

    <h2>Hébergement</h2>
    <p>Hébergeur du site : $hebergeur</p>
    $hebergeur_adresse_field
    $hebergeur_telephone_field
    $hebergeur_lien_field

    <h2>Activité</h2>
    <p>$site_internet est identifié sous le numéro de siret $siret dont l’activité est $activite.</p>

    <h2>Utilisation de cookies</h2>
    <p>Un cookie est un petit fichier déposé sur votre terminal lors de la consultation d’un site internet. Il permet au site de reconnaître votre navigateur pendant une durée limitée et d’y conserver certaines informations.</p>
    <p>Certains cookies sont strictement nécessaires au fonctionnement de $site_internet. Les autres, comme les outils de mesure d’audience, ne sont déposés qu’avec votre consentement, que vous pouvez retirer à tout moment.</p>
    $cookies_policy_field

    <h2>Politique de confidentialité</h2>
    <p>L’équipe de $site_internet est particulièrement attentive au respect des obligations légales de tout éditeur de site internet et suit les recommandations de la commission nationale de l’informatique et des libertés (CNIL).</p>
    <p>D’une façon générale, vous pouvez visiter le site web de $site_internet sans avoir à décliner votre identité et à fournir des informations personnelles vous concernant. Cependant, des informations peuvent vous être demandées. Par exemple, pour pouvoir répondre à votre demande de contact, vous établir un devis, répondre à votre candidature, etc.</p>
    <p>$site_internet respecte la vie privée de l’internaute et se conforme strictement aux lois en vigueur sur la protection de la vie privée et des libertés individuelles. Aucune information personnelle n’est collectée à votre insu. Aucune information personnelle n’est vendue ni cédée à des tiers à des fins commerciales. Les adresses électroniques, numéros de téléphone ou autres informations nominatives dont ce site est destinataire sont conservées pendant une durée de $duree_conservation, puis supprimées. Elles ne font l’objet d’aucune autre exploitation que celle pour laquelle vous avez renseigné ces informations.</p>
    <p>En transmettant vos informations par le biais du formulaire de contact, vous acceptez que vos données soient stockées, de manière confidentielle, et utilisées par $site_internet pour répondre à votre requête.</p>
    <p>Conformément au Règlement Général sur la Protection des Données (RGPD), toute personne peut exercer son droit d’accès, de rectification, d’opposition, à la limitation des traitements, à la suppression et à la portabilité des données la concernant en en faisant la demande :</p>
    <ul>
        <li>Par courrier signé avec photocopie de votre pièce d’identité (ces informations ne seront bien sûr pas conservées ni réutilisées à d’autres fins) à l’adresse du propriétaire indiquée en haut de cette page.</li>
        <li>Par le formulaire de contact accessible <a href='$contact_url'>ici</a>.</li>
    </ul>
    <p>$site_internet s’engage à traiter votre demande et à vous faire une réponse au plus tard dans le mois suivant la demande.</p>
    <p>Si vous estimez, après nous avoir contactés, que vos droits ne sont pas respectés, vous pouvez adresser une réclamation à la CNIL : <a href='https://www.cnil.fr' target='_blank' rel='noopener'>www.cnil.fr</a>.</p>

    <h2>Droits d’auteur</h2>
    <p>Tous droits réservés.</p>
    <p>Les textes, images, illustrations, animations, plans, et autres médias sont soumis à la protection du droit d’auteur. Toute reproduction, modification, transmission du contenu de ce site pour une publication écrite ou électronique est strictement interdite sauf autorisation préalable du responsable de publication.</p>

    <h2>Nous contacter</h2>
    <p>$site_internet vous offre la possibilité de correspondre avec ses services pour toute question, par le biais du formulaire de contact présent <a href='$contact_url'>ici</a>.</p>
    <p>$site_internet s’engage à vous apporter une réponse dans les plus brefs délais.</p>
    ";
}
add_shortcode('mentions_legales', 'ml_generate_legal_mentions');

// Namespace de la librairie de PUC -> plugin pour maj auto
use YahnisElsts\PluginUpdateChecker\v5p4\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/Flibustiers/Mentions-l-gales-Les-Flibustiers/', // URL du dépôt GitHub
    __FILE__, // Chemin vers le fichier principal du plugin
    'Mentions-l-gales-Les-Flibustiers' // Slug du plugin
);

// Définis la branche, ici `master` par défaut.
$updateChecker->setBranch('master');
