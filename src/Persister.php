<?php

namespace Genesis\MethodPersister;

use Genesis\MethodPersister\Interfaces\PersisterInterface;

class Persister implements Interfaces\PersisterInterface
{
    /**
     * @var object The object on which the method exists.
     */
    private $obj;

    /**
     * @var string The method to call.
     */
    private $on;

    /**
     * @var array The arguments for the method called.
     */
    private $args;

    /**
     * @var time The time to cache.
     */
    private $over;

    /**
     * @var int The state in which to store the method result.
     */
    private $in;

    /**
     * @param persistenceRepositoryInterface $persistenceRepository
     */
    public function __construct(Interfaces\persistenceRepositoryInterface $persistenceRepository)
    {
        $this->persistenceRepository = $persistenceRepository;
    }

    /**
     * @param mixed $obj The object to cache.
     */
    public function persist($obj, ?string $method = null): PersisterInterface
    {
        $this->obj = $obj;
        $this->on = $method;
        $this->args = [];

        return $this;
    }

    public function overAPeriodOf(string $time): PersisterInterface
    {
        $this->over = $time;

        return $this;
    }

    public function in(int $state = self::STATE_CENTRAL): PersisterInterface
    {
        $this->in = $state;

        return $this;
    }

    public function withParameters(): PersisterInterface
    {
        $this->args = func_get_args();

        return $this;
    }

    public function execute()
    {
        return $this->persistResult(
            array(
                $this->obj,
                $this->on
            ),
            $this->args,
            $this->over,
            $this->in ?? self::STATE_CENTRAL
        );
    }

    /**
     * @return val The value stored for the method
     */
    private function persistResult(array $callable, array $args, string $time, int $state = self::STATE_CENTRAL)
    {
        $val = null;
        // Make sure the key also looks at the argument supplied so it can detect a change in the arg
        $key = $this->className($callable[0]).'::'.$callable[1].'::'.serialize($args);

        if (! $val = $this->persistenceRepository->get($key, $state)) {
            $val = call_user_func_array($callable, $args);
            $this->persistenceRepository->set($key, $val, $time, $state);
        }

        return $val;
    }

    private function className($name): string
    {
        return str_replace('\\', '', get_class($name));
    }
}
