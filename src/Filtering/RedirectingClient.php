<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Filtering;

use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;

/**
 * MeiliSearch client redirecting one index to another through the given client.
 *
 * Only `index()` is supported, which is all Scout's engine uses when updating documents.
 */
class RedirectingClient extends Client
{
    public function __construct(protected Client $client, protected string $from, protected string $to)
    {
    }

    public function index(string $uid): Indexes
    {
        return $this->client->index($uid === $this->from ? $this->to : $uid);
    }
}
