<?php
$args = get_template_args();
?>

<section <?= options('strate strate-anchor_nav', $args) ?>>

    <?php
    component('anchor-nav', [
        'items' => [
            ['label' => 'Section 1', 'target' => 'anchor-1'],
            ['label' => 'Section 2', 'target' => '#anchor-2'],
            ['label' => 'Section 3', 'target' => '#anchor-3'],
        ],
        'aria_label' =>  'Navigation des sections',
    ]);
    ?>

    <section id="anchor-1">
        <h2>Section 1</h2>

        <div class="rte">
            <h3>Lorem</h3>
            <p>
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Perferendis similique autem labore quos adipisci mollitia optio, ipsum laudantium sunt, aperiam impedit! Excepturi provident facilis pariatur ipsum non incidunt eum consequatur cumque libero rem nostrum recusandae adipisci mollitia reiciendis dicta vero veniam autem, corporis a. Rem sit magni praesentium. Pariatur voluptate est accusamus eveniet tenetur dolorem nihil! Libero iure, suscipit atque, rem incidunt distinctio nisi a illo amet quasi aliquid, dignissimos eius id eaque explicabo accusantium obcaecati! Dolorem corrupti officiis repudiandae modi consequatur, recusandae nemo illo reprehenderit. Voluptatum distinctio delectus, dolorum soluta est excepturi dolorem ipsum dicta, culpa atque odio fuga.
            </p>
                 <p>
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Perferendis similique autem labore quos adipisci mollitia optio, ipsum laudantium sunt, aperiam impedit! Excepturi provident facilis pariatur ipsum non incidunt eum consequatur cumque libero rem nostrum recusandae adipisci mollitia reiciendis dicta vero veniam autem, corporis a. Rem sit magni praesentium. Pariatur voluptate est accusamus eveniet tenetur dolorem nihil! Libero iure, suscipit atque, rem incidunt distinctio nisi a illo amet quasi aliquid, dignissimos eius id eaque explicabo accusantium obcaecati! Dolorem corrupti officiis repudiandae modi consequatur, recusandae nemo illo reprehenderit. Voluptatum distinctio delectus, dolorum soluta est excepturi dolorem ipsum dicta, culpa atque odio fuga.
            </p>
        </div>
    </section>

    <section id="anchor-2">
        <h2>Section 2</h2>
                <div class="rte">
            <h3>Lorem</h3>
            <p>
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Perferendis similique autem labore quos adipisci mollitia optio, ipsum laudantium sunt, aperiam impedit! Excepturi provident facilis pariatur ipsum non incidunt eum consequatur cumque libero rem nostrum recusandae adipisci mollitia reiciendis dicta vero veniam autem, corporis a. Rem sit magni praesentium. Pariatur voluptate est accusamus eveniet tenetur dolorem nihil! Libero iure, suscipit atque, rem incidunt distinctio nisi a illo amet quasi aliquid, dignissimos eius id eaque explicabo accusantium obcaecati! Dolorem corrupti officiis repudiandae modi consequatur, recusandae nemo illo reprehenderit. Voluptatum distinctio delectus, dolorum soluta est excepturi dolorem ipsum dicta, culpa atque odio fuga.
            </p>
                 <p>
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Perferendis similique autem labore quos adipisci mollitia optio, ipsum laudantium sunt, aperiam impedit! Excepturi provident facilis pariatur ipsum non incidunt eum consequatur cumque libero rem nostrum recusandae adipisci mollitia reiciendis dicta vero veniam autem, corporis a. Rem sit magni praesentium. Pariatur voluptate est accusamus eveniet tenetur dolorem nihil! Libero iure, suscipit atque, rem incidunt distinctio nisi a illo amet quasi aliquid, dignissimos eius id eaque explicabo accusantium obcaecati! Dolorem corrupti officiis repudiandae modi consequatur, recusandae nemo illo reprehenderit. Voluptatum distinctio delectus, dolorum soluta est excepturi dolorem ipsum dicta, culpa atque odio fuga.
            </p>
        </div>
    </section>

    <section id="anchor-3">
        <h2>Section 3</h2>
                <div class="rte">
            <h3>Lorem</h3>
            <p>
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Perferendis similique autem labore quos adipisci mollitia optio, ipsum laudantium sunt, aperiam impedit! Excepturi provident facilis pariatur ipsum non incidunt eum consequatur cumque libero rem nostrum recusandae adipisci mollitia reiciendis dicta vero veniam autem, corporis a. Rem sit magni praesentium. Pariatur voluptate est accusamus eveniet tenetur dolorem nihil! Libero iure, suscipit atque, rem incidunt distinctio nisi a illo amet quasi aliquid, dignissimos eius id eaque explicabo accusantium obcaecati! Dolorem corrupti officiis repudiandae modi consequatur, recusandae nemo illo reprehenderit. Voluptatum distinctio delectus, dolorum soluta est excepturi dolorem ipsum dicta, culpa atque odio fuga.
            </p>
                 <p>
                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Perferendis similique autem labore quos adipisci mollitia optio, ipsum laudantium sunt, aperiam impedit! Excepturi provident facilis pariatur ipsum non incidunt eum consequatur cumque libero rem nostrum recusandae adipisci mollitia reiciendis dicta vero veniam autem, corporis a. Rem sit magni praesentium. Pariatur voluptate est accusamus eveniet tenetur dolorem nihil! Libero iure, suscipit atque, rem incidunt distinctio nisi a illo amet quasi aliquid, dignissimos eius id eaque explicabo accusantium obcaecati! Dolorem corrupti officiis repudiandae modi consequatur, recusandae nemo illo reprehenderit. Voluptatum distinctio delectus, dolorum soluta est excepturi dolorem ipsum dicta, culpa atque odio fuga.
            </p>
        </div>
    </section>
</section>