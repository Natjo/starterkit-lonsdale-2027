# Content Types

Chaque dossier décrit un Content Type proposé par le starter à Forge. Le
manifeste `{key}.json` sert uniquement au catalogue de création ; la
déclaration WordPress standard correspondante reste dans `inc/datatypes.php`.

Lors d'une création, Forge réécrit ce fichier PHP et ne conserve que les
Content Types choisis.

La clé `card` contient le slug de la card associée au Content Type. Forge
active automatiquement cette card lorsque le Content Type est ajouté. Une
chaîne vide indique qu’aucune card dédiée n’est imposée.
