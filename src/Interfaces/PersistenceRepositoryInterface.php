<?php

namespace Genesis\MethodPersister\Interfaces;

interface PersistenceRepositoryInterface
{
	/**
     * @param string $key
     * @param mixed $data
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