<?php

namespace Genesis\MethodPersister;

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
     * Persist a call.
     *
     * @param mixed $obj
     * @param null|mixed $method
     */
    public function persist($obj, $method = null)
    {
        $this->obj = $obj;
        $this->on = $method;
        $this->args = [];

        return $this;
    }

    /**
     * Time for caching.
     *
     * @param int $time
     */
    public function overAPeriodOf($time)
    {
        $this->over = $time;

        return $this;
    }

    /**
     * Which storage state to use.
     *
     * @param int $state
     */
    public function in($state = self::STATE_CENTRAL)
    {
        $this->in = $state;

        return $this;
    }

    /**
     * Parameters to pass into the method.
     */
    public function withParameters()
    {
        $this->args = func_get_args();

        return $this;
    }

    /**
     * Execute the persister.
     */
    public function execute()
    {
        return $this->persistResult(
            array(
                $this->obj,
                $this->on
            ),
            $this->args,
            $this->over,
            $this->in
        );
    }

    /**
     * @param callable an array with the object and its method to call
     * @param args the arguments to pass to the method to call
     * @param time The amount of time before the data needs to be refreshed
     * @param state/optional Which method is used to store the data
     * @param mixed $time
     * @param mixed $state
     *
     * @return val The value stored for the method
     */
    private function persistResult(array $callable, array $args, $time, $state = self::STATE_DISTRIBUTE)
    {
        $val = null;
        // Make sure the key also looks at the argument supplied so it can detect a change in the arg
        $key = get_class($callable[0]).'::'.$callable[1].'::'.serialize($args);

        if (! $val = $this->persistenceRepository->get($key, $state)) {
            $val = call_user_func_array($callable, $args);
            $this->persistenceRepository->set($key, $val, $time, $state);
        }

        return $val;
    }
}
