<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering;

use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;

/**
 * MeiliSearch client redirecting one index to another, used to import models into another index.
 */
class RedirectingClient extends Client
{
    public function __construct(protected string $from, protected string $to, string $url, ?string $apiKey = null)
    {
        parent::__construct($url, $apiKey);
    }

    public function index(string $uid): Indexes
    {
        return parent::index($uid === $this->from ? $this->to : $uid);
    }
}
