<?php

/**
 * Traite le formulaire de contact et expose son état au template.
 *
 * La classe centralise la normalisation des champs, la validation du nonce et
 * des données obligatoires, l'envoi avec wp_mail(), ainsi que la réponse POST
 * classique ou REST. Elle ne produit pas le formulaire HTML : le template
 * utilise les accesseurs publics pour afficher les valeurs et les messages.
 *
 * Le mode AJAX est une amélioration progressive. Lorsqu'il est désactivé ou
 * indisponible, handle() traite la soumission POST puis applique le motif
 * Post/Redirect/Get. Lorsqu'il est activé, endpoint_url() fournit la route REST
 * consommée par le module `pages/template-contact`.
 *
 * Le nonce limite les soumissions intersites accidentelles, mais ne constitue
 * pas une protection antispam. Un CAPTCHA, un honeypot ou une limitation de
 * débit doivent être ajoutés séparément lorsqu'ils sont nécessaires.
 */
final class Theme_Contact_Form
{
    private const REST_NAMESPACE = 'starter/v1';
    private const REST_ROUTE = '/contact';

    /** @var array{name: string, email: string, phone: string, subject: string, message: string} */
    private array $values = [
        'name' => '',
        'email' => '',
        'phone' => '',
        'subject' => '',
        'message' => '',
    ];

    /** @var array{success: string, expired: string, invalid: string, failed: string} */
    private array $messages;
    private string $action_url;
    private string $recipient;
    private string $error = '';
    private bool $sent;
    private bool $ajax;
    private bool $handled = false;

    /**
     * Initialise la configuration et détecte un éventuel retour de succès.
     *
     * Le constructeur n'envoie aucun message. Appeler handle() dans un template
     * utilisant le POST classique. La route REST appelle directement le même
     * traitement interne.
     *
     * @param array{
     *     action_url?: string,
     *     recipient?: string,
     *     ajax?: bool,
     *     messages?: array{
     *         success?: string,
     *         expired?: string,
     *         invalid?: string,
     *         failed?: string
     *     }
     * } $config `action_url` utilise le permalien courant par défaut,
     *   `recipient` l'adresse administrateur du site et `ajax` vaut `false`.
     */
    public function __construct(array $config = [])
    {
        $action_url = $config['action_url'] ?? '';
        $recipient = $config['recipient'] ?? '';
        $messages = $config['messages'] ?? [];

        $this->action_url = is_string($action_url) && $action_url !== ''
            ? $action_url
            : get_permalink();
        $this->recipient = is_string($recipient) && $recipient !== ''
            ? $recipient
            : (string) get_option('admin_email');
        $this->ajax = ($config['ajax'] ?? false) === true;
        $this->messages = [
            'success' => 'Votre message a bien été envoyé.',
            'expired' => 'La session a expiré. Rechargez la page puis réessayez.',
            'invalid' => 'Complétez tous les champs obligatoires avec des informations valides.',
            'failed' => 'Le message n’a pas pu être envoyé. Réessayez dans quelques instants.',
        ];
        if (is_array($messages)) {
            foreach ($this->messages as $key => $default_message) {
                if (isset($messages[$key]) && is_string($messages[$key]) && $messages[$key] !== '') {
                    $this->messages[$key] = $messages[$key];
                }
            }
        }

        $status = $_GET['contact'] ?? '';
        $this->sent = is_scalar($status)
            && sanitize_key(wp_unslash((string) $status)) === 'sent';
    }

    /**
     * Traite une soumission POST classique une seule fois par instance.
     *
     * Seules les requêtes POST contenant `contact_action=send` sont prises en
     * compte. Après un envoi réussi, la méthode redirige vers action_url avec
     * `?contact=sent` puis interrompt l'exécution. En cas d'échec, error()
     * expose le message à rendre dans le template.
     */
    public function handle(): void
    {
        if ($this->handled) {
            return;
        }
        $this->handled = true;

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return;
        }

        $action = $_POST['contact_action'] ?? '';
        if (!is_scalar($action) || sanitize_key(wp_unslash((string) $action)) !== 'send') {
            return;
        }

        if (!$this->submit($_POST)) {
            return;
        }

        if (wp_safe_redirect(add_query_arg('contact', 'sent', $this->action_url))) {
            exit;
        }

