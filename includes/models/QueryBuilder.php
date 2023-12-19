<?php

/**
 * QueryBuilder class for building and executing WordPress queries.
 */
namespace Cpteasy\includes\models;

use \WP_Query;

class QueryBuilder
{
    /**
     * Constants for query orders.
     */
    const ORDER_ASC = "ASC";
    const ORDER_DESC = "DESC";

    /**
     * Constants for query relations and operators.
     */
    const AND = "and";
    const OR = "or";
    const IN = "in";
    const NOT_IN = "not in";

    /**
     * The post type for the query.
     * @var string
     */
    private $_postType;

    /**
     * The WP_Query instance.
     * @var \WP_Query|null
     */
    private $_query;

    /**
     * The query parameters.
     * @var array
     */
    private $_queryParams = ["posts_per_page" => -1];

    /**
     * The fetched query parameters.
     * @var array|null
     */
    private $_fetchedParams = null;

    /**
     * Constructor for QueryBuilder.
     *
     * @param string $postType - The post type for the query.
     */
    public function __construct(string $postType)
    {
        $this->_postType = $postType;
        $this->_queryParams["post_type"] = $postType::TYPE;
        $this->_queryParams["ignore_sticky_posts"] = 1;
    }

    /**
     * Static method to create a new QueryBuilder instance.
     *
     * @param string $postType - The post type for the query.
     * @return QueryBuilder
     */
    public static function from(string $postType): self
    {
        return new self($postType);
    }

    /**
     * Set the number of items to display per page.
     *
     * @param int $limit - Number of items to display per page.
     * @param int $page - Optional. Page number. Default is 1.
     * @return QueryBuilder
     */
    public function paginate(int $limit, int $page = 1): self
    {
        $this->_queryParams["posts_per_page"] = $limit;
        $this->_queryParams["paged"] = $page;
        return $this;
    }

    /**
     * Add a basic key-value pair to the query parameters.
     *
     * @param string $key - The key to add.
     * @param mixed $value - The value to assign to the key.
     * @return QueryBuilder
     */
    public function where(string $key, $value): self
    {
        $this->_queryParams[$key] = $value;
        return $this;
    }

    /**
     * Add a search query to the parameters.
     *
     * @param mixed $value - The value to search for.
     * @return QueryBuilder
     */
    public function search($value): self
    {
        return $this->where("s", $value);
    }

    /**
     * Add a condition to include or exclude posts with specific IDs.
     *
     * @param array $ids - The array of post IDs.
     * @param string $operator - Optional. The operator for the query (IN or NOT IN). Default is IN.
     * @return QueryBuilder
     */
    public function where_ids(array $ids, string $operator = self::IN): self
    {
        if ($operator === self::IN) {
            return $this->where("post__in", $ids);
        }

        return $this->where("post__not_in", $ids);
    }

    /**
     * Set the order of the query results.
     *
     * @param string $field - The field to order by.
     * @param string $order - Optional. The order direction (ASC or DESC). Default is ASC.
     * @return QueryBuilder
     */
    public function order(string $field, string $order = self::ORDER_ASC): self
    {
        $this->_queryParams["orderby"] = $field;
        $this->_queryParams["order"] = $order;
        return $this;
    }

    /**
     * Set the order of the query results based on meta values.
     *
     * @param string $field - The meta field to order by.
     * @param string $order - Optional. The order direction (ASC or DESC). Default is ASC.
     * @return QueryBuilder
     */
    public function meta_order(string $field, string $order = self::ORDER_ASC): self
    {
        $this->order("meta_value", $order);
        $this->_queryParams["meta_key"] = $field;
        return $this;
    }

    /**
     * Set the relation for meta queries.
     *
     * @param string $operator - The relation operator (AND or OR).
     * @return QueryBuilder
     */
    public function meta_query_relation(string $operator): self
    {
        if (!isset($this->_queryParams["meta_query"])) {
            $this->_queryParams["meta_query"] = [];
        }

        $this->_queryParams["meta_query"]["relation"] = $operator;
        return $this;
    }

    /**
     * Add a meta query to the parameters.
     *
     * @param string $key - The meta key to query.
     * @param string $value - The value to compare.
     * @param string $compare - Optional. The comparison operator. Default is "=".
     * @param string $type - Optional. The type of the value. Default is "CHAR".
     * @return QueryBuilder
     */
    public function add_meta_query(string $key, string $value, string $compare = "=", string $type = "CHAR"): self
    {
        if (!isset($this->_queryParams["meta_query"])) {
            $this->_queryParams["meta_query"] = [];
        }

        array_push($this->_queryParams["meta_query"], [
            "key" => $key,
            "value" => $value,
            "compare" => $compare,
            "type" => $type,
        ]);

        return $this;
    }

