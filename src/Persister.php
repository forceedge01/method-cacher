<?php

namespace Genesis\MethodPersister;

Class Persister
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
     * @var object $obj The object on which the method exists.
     */
    private $obj;

    /**
     * @var string $on The method to call.
     */
    private $on;

    /**
     * @var array $args The arguments for the method called.
     */
    private $args;

    /**
     * @var time $over The time to cache.
     */
    private $over;

    /**
     * @var int $in The state in which to store the method result.
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
        return $this->persistResult(array(
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
     *
     * @return val The value stored for the method
     */
    private function persistResult(array $callable, array $args, $time, $state = self::STATE_DISTRIBUTE)
    {
        $val = null;
        // Make sure the key also looks at the argument supplied so it can detect a change in the arg
        $key = get_class($callable[0]).'::'.$callable[1].'::'.serialize($args);

        if(! $val = $this->persistenceRepository->get($key, $state)) {
            $val = call_user_func_array($callable, $args);
            $this->persistenceRepository->set($key, $val, $time, $state);
        }

        return $val;
    }
}