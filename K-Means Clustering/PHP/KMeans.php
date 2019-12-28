<?php
/**
 * Class used for performing the K-Means Based Clustering ML algorithm
 */

class KMeans
{
    // Properties ------------------------------------------------
    private $data_set;
    private $num_clusters;
    private $cluster_set;
    private $tabulated_data;
    private $need_to_recluster;

    // Constructor ----------------------------------------------------
    public function __construct($data_set,$num_clusters)
    {
        $this->data_set = $data_set;
        $this->num_clusters = $num_clusters;
        $this->cluster_set = array();
        $this->tabulated_data = array();
        $this->need_to_recluster = false;
    }
    
    // Methods -----------------------------------------------------------------
    /**
     * Initializes the cluster set with the required number of clusters (given in $num_clusters), with each being a random value from 
     * within the $data_set.
     */
    public function initialize_clusters()
    {
        // see the use of this variable below (*)
        $temporary_randomized_indices = array();
        for ($i = 0; $i < $this->num_clusters; $i++)
        {
            $randomized_index = mt_rand(0,(count($this->data_set) - 1));
            if ($this->data_set[$randomized_index] != 0 && (!(in_array($randomized_index,$temporary_randomized_indices))))
            {
                $this->cluster_set[] = new Cluster($this->data_set[$randomized_index]);
            }
            // (*) used to prevent the same $data_set point from being used for any subsequent Cluster values.
            $temporary_randomized_indices[] = $randomized_index;
        }
    }


    /**
     * Updates the current Clusters upon the need for a re-clustering.
     */
    public function update_clusters()
    {
        foreach($this->cluster_set as $cluster)
        {
            $cluster->update_cluster_value();
        }
    }


    /**
     * Performs the majority of the k-means algorithm. Includes data-to-cluster assignment, Cluster updates, and the repetition of the 
     * aforementioned until a final clustering has been achieved.
     */
    public function perform_k_means()
    {
        // initial data-to-cluster assignment
        $this->perform_tabulation();  

        // looping through the re-clustering until a final re-clustering has been achieved
        do {
            // this array will hold the previous Cluster's and their $associated_data_points
            $cluster_data_association_previous = array();

            foreach($this->get_cluster_set() as $cluster)
            {
                $cluster_data_association_previous[] = $cluster->get_associated_data_points();
            }
            // updating the Clusters
            $this->update_clusters();

            // clearing the previous Cluster associative array
            foreach($this->cluster_set as $cluster)
            {
                $cluster->clear_associated_data_points();
            }

            // the "data-to-cluster" re-assignment
            $this->perform_tabulation();
            
            // scan for the need of a re-clustering
            $this->scan_for_reclustering($cluster_data_association_previous);

        } while($this->need_to_recluster);
        
    }


    /**
     * Defines for each $data_set point value the distances to each Cluster and its nearest, or "assigned," cluster.
     */
    public function perform_tabulation()
    {
        for ($i = 0; $i < count($this->data_set); $i++)
        {
            // this array will contain the distances and assigned cluster for each $data_set point *
            $data_to_cluster_relation = array();

            // calculating the distances
            for ($j = 0; $j < $this->num_clusters; $j++)
            {
                $data_to_cluster_relation[] = $this->calculate_euclidean_distance((int)$this->data_set[$i],$this->cluster_set[$j]->get_cluster_value());
            }

            // assigning to the current data point the nearest cluster
            $data_to_cluster_relation[] = $this->identify_nearest_cluster($data_to_cluster_relation);

            // finally tabulates all of the data * 
            $this->tabulated_data[$this->data_set[$i]] = $data_to_cluster_relation;
        }
        // adding each $data_set point value to the appropriate Cluster's list of attached $data_set point values
        $this->create_cluster_association($this->tabulated_data);
    }


    /**
     * Scans the current cluster associative array (CLA) to check for a difference compared to the previous one.
     * 
     * If a difference has been identified, then the $data_set will be reclustered. If there isn't one present, then we have reached the 
     * final clustering.
     */
    public function scan_for_reclustering($cluster_data_association_previous)
    {
        $cluster_data_association_current = array(); 
        foreach($this->cluster_set as $cluster)
        {
            $cluster_data_association_current[] = $cluster->get_associated_data_points();
        }
        $current_counter = 0;
        foreach($cluster_data_association_previous as $data_array_previous)
        {
            $current_mapping = $cluster_data_association_current[$current_counter];
            foreach($current_mapping as $mapped_value)
            {
                if(!(in_array($mapped_value,$data_array_previous)))
                {
                    $this->need_to_recluster = true;
                    break 2;
                }
                else
                {
                    $this->need_to_recluster = false;
                }
            }
            $current_counter++;
        }
    }


    /**
     * Calculates the Euclidean distance between a $data_set point and a Cluster.
     */
    public function calculate_euclidean_distance($data_value,$cluster_value)
    {
        $square = pow(($data_value-$cluster_value),2);
        return sqrt($square);
    }


    /**
     * Identifies the cluster which is closest to the current $data_set point value.
     */
    public function identify_nearest_cluster($distances)
    {
        $smallest_distance = $distances[0];
        $index_of_nearest_cluster = 0;
        for ($i = 0; $i < count($distances); $i++)
        {
            if ($distances[$i] < $smallest_distance)
            {
                $smallest_distance = $distances[$i];
                $index_of_nearest_cluster = $i;
            }
        }
        return $index_of_nearest_cluster;
    }


    /**
     * Assigns to an associative array the input Cluster as a key and its assigned $data_set points as a mapped "values" array.
     */
    public function create_cluster_association($tabulated_data)
    {
        foreach($tabulated_data as $data_point => $cluster_relationship)
        {
            $this->cluster_set[$cluster_relationship[(count($cluster_relationship)-1)]]->add($data_point);
        }
    }

    /**
     * Retrieves and returns the final clustering once it has been achieved.
     */
    public function write_solution_to_file()
    {
        $filename = "solutions.txt";

        // creating a .txt file to hold the solutions to the clustering
        $fh = fopen($filename, 'w') or die("Failed to open/create a new solutions file.");

        // indices start at 0, but logically speaking, the first cluster should be labeled as Cluster "1", and not Cluster "0."
        $cluster_number = 1;
        for ($i = 0; $i < count($this->cluster_set); $i++)
        {
            $beginning_output = "Cluster $cluster_number (" . $this->cluster_set[$i]->get_cluster_value() . "): {";
            fwrite($fh,$beginning_output);

            $cluster_contents = "";

            $k_means_solution = array();
            $k_means_solution[] = $this->cluster_set[$i]->get_associated_data_points();
            foreach($k_means_solution as $partial_solution)
            {
                for ($j = 0; $j < count($partial_solution); $j++)
                {
                    $cluster_contents .= "$partial_solution[$j], ";
                }
                $cluster_contents = rtrim($cluster_contents," ,");
                fwrite($fh,$cluster_contents);
            }

            $ending_output = "}" . PHP_EOL;
            fwrite($fh,$ending_output);
            $cluster_number++;
        }
        // closing the solutions file
        fclose($fh);
        
        if (file_exists($filename)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
        }

        // terminating the now completed script;
        exit;
    }


    //----------------- Getter Methods --------------


    /**
     * Gets the input data set for $this problem.
     */
    public function get_data_set()
    {
        return $this->data_set;
    }


     /**
     * Gets the number of clusters required for $this problem.
     */
    public function get_num_clusters()
    {
        return $this->num_clusters;
    }


    /**
     * Gets the cluster set for $this problem.
     */
    public function get_cluster_set()
    {
        return $this->cluster_set;
    }

}
?>