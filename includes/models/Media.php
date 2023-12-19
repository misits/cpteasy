<?php

namespace Cpteasy\includes\models;

use Cpteasy\includes\utils\Size;
use Cpteasy\includes\models\PostType;

class Media extends PostType
{
    /**
     * Post type constant.
     */
    const TYPE = "attachment";

    /**
     * Get the media url for a size.
     * Return The original media's url if size didn't exist.
     * @param string $size The size ID
     *
     * @return string
     */
    public function src($size = "thumbnail"): string
    {
        $data = Size::src($this->id(), $size);

        if ($size !== "full" and !$data) {
            return $this->src("full");
        }

        if (!$data) {
            return false;
        }

        if (isset($data[0])) {
            return $data[0];
        }

        return $data["src"];
    }

    /**
     * Get the media alt description.
     *
     * @return string
     */
    public function alt(): string
    {
        return get_post_meta($this->id(), "_wp_attachment_image_alt", true);
    }

    /**
     * Get the media caption.
     *
     * @return string
     */
    public function caption(): string
    {
        return wp_get_attachment_caption($this->id());
    }
}
