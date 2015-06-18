<?php namespace resque;

/**
 * Yii Resque Component
 *
 * Yii component to work with php resque
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Rolies Deby <rolies106@gmail.com>
 * @copyright     Copyright 2012, Rolies Deby <rolies106@gmail.com>
 * @link          http://www.rolies106.com/
 * @package       yii-resque
 * @since         0.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
use resque\ResqueAutoloader;
//use resque\lib\Resque;
//use resque\lib\ResqueScheduler;

class Resque extends \yii\base\Component
{

    /**
     * @var string Redis server address
     */
    public $server = 'localhost';

    /**
     * @var string Redis port number
     */
    public $port = '6379';

    /**
     * @var int Redis database index
     */
    public $database = 0;

    /**
     * @var string Redis password auth
     */
    public $password = '';
    public $prefix = '';

    /**
     * @var mixed include file in daemon (userul for defining YII_DEBUG, etc), may be string or array
     */
    public $includeFiles = '';

    /**
     * Initializes the connection.
     */
    public function init()
    {
        parent::init();

        if (!class_exists('ResqueAutoloader', false)) {

            # Turn off our amazing library autoload
            spl_autoload_unregister(['Yii', 'autoload']);
            # Include Autoloader library
            include(dirname(__FILE__) . '/ResqueAutoloader.php');

            # Run request autoloader
            ResqueAutoloader::register();
            # Give back the power to Yii
            spl_autoload_register(array('Yii', 'autoload'));
        }
        \resque\lib\Resque::setBackend($this->server . ':' . $this->port, $this->database, $this->password);
        if ($this->prefix) {
            Resque::redis()->prefix($this->prefix);
        }
    }

    /**
     * Create a new job and save it to the specified queue.
     *
     * @param string $queue The name of the queue to place the job in.
     * @param string $class The name of the class that contains the code to execute the job.
     * @param array $args Any optional arguments that should be passed when the job is executed.
     *
     * @return string
     */
    public function createJob($queue, $class, $args = array(), $track_status = false)
    {

        return \resque\lib\Resque::enqueue($queue, $class, $args, $track_status);
    }

    /**
     * Create a new scheduled job and save it to the specified queue.
     *
     * @param int $in Second count down to job.
     * @param string $queue The name of the queue to place the job in.
     * @param string $class The name of the class that contains the code to execute the job.
     * @param array $args Any optional arguments that should be passed when the job is executed.
     *
     * @return string
     */
    public function enqueueJobIn($in, $queue, $class, $args = array())
    {
        return \resque\lib\ResqueScheduler::enqueueIn($in, $queue, $class, $args);
    }

    /**
     * Create a new scheduled job and save it to the specified queue.
     *
     * @param timestamp $at UNIX timestamp when job should be executed.
     * @param string $queue The name of the queue to place the job in.
     * @param string $class The name of the class that contains the code to execute the job.
     * @param array $args Any optional arguments that should be passed when the job is executed.
     *
     * @return string
     */
    public function enqueueJobAt($at, $queue, $class, $args = array())
    {

        return \resque\lib\ResqueScheduler::enqueueAt($at, $queue, $class, $args);
    }

    /**
     * Get delayed jobs count
     *
     * @return int
     */
    public function getDelayedJobsCount()
    {
        return (int) \resque\lib\Resque::redis()->zcard('delayed_queue_schedule');
    }

    /**
     * Check job status
     *
     * @param string $token Job token ID
     *
     * @return string Job Status
     */
    public function status($token)
    {
        $status = new Resque_Job_Status($token);
        return $status->get();
    }

    /**
     * Return Redis
     *
     * @return object Redis instance
     */
    public function redis()
    {
        return  \resque\lib\Resque::redis();
    }

    /**
     * Get queues
     *
     * @return object Redis instance
     */
    public function getQueues()
    {
        return $this->redis()->zRange('delayed_queue_schedule', 0, -1);
    }
//    public function getValueByKey($key){
//        return $this->redis()->get($key);
//    }
}
