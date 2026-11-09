# Content Types

Chaque dossier décrit un Content Type proposé par le starter à Forge. Le
manifeste `{key}.json` sert uniquement au catalogue de création ; la
déclaration WordPress standard correspondante reste dans `inc/datatypes.php`.

Lors d'une création, Forge réécrit ce fichier PHP et ne conserve que les
Content Types choisis.

Dans le catalogue initial, les Content Types ne sont pas exposés dans l’API
REST (`showInRest: false`) et seuls les supports explicitement nécessaires sont
activés. `thumbnail` et `excerpt` sont désactivés par défaut.

La clé `card` contient le slug de la card associée au Content Type. Forge
active automatiquement cette card lorsque le Content Type est ajouté. Une
chaîne vide indique qu’aucune card dédiée n’est imposée.

Un Content Type avec `isPublic: false` et `hasArchive: false` reste disponible
dans l’administration et peut être chargé explicitement pour être affiché dans
une card ou une strate, sans exposer de page individuelle ni d’archive.

## Champs métier des cards

Dans `inc/datatypes.php`, la définition d’un CPT peut déclarer `card_fields`,
un mapping des clés `title`, `text`, `date`, `image`, `images` vers des chemins
ACF. Les chemins imbriqués utilisent un point, par exemple `card.function`.
Les clés canoniques du groupe `card` sont prises en charge directement :
News n’a pas besoin de mapping ; Team utilise `['text' => 'card.function']`.
