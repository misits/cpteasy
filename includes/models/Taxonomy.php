<?php

/**
 * Abstract class representing a Taxonomy model.
 * Implements JsonSerializable for JSON serialization.
 */
namespace Cpteasy\includes\models;

abstract class Taxonomy implements \JsonSerializable
{
    /**
     * The default taxonomy type.
     */
    const TYPE = "category";

    /**
     * @var object The term object.
     */
    protected $term;

    /**
     * Constructor for the Taxonomy class.
     *
     * @param object $term The term object.
     */
    public function __construct($term)
    {
        $this->term = $term;
    }

    /**
     * Retrieve all terms of the specified taxonomy.
     *
     * @param callable|null $callback Callback function for each term.
     *
     * @return array An array of Taxonomy objects.
     */
    public static function all(callable $callback = null): array
    {
        $terms = get_categories(["taxonomy" => static::TYPE]);

        return array_map(function ($term) use ($callback) {
            return $callback ? $callback(new static($term)) : new static($term);
        }, array_values($terms));
    }

    /**
     * Retrieve all terms of the specified taxonomy and type.
     *
     * @param string|null $type     The taxonomy type.
     * @param callable|null $callback Callback function for each term.
     *
     * @return array An array of Taxonomy objects.
     */
    public static function all_by_type($type = null, callable $callback = null): array
    {
        $terms = get_categories(["taxonomy" => $type, 'hide_empty' => false]);

        return array_map(function ($term) use ($callback) {
            return $callback
                ? $callback(new static($term))
                : new static($term);
        }, array_values($terms));
    }

    /**
     * Retrieve the current term of the specified taxonomy.
     *
     * @param callable|null $callback Callback function for the term.
     *
     * @return Taxonomy|null A Taxonomy object or null if not applicable.
     */
    public static function current(callable $callback = null)
    {
        wp_reset_query();
        $term = get_queried_object();

        if (!$term or !isset($term->taxonomy)) {
            return;
        }

        if ($term->taxonomy !== static::TYPE) {
            return;
        }

        $model = new static($term);
        return $callback ? $callback($model) : $model;
    }

    /**
     * Get the ID of the term.
     *
     * @return int The term ID.
     */
    public function id(): int
    {
        return $this->term->term_taxonomy_id;
    }

    /**
     * Get the slug of the term.
     *
     * @return string The term slug.
     */
    public function slug(): string
    {
        return $this->term->slug;
    }

    /**
     * Get the title of the term.
     *
     * @return string The term title.
     */
    public function title(): string
    {
        return ucfirst($this->term->name);
    }

    /**
     * Get the link to the term archive.
     *
     * @return string The term archive link.
     */
    public function link(): string
    {
        return esc_url(get_term_link($this->term, static::TYPE));
    }

    /**
     * Find a term by a specified field and value.
     *
     * @param string $field The field to search.
     * @param mixed  $value The value to match.
     *
     * @return Taxonomy|null A Taxonomy object or null if not found.
     */
    private static function find($field, $value)
    {
        $term = get_term_by($field, $value, static::TYPE);
        if ($term) {
            return new static($term);
        }
    }

    /**
     * Find a term by ID.
     *
     * @param int $value The ID to search.
     *
     * @return Taxonomy|null A Taxonomy object or null if not found.
     */
    public static function find_by_id($value)
    {
        return self::find("id", $value);
    }

    /**
     * Find a term by slug.
     *
     * @param string $value The slug to search.
     *
     * @return Taxonomy|null A Taxonomy object or null if not found.
     */
    public static function find_by_slug($value)
    {
        return self::find("slug", $value);
    }

    /**
     * JsonSerialize method for JSON serialization.
     *
     * @return mixed The serialized data.
     */
    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->id(),
            "title" => $this->title(),
            "slug" => $this->slug(),
        ];
    }
}