        $this->error = $this->messages['failed'];
    }

    /** Retourne l'URL d'action du formulaire classique. */
    public function action_url(): string
    {
        return $this->action_url;
    }

    /**
     * Retourne la valeur normalisée d'un champ après une soumission invalide.
     *
     * Les noms acceptés sont `name`, `email`, `phone`, `subject` et `message`.
     * Un nom inconnu retourne une chaîne vide.
     */
    public function value(string $name): string
    {
        return $this->values[$name] ?? '';
    }

    /** Retourne le dernier message d'erreur, ou une chaîne vide. */
    public function error(): string
    {
        return $this->error;
    }

    /** Retourne le message à afficher après un envoi réussi. */
    public function success_message(): string
    {
        return $this->messages['success'];
    }

    /**
     * Indique si l'URL courante contient le statut `contact=sent`.
     */
    public function is_sent(): bool
    {
        return $this->sent;
    }

    /** Indique si l'amélioration progressive AJAX est activée. */
    public function is_ajax(): bool
    {
        return $this->ajax;
    }

    /**
     * Retourne l'URL de la route REST lorsque AJAX est activé.
     *
     * @return string URL absolue de `starter/v1/contact`, ou chaîne vide.
     */
    public function endpoint_url(): string
    {
        return $this->ajax ? rest_url(self::REST_NAMESPACE . self::REST_ROUTE) : '';
    }

    /** Affiche le champ nonce attendu par les traitements POST et REST. */
    public function nonce_field(): void
    {
        wp_nonce_field('theme_contact', 'contact_nonce');
    }

    /** Enregistre la route publique POST `starter/v1/contact`. */
    public static function register_rest_route(): void
    {
        register_rest_route(self::REST_NAMESPACE, self::REST_ROUTE, [
            'methods' => 'POST',
            'callback' => [self::class, 'rest_submit'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Traite une soumission REST et retourne une réponse JSON normalisée.
     *
     * La route est publiquement accessible pour les visiteurs, mais submit()
     * refuse toute requête dont le nonce `contact_nonce` est absent ou invalide.
     *
     * @return WP_REST_Response Réponse 200 en cas de succès, 422 sinon.
     */
    public static function rest_submit(WP_REST_Request $request): WP_REST_Response
    {
        $form = new self();
        $success = $form->submit($request->get_params());

        return new WP_REST_Response([
            'success' => $success,
            'message' => $success ? $form->success_message() : $form->error(),
        ], $success ? 200 : 422);
    }

    /**
     * Normalise, valide et envoie une soumission issue du POST ou de REST.
     *
     * Les champs `name`, `email`, `message` et `consent=1` sont obligatoires.
     * `phone` et `subject` sont facultatifs. Les valeurs réaffichables restent
     * accessibles avec value() lorsque la validation échoue.
     *
     * @param array<string, mixed> $input Données brutes de la soumission.
     * @return bool `true` uniquement lorsque wp_mail() confirme l'envoi.
     */
    private function submit(array $input): bool
    {
        $nonce = $input['contact_nonce'] ?? '';
        $nonce = is_scalar($nonce) ? sanitize_text_field(wp_unslash((string) $nonce)) : '';

        foreach (array_keys($this->values) as $field_name) {
            $value = $input[$field_name] ?? '';
            $this->values[$field_name] = is_scalar($value)
                ? sanitize_text_field(wp_unslash((string) $value))
                : '';
        }

        $message = $input['message'] ?? '';
        $this->values['message'] = is_scalar($message)
            ? sanitize_textarea_field(wp_unslash((string) $message))
            : '';

        $consent = $input['consent'] ?? '';
        $has_consent = is_scalar($consent) && wp_unslash((string) $consent) === '1';

        if (!wp_verify_nonce($nonce, 'theme_contact')) {
            $this->error = $this->messages['expired'];
            return false;
        }

        if (
            $this->values['name'] === ''
            || !is_email($this->values['email'])
            || $this->values['message'] === ''
            || !$has_consent
        ) {
            $this->error = $this->messages['invalid'];
            return false;
        }

        $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $subject = $this->values['subject'] !== ''
            ? $this->values['subject']
            : sprintf('Nouveau message depuis %s', $site_name);
        $body = implode("\n\n", array_filter([
            'Nom : ' . $this->values['name'],
            'E-mail : ' . $this->values['email'],
            $this->values['phone'] !== '' ? 'Téléphone : ' . $this->values['phone'] : '',
            "Message :\n" . $this->values['message'],
        ]));
        $recipient = sanitize_email($this->recipient);

        if (
            $recipient === ''
            || !wp_mail(
                $recipient,
                $subject,
                $body,
                ['Reply-To: ' . $this->values['name'] . ' <' . $this->values['email'] . '>']
            )
        ) {
            $this->error = $this->messages['failed'];
            return false;
        }

        return true;
    }
}

add_action('rest_api_init', [Theme_Contact_Form::class, 'register_rest_route']);
