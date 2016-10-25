<?php
/*
 * PriceEstimate
 */
namespace \Models;

/*
 * PriceEstimate
 */
class PriceEstimate {
    /* @var string $productId Unique identifier representing a specific product for a given latitude &amp; longitude. For example, uberX in San Francisco will have a different product_id than uberX in Los Angeles */
    private $productId;
/* @var string $currencyCode [ISO 4217](http://en.wikipedia.org/wiki/ISO_4217) currency code. */
    private $currencyCode;
/* @var string $displayName Display name of product. */
    private $displayName;
/* @var string $estimate Formatted string of estimate in local currency of the start location. Estimate could be a range, a single number (flat rate) or \&quot;Metered\&quot; for TAXI. */
    private $estimate;
/* @var Number $lowEstimate Lower bound of the estimated price. */
    private $lowEstimate;
/* @var Number $highEstimate Upper bound of the estimated price. */
    private $highEstimate;
/* @var Number $surgeMultiplier Expected surge multiplier. Surge is active if surge_multiplier is greater than 1. Price estimate already factors in the surge multiplier. */
    private $surgeMultiplier;
}
