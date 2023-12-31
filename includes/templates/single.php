<?php

namespace Cpteasy\includes\templates;

use Cpteasy\includes\models\Media;
use Cpteasy\includes\models\Post;

$model = Post::new(get_post_type(), get_the_ID());

/* Edit the bellow template as you wish */

get_header();
?>

<section>
    <h1><?= $model->title() ?></h1>
    <div><?= $model->content() ?></div>
    <?php $model->thumbnail(function (Media $media) { ?>
        <figure>
            <picture>
                <source media="(min-width: 1281px)" srcset="<?= $media->src('image-xl') ?> 1x, <?= $media->src('image-xl-2x') ?> 2x">
                <source media="(max-width: 1280px)" srcset="<?= $media->src('image-l') ?> 1x, <?= $media->src('image-l-2x') ?> 2x">
                <source media="(max-width: 860px)" srcset="<?= $media->src('image-m') ?> 1x, <?= $media->src('image-m-2x') ?> 2x">
                <source media="(max-width: 400px)" srcset="<?= $media->src('image-s') ?> 1x, <?= $media->src('image-s-2x') ?> 2x">
                <img srcset="<?= $media->src('image-l') ?> 1280w, <?= $media->src('image-xl') ?> 1920w" src="<?= $media->src('image-xl') ?>" alt="<?= $media->alt() ?>">
            </picture>
        </figure>
    <?php }); ?>
</section>

<?php get_footer(); ?>
