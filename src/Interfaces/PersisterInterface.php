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
     * Persist a call.
     *
     * @param mixed $obj
     * @param null|mixed $method
     */
    public function persist($obj, $method = null);

    /**
     * Time for caching.
     *
     * @param int $time
     */
    public function overAPeriodOf($time);

    /**
     * Which storage state to use.
     *
     * @param int $state
     */
    public function in($state = self::STATE_CENTRAL);

    /**
     * Parameters to pass into the method.
     */
    public function withParameters();

    /**
     * Execute the persister.
     */
    public function execute();
}
