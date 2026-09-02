# Common `prefooter`

Affiche le pré-footer global en deux colonnes :

- titre, texte et menu de réseaux sociaux ;
- titre, texte et formulaire d’inscription à la newsletter.

## Appel

```php
common('prefooter');
```

Le common est appelé dans `footer.php`, juste avant `common('footer')`. Il ne
reçoit aucun payload.

## Données WordPress

Le contenu vient du groupe ACF `Common - Prefooter`, affiché dans
`Options > Prefooter` :

| Champ | Rôle |
| --- | --- |
| `title` | Titre de la première colonne |
| `text` | Texte éditorial |
| `socials` | Répéteur des réseaux et de leurs URL |
| `newsletter_title` | Titre de la newsletter |
| `newsletter_text` | Texte de présentation |
| `newsletter_action` | URL HTTP(S) recevant le formulaire |
| `newsletter_button` | Libellé du bouton |

Chaque ligne de `socials` associe un réseau prédéfini à son URL. Le common
transmet le répéteur à `component('socials', [...])`, qui affiche l’icône
correspondante lorsqu’elle existe et le nom du réseau dans le cas contraire.

Le prefooter ne produit rien lorsque le groupe ACF ne contient aucun contenu.

## Newsletter

Le formulaire envoie le champ `email` en `POST` vers `newsletter_action`.
L’intégration du service de newsletter reste propre au projet. Sans URL, le
formulaire conserve son balisage pour le développement mais poste sur la page
courante.

## Dépendances

- `component('newsletter', [...])` ;
- `component('socials', [...])` ;
- `assets/common/prefooter/prefooter.scss`, intégré à `app.css` car le prefooter
  est appelé sur toutes les pages.
