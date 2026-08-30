# Composant `newsletter`

Affiche une introduction facultative et un formulaire d’inscription composé
d’un champ e-mail et d’un bouton.

## Utilisation

```php
component('newsletter', [
    'title' => 'Newsletter',
    'text' => '<p>Recevez nos dernières actualités.</p>',
    'action' => 'https://newsletter.example.com/subscribe',
    'email_label' => 'Adresse e-mail',
    'button_label' => 'S’inscrire',
    'mandatory_msg' => 'Saisissez votre adresse e-mail.',
    'type_mismatch_msg' => 'Saisissez une adresse e-mail valide.',
]);
```

## Payload

| Clé | Type | Défaut | Rôle |
| --- | --- | --- | --- |
| `title` | `scalar\|Stringable` | `''` | Titre facultatif |
| `text` | `scalar\|Stringable` | `''` | Texte éditorial facultatif |
| `action` | `scalar\|Stringable` | `''` | URL HTTP(S) recevant le formulaire |
| `email_id` | `scalar\|Stringable` | identifiant unique | Identifiant du champ |
| `email_name` | `scalar\|Stringable` | `'email'` | Nom envoyé au service |
| `email_label` | `scalar\|Stringable` | `''` | Libellé obligatoire du champ |
| `email_placeholder` | `scalar\|Stringable` | `''` | Placeholder facultatif |
| `button_label` | `scalar\|Stringable` | `''` | Libellé obligatoire du bouton |
| `mandatory_msg` | `scalar\|Stringable` | `''` | Message lorsque le champ est vide |
| `type_mismatch_msg` | `scalar\|Stringable` | `''` | Message pour un e-mail invalide |
| `title_classes` | `string\|array` | `'title-3'` | Classes du titre |
| `button_classes` | `string\|array` | `''` | Classes du bouton |
| `classes` | `string\|array` | `''` | Classes du conteneur |
| `attributes` | `array` | `[]` | Attributs libres du conteneur |

Le composant ne produit rien sans `email_label`, sans `button_label` ou si
`email_name` est invalide. Une action absente ou invalide n’est pas rendue :
le formulaire poste alors sur la page courante.

Le composant ne traite pas l’inscription côté serveur. L’appelant fournit
l’URL de Mailchimp, Brevo ou du service choisi par le projet.

## Dépendances

- `component('title', [...])` ;
- `component('text', [...])` ;
- `component('field', [...])` et son module de validation ;
- `component('btn', [...])` ;
- `assets/components/newsletter/newsletter.scss`.
