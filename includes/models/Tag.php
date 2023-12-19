<?php

/**
 * Class representing a Tag model, extending the Taxonomy class.
 */
namespace Cpteasy\includes\models;

use Cpteasy\includes\models\Taxonomy;

class Tag extends Taxonomy
{
    /**
     * The taxonomy type for tags.
     */
    const TYPE = "post_tag";
}
