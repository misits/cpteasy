<?php

/**
 * File class extending the PostType class.
 */
namespace Cpteasy\includes\models;

use Cpteasy\includes\models\PostType;

class File extends PostType
{
    /**
     * Post type constant.
     */
    const TYPE = "attachment";

    /**
     * Get the URL of the file.
     *
     * @return string
     */
    public function url(): string
    {
        return wp_get_attachment_url($this->id());
    }
}
