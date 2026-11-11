    <section class="footer-search-widget" aria-labelledby="footer-search-title">
        <?php component('title', ['title' => 'Recherche', 'hx' => 2, 'attributes' => ['id' => 'footer-search-title'], 'classes' => 'footer-widget-title']); ?>
        <?php component('search', ['label' => 'Rechercher sur le site', 'placeholder' => 'Votre recherche', 'submit_label' => 'Rechercher', 'classes' => 'footer-search']); ?>
    </section>
