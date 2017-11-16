<?php

namespace Genesis\MethodPersister\Interfaces;

/**
 * Use this interface to implement your own persistence repository for caching methods.
 */
interface PersistenceRepositoryInterface
{
    /**
     * @param string $key
     * @param mixed $data
     * @param int $time
     * @param int $state
     *
     * @return PersistenceRepositoryInterface
     */
    public function set($key, $data, $time, $state);

    /**
     * @param string $key
     * @param int $state
     *
     * @return false on failure, string otherwise.
     */
    public function get($key, $state);
}
