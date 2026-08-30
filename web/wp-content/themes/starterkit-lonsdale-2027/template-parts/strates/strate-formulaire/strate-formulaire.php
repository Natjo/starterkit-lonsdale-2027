<section class="strate strate-formulaire">
    <div class="strate-content">
        <?php component('header', [
            'title' => 'Formulaire complet',
            'text' => '<p>Démonstration de tous les types de champs disponibles dans le starter.</p>',
            'classes' => 'strate-header',
            'title_classes' => 'title-2',
        ]); ?>

        <form
            id="formulaire-demo"
            class="form strate-formulaire-form"
            method="post"
            data-module="components/field"
            data-context="@visible true">
            <fieldset>
                <legend>Tous les champs du formulaire</legend>

                <div class="strate-formulaire-fields">
                    <?php component('field', [
                        'type' => 'text',
                        'id' => 'formulaire-demo-name',
                        'name' => 'name',
                        'label' => 'Nom complet',
                        'placeholder' => 'Prénom Nom',
                        'autocomplete' => 'name',
                        'minlength' => 2,
                        'required' => true,
                        'mandatory_msg' => 'Saisissez votre nom.',
                    ]); ?>

                    <?php component('field', [
                        'type' => 'email',
                        'id' => 'formulaire-demo-email',
                        'name' => 'email',
                        'label' => 'Adresse e-mail',
                        'placeholder' => 'nom@exemple.fr',
                        'autocomplete' => 'email',
                        'required' => true,
                        'mandatory_msg' => 'Saisissez votre adresse e-mail.',
                        'type_mismatch_msg' => 'Saisissez une adresse e-mail valide.',
                    ]); ?>

                    <?php component('field', [
                        'type' => 'tel',
                        'id' => 'formulaire-demo-phone',
                        'name' => 'phone',
                        'label' => 'Téléphone',
                        'placeholder' => '01 23 45 67 89',
                        'autocomplete' => 'tel',
                    ]); ?>

                    <?php component('field', [
                        'type' => 'url',
                        'id' => 'formulaire-demo-website',
                        'name' => 'website',
                        'label' => 'Site internet',
                        'placeholder' => 'https://exemple.fr',
                        'autocomplete' => 'url',
                    ]); ?>

                    <?php component('field', [
                        'type' => 'password',
                        'id' => 'formulaire-demo-password',
                        'name' => 'password',
                        'label' => 'Mot de passe',
                        'autocomplete' => 'new-password',
                        'hint' => 'Utilisez au moins huit caractères.',
                        'pattern' => '.{8,}',
                        'pattern_mismatch_msg' => 'Utilisez au moins huit caractères.',
                    ]); ?>

                    <?php component('field', [
                        'type' => 'number',
                        'id' => 'formulaire-demo-quantity',
                        'name' => 'quantity',
                        'label' => 'Quantité',
                        'value' => 1,
                        'min' => 1,
                        'max' => 10,
                    ]); ?>

                    <?php component('field', [
                        'type' => 'date',
                        'id' => 'formulaire-demo-date',
                        'name' => 'appointment_date',
                        'label' => 'Date souhaitée',
                    ]); ?>

                    <?php component('field', [
                        'type' => 'select',
                        'id' => 'formulaire-demo-subject',
                        'name' => 'subject',
                        'label' => 'Sujet de la demande',
                        'required' => true,
                        'mandatory_msg' => 'Choisissez un sujet.',
                        'options' => [
                            ['value' => '', 'label' => 'Sélectionner', 'selected' => true, 'disabled' => true],
                            ['value' => 'project', 'label' => 'Nouveau projet'],
                            ['value' => 'support', 'label' => 'Assistance'],
                            ['value' => 'other', 'label' => 'Autre demande'],
                        ],
                    ]); ?>

                    <?php component('field', [
                        'type' => 'textarea',
                        'id' => 'formulaire-demo-message',
                        'name' => 'message',
                        'label' => 'Message',
                        'placeholder' => 'Décrivez votre demande',
                        'rows' => 6,
                        'classes' => 'field-wide',
                        'required' => true,
                        'mandatory_msg' => 'Saisissez votre message.',
                    ]); ?>

                    <?php component('field', [
                        'type' => 'radios',
                        'id' => 'formulaire-demo-contact',
                        'name' => 'contact_method',
                        'label' => 'Moyen de contact préféré',
                        'classes' => 'field-wide',
                        'required' => true,
                        'mandatory_msg' => 'Choisissez un moyen de contact.',
                        'options' => [
                            ['value' => 'email', 'label' => 'E-mail', 'checked' => true],
                            ['value' => 'phone', 'label' => 'Téléphone'],
                        ],
                    ]); ?>

                    <?php component('field', [
                        'type' => 'checkboxes',
                        'id' => 'formulaire-demo-interests',
                        'name' => 'interests',
                        'label' => 'Sujets qui vous intéressent',
                        'classes' => 'field-wide',
                        'options' => [
                            ['value' => 'strategy', 'label' => 'Stratégie'],
                            ['value' => 'design', 'label' => 'Design'],
                            ['value' => 'digital', 'label' => 'Digital'],
                        ],
                    ]); ?>

                    <?php component('field', [
                        'type' => 'checkbox',
                        'id' => 'formulaire-demo-consent',
                        'name' => 'consent',
                        'label' => 'J’accepte que mes informations soient utilisées pour traiter ma demande.',
                        'classes' => 'field-wide',
                        'required' => true,
                        'mandatory_msg' => 'Votre accord est nécessaire.',
                    ]); ?>
                </div>
            </fieldset>

            <div class="action">
                <?php component('btn', [
                    'name' => 'Envoyer',
                    'classes' => 'btn-1',
                    'attributes' => ['type' => 'submit'],
                ]); ?>
                <?php component('btn', [
                    'name' => 'Réinitialiser',
                    'attributes' => ['type' => 'reset'],
                ]); ?>
            </div>
        </form>
    </div>
</section>
