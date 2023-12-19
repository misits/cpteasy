<?php

/**
 * Abstract CustomPostType class extending the PostType class.
 */
namespace Cpteasy\includes\models;

use Cpteasy\includes\models\PostType;

abstract class CustomPostType extends PostType
{
    /**
     * Abstract method to define type_settings for the custom post type.
     *
     * @return array
     */
    abstract public static function type_settings();

    /**
     * Register the custom post type.
     */
    public static function register()
    {
        register_post_type(static::TYPE, static::type_settings());
    }
}
