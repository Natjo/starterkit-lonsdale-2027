<?php
/** Shared editorial HTML sample for Text and Common demonstrations. */
return <<<'HTML'
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. <strong>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</strong> Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>

<h2>Consectetur adipiscing elit</h2>
<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. <em>Excepteur sint occaecat cupidatat non proident</em>, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>

<h3>Sed do eiusmod tempor</h3>
<p>Voici un texte contenant du <strong>gras</strong>, de l’<em>italique</em>, du <strong><em>gras italique</em></strong> ainsi qu’un texte <del>barré</del>. On peut également ajouter un <a href="https://example.com">lien vers un site</a> au milieu d’un paragraphe.</p>
<blockquote><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante venenatis dapibus posuere velit aliquet.</p></blockquote>

<h3>Liste à puces</h3>
<ul>
    <li>Lorem ipsum dolor sit amet</li>
    <li>Consectetur adipiscing elit</li>
    <li>Sed do eiusmod tempor incididunt
        <ul>
            <li>Ut labore et dolore magna aliqua</li>
            <li>Ut enim ad minim veniam</li>
        </ul>
    </li>
    <li>Quis nostrud exercitation ullamco</li>
</ul>

<h3>Liste numérotée</h3>
<ol>
    <li>Lorem ipsum dolor sit amet</li>
    <li>Consectetur adipiscing elit</li>
    <li>Sed do eiusmod tempor incididunt</li>
    <li>Ut labore et dolore magna aliqua</li>
</ol>

<h3>Texte supplémentaire</h3>
<p>Praesent commodo cursus magna, vel scelerisque nisl consectetur et. Donec ullamcorper nulla non metus auctor fringilla. Vestibulum id ligula porta felis euismod semper.</p>
<hr>

<h2>Aliquam erat volutpat</h2>
<p>Curabitur blandit tempus porttitor. Maecenas faucibus mollis interdum. <strong>Nullam quis risus eget urna mollis ornare vel eu leo.</strong> <code>Lorem ipsum dolor sit amet</code> Vestibulum id ligula porta felis euismod semper. Cras mattis consectetur purus sit amet fermentum.</p>

<h3>Tableau</h3>
<table>
    <thead>
        <tr><th>Élément</th><th>Description</th><th>Valeur</th></tr>
    </thead>
    <tbody>
        <tr><td>Lorem</td><td>Ipsum dolor sit amet</td><td>01</td></tr>
        <tr><td>Dolor</td><td>Consectetur adipiscing</td><td>02</td></tr>
        <tr><td>Amet</td><td>Sed do eiusmod tempor</td><td>03</td></tr>
    </tbody>
</table>

<h3>Citation longue</h3>
<blockquote><p>“Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed posuere consectetur est at lobortis.”</p></blockquote>

<h2>Dernier niveau de contenu</h2>
<p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Aenean lacinia bibendum nulla sed consectetur. <strong>Texte important :</strong> lorem ipsum dolor sit amet, consectetur adipiscing elit. <em>Texte secondaire :</em> sed do eiusmod tempor incididunt ut labore et dolore magna aliqua</p>
HTML;
