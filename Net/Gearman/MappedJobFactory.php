<?php
/**
 * Interface for Danga's Gearman job scheduling system
 *
 * PHP version 5.1.0+
 *
 * LICENSE: This source file is subject to the New BSD license that is
 * available through the world-wide-web at the following URI:
 * http://www.opensource.org/licenses/bsd-license.php. If you did not receive
 * a copy of the New BSD License and are unable to obtain it through the web,
 * please send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Net
 * @package   Net_Gearman
 * @author    Ray Rehbein <mrrehbein@gmail.com>
 * @copyright 2011 Deal Express LLC
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Net_Gearman
 * @link      http://www.danga.com/gearman/
 */

require_once 'Net/Gearman/Exception.php';

/**
 * Create jobs based on a map of function to class name.
 *
 * Note: this assumes that either the classes are already loaded or they will
 * be handled by an auto-loader
 *
 * @category  Net
 * @package   Net_Gearman
 * @author    Ray Rehbein <mrrehbein@gmail.com>
 * @copyright 2011 Deal Express LLC
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   Release: @package_version@
 * @link      http://www.danga.com/gearman/
 * @see       Net_Gearman_Job_Common, Net_Gearman_Worker
 */
class Net_Gearman_MappedJobFactory
{
    /**
     * Map of function to class name
     *
     * @var array
     */
    protected $map = array();

    /**
     * Constructor
     *
     * @param array $jobMap inital map of job to classes
     *
     * @return void
     */
    public function __construct($jobMap = array())
    {
        $this->mapJobClasses($jobMap);
    }

    /**
     * Copy an associative array of job => class into the mapped factory
     *
     * @param array $jobMap map of job to classes
     *
     * @return void
     */
    public function mapJobClasses($jobMap)
    {
        foreach ($jobMap as $job => $class) {
            $this->mapJobClass($job, $class);
        }
    }

    /**
     * Map a job/function to a class name.
     *
     * @param string $job   gearman function name
     * @param string $class class to use for handling the job
     *
     * @return void
     */
    public function mapJobClass($job, $class)
    {
        $this->map[$job] = $class;
    }

    /**
     * Get class name that a job is mapped to
     *
     * @param string $job gearman function name
     *
     * @return string
     */
    public function getJobClassName($job)
    {
        if (!isset($this->map[$job])) {
            throw new Net_Gearman_Job_Exception('Job not found');
        }

        return $this->map[$job];
    }

    /**
     * Copy map of jobs into worker as abilities for the worker
     *
     * @param Net_Gearman_Worker $worker     Worker object to add abilities to
     * @param array              $initParams Parameters for job constructor
     *                                       as per $worker->addAbility
     *
     * @return void
     */
    public function mapToWorker(Net_Gearman_Worker $worker, $initParams = array())
    {
        foreach ($this->map as $job => $class) {
            $worker->addAbility($job, null, $initParams);
        }
    }

    /**
     * Create an instance of a job
     *
     * The Net_Geraman_Worker class creates connections to multiple job servers
     * and then fires off jobs using this function. It hands off the connection
     * which made the request for the job so that the job can communicate its
     * status from there on out.
     *
     * @param string $job        Name of job (func in Gearman terms)
     * @param object $conn       Instance of Net_Gearman_Connection
     * @param string $handle     Gearman job handle of job
     * @param array  $initParams initialisation parameters for job
     *
     * @return object Instance of Net_Gearman_Job_Common child
     * @see Net_Gearman_Job_Common
     * @throws Net_Gearman_Exception
     */
    public function factory($job, $conn, $handle, $initParams=array())
    {
        $class = $this->getJobClassName($job);

        $instance = new $class($conn, $handle, $initParams);
        if (!$instance instanceof Net_Gearman_Job_Common) {
            throw new Net_Gearman_Job_Exception('Job is of invalid type');
        }

        return $instance;
    }
}