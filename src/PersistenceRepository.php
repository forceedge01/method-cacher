<?php

namespace Genesis\MethodPersister;

class PersistenceRepository implements Interfaces\PersistenceRepositoryInterface
{
    /**
     * Holds path of the cache folder.
     */
    private $centralStoragePath;

    /**
     * @param string $centralStoragePath
     */
    public function __construct($centralStoragePath)
    {
        $this->centralStoragePath = $centralStoragePath;
    }

    /**
     * @param string $key
     * @param mixed $data
     * @param string $time
     * @param int $state
     *
     * @return PersistenceRepositoryInterface
     */
    public function set($key, $data, $time, $state)
    {
        // Prepare data for storage
        $data = json_encode(['value' => serialize($data), 'time' => strtotime($time)]);

        // Save data in storage 
        if ($state == Persister::STATE_DISTRIBUTE) {
            // Check for key clashing
            $_SESSION['persistentState'][$key] = $data;
        } else {
            try {
                // Save to file
                if (! file_put_contents($this->centralStoragePath . $key, $data)) {
                    // Fail safe, display 500 for the first time then recover.
                    if (mkdir($this->centralStoragePath, 0777, true)) {
                        throw new \Exception(sprintf(
                            '"%s" directory was not found but is now created, refresh to continue.',
                            $this->centralStoragePath
                        ));
                    }
                }
            } catch (\Exception $e) {
                if (! is_dir($this->centralStoragePath)) {
                    if (mkdir($this->centralStoragePath, 0777, true)) {
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
     * @param string $key
     * @param int $state
     *
     * @return false on failure, string otherwise.
     */
    public function get($key, $state)
    {
        $val = null;

        // Get data from storage
        if ($state == Persister::STATE_DISTRIBUTE) {
            if (! isset($_SESSION['persistentState'][$key])) {
                return false;
            }

            $val = $_SESSION['persistentState'][$key];
        } else {
            // Get from file
            if (! file_exists($this->centralStoragePath . $key)) {
                return false;
            }

            $val = file_get_contents($this->centralStoragePath . $key);
        }

        // Revert conversion form json to array
        $val = json_decode($val, true);

        // Check if the data is valid
        if (! $val || $val['time'] < time()) {
            return false;
        }

        return unserialize($val['value']);
    }
}
