<?php
    global $wp_query;
    $query = isset($args['query']) ? $args['query'] : $wp_query;

    if ($query->max_num_pages <= 1) return;

    $offset = 2;
    $current = (isset($paged) && $paged) ? $paged : 1;
    $max_num_pages = (isset($query->max_num_pages) && $query->max_num_pages) ? $query->max_num_pages : 1;

    if (!isset($args['url']) || empty($args['url'])) {
        $args['url'] = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    $params = explode('?', $args['url']);
    $params = isset($params[1]) ? '?' . $params[1] : '';

    $url = explode('/page/', $args['url'])[0];
    $url = explode('?', $url)[0];

    if(substr($url, -1) == '/') {
        $url = substr($url, 0, -1);
    }
?>

<nav role="navigation" class="container ezfilters-paging">
    <ul>
        <li>
            <a
                class="js-ezfilters-paging-link"
                <?php if ($current == 1): ?>
                    aria-disabled="true"
                <?php else: ?>
                    data-index="<?= $current - 1 ?>"
                    href="<?= $url ?>/page/<?= $current - 1 ?><?= $params ?>"
                <?php endif ?>
                role="link"
            >
                Précédent
            </a>
        </li>

        <?php if ($current < 4):
            for ($i = 1; $i <= $max_num_pages; $i++):
                if ($i <= 4):
        ?>
                    <li>
                        <a
                            class="js-ezfilters-paging-link"
                            data-index="<?= $i ?>"
                            <?php if ($current == $i) : ?> aria-current="page"<?php else : ?>href="<?= $url ?>/page/<?= $i ?><?= $params ?>"<?php endif; ?> title="Page <?= $i; ?>"
                        >
                            <?= $i; ?>
                        </a>
                    </li>

                    <?php if (($i == 4 || $i == $max_num_pages) && $i < $max_num_pages) : ?>
                        <?php if ($i < $max_num_pages - 1) : ?>
                            <li>
                                <a>
                                    …
                                </a>
                            </li>
                        <?php endif ?>

                    <li>
                        <a
                            class="js-ezfilters-paging-link"
                            data-index="<?= $max_num_pages ?>"
                            href="<?= $url ?>/page/<?= $max_num_pages ?><?= $params ?>"
                            title="Page <?= $max_num_pages; ?>"
                        >
                            <?= $max_num_pages; ?>
                        </a>
                    </li>

        <?php
              endif;
            endif;
          endfor;
        else:
          // si + de 6
            for ($i = 1; $i <= $max_num_pages; $i++):
                if (($i > ($current - $offset) && $i < ($current + $offset)) || $i == 1 || $i == $max_num_pages) :

                    if ($i == $max_num_pages && $current < $i - $offset): ?>
                        <li>    
                            <a>
                                …
                            </a>
                        </li>
                    <?php endif; ?>
                        <li>
                            <a
                                class="js-ezfilters-paging-link"
                                data-index="<?= $i ?>"
                                <?php if ($current == $i): ?>aria-current="page"<?php else : ?>href="<?= $url ?>/page/<?= $i ?><?= $params ?>"<?php endif; ?>
                                title="Page <?= $i; ?>"
                            >
                                <?= $i; ?>
                            </a>
                        </li>

                    <?php if ($i == 1 && $current > $i + $offset) : ?>
                        <li>
                            <a>
                                …
                            </a>
                        </li>
                    <?php endif; ?>

        <?php
                endif;
              endfor;
            endif;
        ?>

        <li>
            <a
                class="js-ezfilters-paging-link"
                <?php if ($current == $max_num_pages): ?>
                    aria-disabled="true"
                <?php else: ?>
                    class="js-ezfilters-paging-link"
                    data-index="<?= $current + 1 ?>"
                    href="<?= $url ?>/page/<?= $current + 1 ?><?= $params ?>"
                <?php endif ?>
            >
                Suivant
            </a>
        </li>
    </ul>
</nav>

<style>
    .ezfilters-paging {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 4rem;

        ul {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;

            li {
                a {
                    &[aria-current="page"] {
                        text-decoration: underline;
                    }

                    &:not([href]) {
                        pointer-events: none;
                    }
                }
            }
        }
    }
</style>