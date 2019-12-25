<?php
/**
 * Represents a Cluster within the K-Means Based Clustering ML Algorithm.
 */
class Cluster
{
    // Properties ------------------------------------------------
    private $cluster_value;
    private $mean_value;
    private $associated_data_points;

    // Constructor ----------------------------------------------------
    public function __construct($cluster_value)
    {
        $this->cluster_value = $cluster_value;
        $this->mean_value = 0;
        $this->associated_data_points = array();
    }

    // Methods -----------------------------------------------------------------
    /**
     * Adds a data point to $this Cluster's list of associated data points.
     */
    public function add($data_point)
    {
        $this->associated_data_points = $data_point;
    }


      /**
     * Updates a Cluster's $cluster_value property upon a finished clustering.
     */
    public function update_cluster_value()
    {
        $updated_counter = 1; // staring at one, as the initial Cluster's $cluster_value must be included within the mean calculation
        // but is not an actual part of the $this Cluster's list of $associated_data_points.
        foreach($this->associated_data_points as $data_point)
        {
            $this->cluster_value += $data_point;
            $updated_counter++;
        }
        $this->$cluster_value = (($this->cluster_value) / ($updated_counter));
    }


    /**
     * Gets the list of data points associated with $this Cluster.
     */
    public function get_associated_data_points()
    {
        return $this->associated_data_points;
    }
    

    /**
     * Gets the Cluster value of $this Cluster.
     */
    public function get_cluster_value()
    {
        return $this->cluster_value;
    }

    
}
?>