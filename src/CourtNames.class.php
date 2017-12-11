<?php

/**
 * CourtNames
 * @author Farzad kn
 * @description Provides a list of court pseudonyms based on the initial courts list file
 */

class CourtNames {

    /**
     * Name of text file containing court pseudonym list
     * @var string
     */
    public $courts_list_filename = "court_names_list.txt";

    /**
     * Array contents of court pseudonym list file
     * @var array
     */
    public $courts_pseudonym_file_content;

    /**
     * Original pseudonym array with new variations
     * @var array
     */
    public $new_court_pseudonym_list = array();


    /**
     *
     */
    public function __construct(){

        $this->courts_pseudonym_file_content = $this->getCourtsPseudonymFileContents();
        $this->new_court_pseudonym_list = $this->getCompleteCourtPseudonymList();
    }


    public function getCourtNameFromRawData($court_name_raw){

        $correct_court_names = $this->new_court_pseudonym_list;

        if (empty($correct_court_names[$court_name_raw])){

            if (!in_array($court_name_raw, $correct_court_names)){

                $error_message = $court_name_raw . ' does not exist';
                var_dump($error_message);
            }

            $actual_court_name = $court_name_raw;

        } else {

            $actual_court_name = $correct_court_names[$court_name_raw];
        }

        return $actual_court_name;
    }


    /**
     * Get a list of all correct court names from text file
     * @return array
     */
    private function getCourtsPseudonymFileContents(){

        // Clear PHP cache for dirname() function
        clearstatcache();

        $correct_court_names = [];

        $courts_list_filename = $this->courts_list_filename;

	    $courts_list_filename_location = dirname(__FILE__) . DIRECTORY_SEPARATOR . $courts_list_filename;

        if (file_exists($courts_list_filename_location)){

            $file = fopen($courts_list_filename_location, "r");

            while(!feof($file)){

                $line = fgets($file);

                if (!empty($line)) {

                    $court_name_location_explode = explode("=>", trim($line));
                    list($key, $value) = $court_name_location_explode;
                    $correct_court_names[$key] = $value;
                }
            }
            fclose($file);

        } else {

            echo "File $courts_list_filename_location does not exist";
        }

        return $correct_court_names;
    }


    /**
     * Add new pseudonym to the list of courts
     * @param $new_court_pseudonym
     * @param $court_actual_name
     */
    private function addNewPseudonym($new_court_pseudonym, $court_actual_name){

        // If the new pseudonym is the same as the courts actual name do not add
        if ($new_court_pseudonym != $court_actual_name){

            // If the pseudonym already exists in the list do not add
            if (!array_key_exists($new_court_pseudonym, $this->courts_pseudonym_file_content)){

                $this->new_court_pseudonym_list[$new_court_pseudonym] = $court_actual_name;
            }
        }
    }


    /**
     * Get Court name variations from the existing Court name list
     */
    public function getCourtPseudonymVariations(){

        $magistrates_spelling_options = array('Magistrates', 'Magistrates\'', 'Magistrate\'s', 'MC');

        foreach ($this->courts_pseudonym_file_content as $court_pseudonym => $court_actual_name) {

            // Find any varying instances of Magistrates
            $pattern = '/^(.*)(?:[\s]?)(Magistrate[\']?s[\']?[s]?|MC)(?:[\s]?)(.*)$/i';
            preg_match($pattern, $court_pseudonym, $court_pseudonym_matches);

            $court_pseudonym_matches = $this->trimArrayContentAndRemoveEmptyElements($court_pseudonym_matches);

            // If Magistrates exists in the text
            if (sizeof($court_pseudonym_matches) > 0){

                // If there is any text after Magistrates
                if (sizeof($court_pseudonym_matches) == 4){

                    // If Court is missing from the pseudonym then add a new entry
                    if(!preg_match("/(court)/i", $court_pseudonym_matches[3])) {

                        $spacing = $this->determineCorrectSpacing($court_pseudonym_matches[3]);

                        $new_court_pseudonym = $court_pseudonym_matches[1] .' '. $court_pseudonym_matches[2] .' Court' . $spacing . $court_pseudonym_matches[3];

                        $this->addNewPseudonym($new_court_pseudonym, $court_actual_name);
                    }
                } else {

                    foreach($magistrates_spelling_options as $magistrates_spelling){

                        $new_court_pseudonym = $court_pseudonym_matches[1] .' '. $magistrates_spelling;

                        $this->addNewPseudonym($new_court_pseudonym, $court_actual_name);

                        if ($magistrates_spelling != 'MC') {

                            $new_court_pseudonym_with_court = $court_pseudonym_matches[1] .' '. $magistrates_spelling .' Court';

                            $this->addNewPseudonym($new_court_pseudonym_with_court, $court_actual_name);
                        }
                    }
                }
            }
        }
    }


    /**
     * Trim all spaces from content of an array and remove any empty elements
     * @param $array
     * @return $array_filtered_trimmed
     */
    private function trimArrayContentAndRemoveEmptyElements($array){

        $array_filtered = array_filter($array);

        $array_filtered_trimmed = array_map('trim',$array_filtered);

        return $array_filtered_trimmed;
    }


    /**
     * Determine the correct spacing between characters
     * @param $string
     * @return $spacing
     */
    private function determineCorrectSpacing($string){

        $spacing = ' ';

        // Match for commas or parenthesis
        if(preg_match("/^([,)]+)/", $string)) {

            $spacing = '';
        }

        return $spacing;
    }


    /**
     * Return newly created court pseudonym list
     */
    public function getNewCourtPseudonymList(){

        $this->getCourtPseudonymVariations();
        return $this->new_court_pseudonym_list;
    }


    /**
     * @return array
     */
    public function getCompleteCourtPseudonymList(){

        $new_court_pseudonym_list = $this->getNewCourtPseudonymList();
        $original_court_pseudonym_list = $this->courts_pseudonym_file_content;

        $complete_court_pseudonym_list = array_merge_recursive($new_court_pseudonym_list, $original_court_pseudonym_list);
        ksort($complete_court_pseudonym_list);

        //echo 'New courts added: ', sizeof($new_court_pseudonym_list), ' Old courts size: ', sizeof($original_court_pseudonym_list), '<br />';
        //echo '<pre>';print_r($this->new_court_pseudonym_list); echo '</pre>';

        //$complete_court_pseudonym_list = array_slice($complete_court_pseudonym_list, 0, 5);
	return $complete_court_pseudonym_list;
    }
}
