<?php

namespace Genesis\MethodPersister\Interfaces;

/**
 * Use this interface to implement your own persistence repository for caching methods.
 */
interface PersistenceRepositoryInterface
{
    /**
     * @param mixed $data The data to persist.
     */
    public function set(string $key, $data, string $time, int $state): PersistenceRepositoryInterface;

    public function get(string $key, int $state);
}
