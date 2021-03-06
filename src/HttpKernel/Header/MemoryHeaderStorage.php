<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\HttpKernel\Header;

/**
 * Handles HTTP headers in memory (for unit tests).
 */
class MemoryHeaderStorage implements HeaderStorageInterface
{
    /**
     * @var array
     */
    private $headers;

    /**
     * @param array $headers
     */
    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $header): void
    {
        $this->headers[] = $header;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->headers = [];
    }
}
