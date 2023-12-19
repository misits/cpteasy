<?php

/**
 * Post class extending the PostType class.
 */
namespace Cpteasy\includes\models;

use Cpteasy\includes\models\PostType;

class Post extends PostType
{
    /**
     * Post type constant.
     */
    const TYPE = "post";

    /**
     * Retrieve categories associated with the post.
     *
     * @param callable|null $callback - Optional callback function.
     * @return array
     */
    public function categories(callable $callback = null): array
    {
        return $this->terms(Category::class, $callback);
    }

    /**
     * Retrieve tags associated with the post.
     *
     * @param callable|null $callback - Optional callback function.
     * @return array
     */
    public function tags(callable $callback = null): array
    {
        return $this->terms(Tag::class, $callback);
    }

    /**
     * Retrieve comma-separated category names for the post.
     *
     * @return string
     */
    public function categories_name(): string
    {
        return implode(
            ", ",
            $this->categories(function ($category) {
                return $category->title();
            })
        );
    }

    /**
     * Retrieve comma-separated tag names for the post.
     *
     * @return string
     */
    public function tags_name(): string
    {
        return implode(
            ", ",
            $this->tags(function ($tag) {
                return $tag->title();
            })
        );
    }
}
