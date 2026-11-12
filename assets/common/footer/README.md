# Footer configurable

`common('footer')` affiche le pied de page sans argument. Le starter utilise
Simple avec copyright par défaut. Forge propose deux types en Build et affiche
leurs réglages en lecture seule en Manage.

| Option | Simple | Columns |
| --- | --- | --- |
| Liens sociaux | oui | oui |
| Menu footer secondaire | oui | oui |
| Copyright | oui | oui |
| Texte | non | oui |
| Newsletter | non | oui |
| Recherche | non | oui |

Newsletter et Recherche occupent une zone à droite des menus de Columns. Si les
deux sont actives, elles sont empilées. Sur mobile, la zone passe sous les menus.

## Menus WordPress

- `menu-footer` : menu principal, premier niveau pour Simple.
- Columns : trois entrées de premier niveau maximum, rendues comme titres non
  cliquables. Leurs enfants directs sont les liens. Les colonnes suivantes et
  niveaux plus profonds ne sont pas affichés. Contribuer au maximum trois groupes.
  Dans Apparence → Menus, ajouter un lien personnalisé avec l’URL `#` pour
  chaque titre, puis glisser les pages dessous en les décalant.
- `menu-footer-secondary` : liens légaux et complémentaires, sur un niveau.
- `menu-footer-socials` : liens personnalisés vers les profils sociaux. Donner
  une classe CSS `icon-linkedin`, `icon-instagram`, `icon-facebook`, etc. à chaque
  entrée (Options de l’écran → Classes CSS). Le composant `socials` valide les
  réseaux et URL, sans lire le sprite au runtime.

Le texte reprend le slogan de Réglages → Général. Le copyright utilise l’année
courante et le titre du site. Les titres de colonnes sont des titres `h2`.
Les liens conservent leurs cibles et les classes actives utiles ; les IDs et
classes techniques WordPress ne sont pas rendus.

## Assemblage Forge

`footerConfiguration.version: 1` déclare les capacités du catalogue. Les fragments
PHP résident dans `footer-parts/` et les styles dans `assets/common/footer/recipe/`.
Un site généré reçoit un seul footer PHP/SCSS avec le type et les options retenus,
ainsi que leurs dépendances de menus et composants. Les fragments d’aperçu sont
retirés. Il n’y a pas de JavaScript propre au footer.

La recherche et la newsletter ne sont pas proposées pour Simple.

## Newsletter et recherche dans Columns

La newsletter utilise le composant `newsletter` : titre, texte, champ e-mail et
bouton. Les libellés sont fournis par le footer ; l’envoi doit être relié au
service retenu pour le projet. Aucun succès d’inscription n’est simulé.
Le tableau WordPress `get_option('theme_footer_newsletter', [])` et le filtre
`theme_footer_newsletter` acceptent `title`, `text` (HTML éditorial), `action`
(URL HTTP(S) du service) et `button_label`. Exemple dans le thème :

```php
add_filter('theme_footer_newsletter', static fn (array $args): array => array_replace($args, [
    'action' => 'https://newsletter.example.com/subscribe',
]));
```

La recherche utilise le formulaire natif WordPress. L’activer dans Forge active
aussi la recherche du site. Les deux formulaires gardent leurs labels accessibles,
et le design system assure l’unicité des IDs lors des remplacements partiels.
Les composants ne sont pas modifiés.