    /**
     * Set the relation for tax queries.
     *
     * @param string $operator - The relation operator (AND or OR).
     * @return QueryBuilder
     */
    public function tax_query_relation(string $operator): self
    {
        if (!isset($this->_queryParams["tax_query"])) {
            $this->_queryParams["tax_query"] = [];
        }

        $this->_queryParams["tax_query"]["relation"] = $operator;
        return $this;
    }

    /**
     * Add a tax query to the parameters.
     *
     * @param string $taxonomy - The taxonomy to query.
     * @param string $field - The field to match (term_id, slug, or name).
     * @param mixed $terms - The terms to match.
     * @param string $operator - Optional. The operator for the query (IN or NOT IN). Default is IN.
     * @return QueryBuilder
     */
    public function add_tax_query(string $taxonomy, string $field, $terms, string $operator = self::IN): self
    {
        if (!isset($this->_queryParams["tax_query"])) {
            $this->_queryParams["tax_query"] = [];
        }

        array_push($this->_queryParams["tax_query"], [
            "taxonomy" => $taxonomy::TYPE,
            "field" => $field,
            "terms" => $terms,
            "operator" => $operator,
        ]);

        return $this;
    }

    /**
     * Add a filter to include posts after a specific date.
     *
     * @param string $date - The date to filter by.
     * @param bool $inclusive - Optional. Whether the date is inclusive. Default is true.
     * @return QueryBuilder
     */
    public function after($date, $inclusive = true): self
    {
        return $this->add_date_filter(["after" => $date], "", $inclusive);
    }

    /**
     * Add a filter to include posts before a specific date.
     *
     * @param string $date - The date to filter by.
     * @param bool $inclusive - Optional. Whether the date is inclusive. Default is true.
     * @return QueryBuilder
     */
    public function before($date, $inclusive = true): self
    {
        return $this->add_date_filter(["before" => $date], "", $inclusive);
    }

    /**
     * Add a date filter to the parameters.
     *
     * @param array $params - The date filter parameters.
     * @param string $compare - Optional. The comparison operator. Default is an empty string.
     * @param bool $inclusive - Optional. Whether the date is inclusive. Default is true.
     * @return QueryBuilder
     */
    public function add_date_filter($params, $compare = "", $inclusive = true): self
    {
        if (!isset($this->_queryParams["date_query"])) {
            $this->_queryParams["date_query"] = [];
        }

        if ($compare) {
            $params["compare"] = $compare;
        }

        $params["inclusive"] = $inclusive;

        array_push($this->_queryParams["date_query"], $params);

        return $this;
    }

    /**
     * Execute the query and return all matching models.
     *
     * @return array
     */
    public function find_all(): array
    {
        $postType = $this->_postType;
        return array_map(function ($model) use ($postType) {
            return new $postType($model->ID);
        }, $this->query()->posts);
    }

    /**
     * Execute the query and return the first matching model.
     *
     * @return PostType|null
     */
    public function find_one(): ?PostType
    {
        $models = $this->paginate(1)->find_all();
        return array_shift($models);
    }

    /**
     * Set a limit on the number of items to retrieve.
     *
     * @param int $limit - The limit to set.
     * @return QueryBuilder
     */
    public function limit(int $limit): self
    {
        return $this->paginate($limit);
    }

    /**
     * Retrieve a model by its ID.
     *
     * @param int $id - The ID of the model to retrieve.
     * @return PostType|null
     */
    public function find_by_id($id): ?PostType
    {
        return $this->where_ids([$id])->find_one();
    }

    /**
     * Get the total number of matching models.
     *
     * @return int
     */
    public function count_all(): int
    {
        return $this->query()->found_posts;
    }

    /**
     * Get the number of displayed models.
     *
     * @return int
     */
    public function count_displayed(): int
    {
        return $this->query()->post_count;
    }

    /**
     * Get the total number of pages for the query.
     *
     * @return int
     */
    public function page_number(): int
    {
        return $this->query()->max_num_pages;
    }

    /**
     * Generate pagination links.
     *
     * @return string|null
     */
    public function pagination(): ?string
    {
        return paginate_links([
            "base" => str_replace(99, "%#%", esc_url(get_pagenum_link(99))),
            "format" => "%#%/",
            "total" => $this->page_number(),
            "current" => $this->_queryParams["paged"],
            "type" => "plain",
            "prev_text" => __("«"),
            "next_text" => __("»"),
        ]);
    }

    /**
     * Execute the query and return the WP_Query instance.
     *
     * @return \WP_Query
     */
    private function query(): \WP_QUERY
    {
        if (
            !is_null($this->_query) and
            $this->_fetchedParams === $this->_queryParams
        ) {
            return $this->_query;
        }

        $this->_fetchedParams = $this->_queryParams;
        return $this->_query = new \WP_QUERY($this->_queryParams);
    }
}
