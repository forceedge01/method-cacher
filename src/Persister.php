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
     * Holds path of the cache folder.
     */
    private $centralStoragePath;

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
     * @param string $centralStoragePath
     */
    public function __construct($centralStoragePath)
    {
        $this->centralStoragePath = $centralStoragePath;
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
                $this->obj, $this->on
            ), 
            $this->args,
            $this->over,
            $this->in
        );
    }

    /**
     * @param key The key with which to persist the value with
     * @param val The value to persist
     * @param time The amount of time before the data needs to be refreshed
     * @param state/optional Which method is used to store the data
     *
     * @return $this
     */
    public function persistState($key, $val, $time, $state = self::STATE_DISTRIBUTE)
    {
        // Prepare data for storage
        $data = json_encode(['value' => serialize($val), 'time' => strtotime($time)]);

        // Save data in storage 
        if($state == self::STATE_DISTRIBUTE) {
            // Check for key clashing
            $_SESSION['persistentState'][$key] = $data;
        } else {
            try {
                // Save to file
                if(! file_put_contents($this->centralStoragePath . $key, $data)) {
                    // Fail safe, display 500 for the first time then recover.
                    if(mkdir($this->centralStoragePath, 0777, true)) {
                        throw new \Exception(sprintf(
                            '"%s" directory was not found but is now created, refresh to continue.', 
                            $this->centralStoragePath
                        ));
                    }
                }
            } catch(\Exception $e) {
                if(! is_dir($this->centralStoragePath)) {
                    if(mkdir($this->centralStoragePath, 0777, true)) {
                        throw new \Exception(sprintf(
                            '"%s" directory was not found but is now created, refresh to continue.', 
                            $this->centralStoragePath
                        ));
                    } else {
                        throw new \Exception(sprintf(
                            'Unable to create directory "%s", check permissions.', 
                            $this->centralStoragePath
                        ));
                    }
                }

                throw $e;
            }            
        }

        return $this;
    }

    /**
     * @param key The key with which to persist the value with
     * @param state/optional Which method is used to store the data
     *
     * @return value The stored value for the key provided
     */
    public function persistent($key, $state = self::STATE_DISTRIBUTE)
    {
        $val = null;

        // Get data from storage
        if($state == self::STATE_DISTRIBUTE) {
            if(! isset($_SESSION['persistentState'][$key])) {
                return false;
            }

            $val = $_SESSION['persistentState'][$key];
        } else {
            // Get from file
            if(! file_exists($this->centralStoragePath . $key)) {
                return false;
            }

            $val = file_get_contents($this->centralStoragePath . $key);
        }

        // Revert conversion form json to array
        $val = json_decode($val, true);

        // Check if the data is valid
        if(! $val || $val['time'] < time()) {
            return false;
        }

        return unserialize($val['value']);
    }

    /**
     * @param callable an array with the object and its method to call
     * @param args the arguments to pass to the method to call
     * @param time The amount of time before the data needs to be refreshed
     * @param state/optional Which method is used to store the data
     *
     * @return val The value stored for the method
     */
    public function persistResult(array $callable, array $args, $time, $state = self::STATE_DISTRIBUTE)
    {
        $val = null;
        // Make sure the key also looks at the argument supplied so it can detect a change in the arg
        $key = get_class($callable[0]).'::'.$callable[1].'::'.serialize($args);

        if(! $val = $this->persistent($key, $state)) {
            $val = call_user_func_array($callable, $args);
            $this->persistState($key, $val, $time, $state);
        }

        return $val;
    }
}