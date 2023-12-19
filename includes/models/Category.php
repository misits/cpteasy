<?php

/**
 * Category class extending the Taxonomy class.
 */
namespace Cpteasy\includes\models;

use Cpteasy\includes\models\Taxonomy;

class Category extends Taxonomy
{
    /**
     * The taxonomy type for categories.
     */
    const TYPE = "category";

    /**
     * Constant representing the "None" category.
     */
    const NONE = 1;
}
