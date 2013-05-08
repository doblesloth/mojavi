<?php
/**
 * Shared memory class used with forking
 * @author hobby
 */
class SharedMemory {
    private $nameToKey = array();
    private $key;
    private $id;
    
    /**
     * Construct this class and setup the shared memory attachment
     * @param integer $key
     */
    function __construct($key = null){
        if ($key === null) {
            $tmp = tempnam('/tmp', 'PHP');
            $this->key = ftok($tmp, 'a');
            $this->id = shm_attach($this->key);
            $this->nameToKey[] = '';
            $this->updateMemoryVarList();
            shm_put_var($this->id, 1, 1);
        } else {
            $this->key = $key;
            $this->id = sem_get($this->key);
            $this->refreshMemoryVarList();
            shm_put_var($this->id, 1, shm_get_var($this->id, 1) + 1);
        }
        if (!$this->id) {
            die('Unable to create shared memory segment');
        }
    }
    
    /**
     * Sleep for a while
     */
    function __sleep(){
        shm_detach($this->id);
    }
    
    /**
     * Destroys the shared memory segment
     */
    function __destruct(){
        if (shm_get_var($this->id, 1) == 1){
            // I am the last listener so kill shared memory space
            $this->remove();
        } else {
            shm_detach($this->id);
            shm_put_var($this->id, 1, shm_get_var($this->id, 1) - 1);
        }
    }
    
    /**
     * Wake up from a sleep
     */
    function __wakeup(){
        $this->id = sem_get($this->key);
        shm_attach($this->id);
        $this->refreshMemoryVarList();
        shm_put_var($this->id, 1, shm_get_var($this->id, 1) + 1);
    }
    
    /**
     * Returns the key used for shared memory
     * @return string
     */
    function getKey(){
        return $this->key;
    }
    
    /**
     * Removes an entry from the shared memory segment
     */
    function remove(){
        shm_remove($this->id);
    }
    
    /**
     * Refreshes the memory list
     */
    function refreshMemoryVarList(){
        $this->nameToKey = shm_get_var($this->id, 0);
    }
    
    /**
     * Updates the shared memory list
     */
    function updateMemoryVarList(){
        shm_put_var($this->id, 0, $this->nameToKey);
    }
    
    /**
     * Returns a variable from shared memory
     * @param mixed $var
     */
    function __get($var){
        if(!in_array($var, $this->nameToKey)){
            $this->refreshMemoryVarList();
        }
        return shm_get_var($this->id, array_search($var, $this->nameToKey));
    }
    
    /**
     * Sets a variable in shared memory
     * @param mixed $var
     * @param mixed $val
     */
    function __set($var, $val){
        if(!in_array($var, $this->nameToKey)){
            $this->refreshMemoryVarList();
            $this->nameToKey[] = $var;
            $this->updateMemoryVarList();
        }
        shm_put_var($this->id, array_search($var, $this->nameToKey), $val);
    }
}