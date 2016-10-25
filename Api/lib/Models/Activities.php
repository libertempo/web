<?php
/*
 * Activities
 */
namespace \Models;

/*
 * Activities
 */
class Activities {
    /* @var int $offset Position in pagination. */
    private $offset;
/* @var int $limit Number of items to retrieve (100 max). */
    private $limit;
/* @var int $count Total number of items available. */
    private $count;
/* @var \\Models\Activity[] $history  */
    private $history;
}
