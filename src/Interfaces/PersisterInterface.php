<?php

namespace Genesis\MethodPersister\Interfaces;

interface PersisterInterface
{
    /**
     * Store data per user.
     */
    const STATE_DISTRIBUTE = 1;

    /**
     * Store data once for all.
     */
    const STATE_CENTRAL = 2;

    /**
     * @param mixed $obj The object to call on.
     */
    public function persist($obj, ?string $method = null): PersisterInterface;

    /**
     * Cache for how long.
     */
    public function overAPeriodOf(string $time): PersisterInterface;

    /**
     * Method of caching.
     */
    public function in(int $state = self::STATE_CENTRAL): PersisterInterface;

    /**
     * Parameters to pass into the method.
     */
    public function withParameters(): PersisterInterface;

    /**
     * Execute the persister.
     */
    public function execute();
}
