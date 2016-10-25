<?php
/*
 * Product
 */
namespace \Models;

/*
 * Product
 */
class Product {
    /* @var string $productId Unique identifier representing a specific product for a given latitude &amp; longitude. For example, uberX in San Francisco will have a different product_id than uberX in Los Angeles. */
    private $productId;
/* @var string $description Description of product. */
    private $description;
/* @var string $displayName Display name of product. */
    private $displayName;
/* @var string $capacity Capacity of product. For example, 4 people. */
    private $capacity;
/* @var string $image Image URL representing the product. */
    private $image;
}
