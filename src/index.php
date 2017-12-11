<?php

require_once('CourtNames.class.php');

$lwacExtractorObj = new LwacExtractor();
$lwacExtractorObj->run();



/**
 * Testing LwacExtractor.php function in a controlled environment
 */
class LwacExtractor {


    protected $contents;
    protected $filename;
    public $nl = '<br />';
    public $file_name = '_missing-courts.txt';
    protected $new_court_pseudonym_list;


    public function __construct(){

        $objCourtNames = new CourtNames();
        $this->new_court_pseudonym_list = $objCourtNames->getCompleteCourtPseudonymList();

        $this->run();
    }

    /**
     * Run through all entries in the txt file
     */
    public function run(){

        $file_contents = file($this->file_name);

        foreach ($file_contents as $line){

            //$standard_hearing_date = $this->getHearingDate($line);
            $this->NewgetCourtCenter($line);
        }
    }



    private function NewgetCourtCenter($court_name)
    {
        $correct_court_names = $this->new_court_pseudonym_list;

        //echo '<pre>'; print_r($correct_court_names); echo '</pre>';

        $court_name = trim($court_name);

        if (empty($correct_court_names[$court_name])){

            if (!in_array($court_name, $correct_court_names)){

                $error_message = $court_name . ' does not exist in file '.$this->filename;
                var_dump($error_message);
            }

            $actual_court_name = $court_name;

        } else {

            $actual_court_name = $correct_court_names[$court_name];
        }

        return $actual_court_name;
    }


    /**
     * Get the Hearing date from the Lwac document
     * @return mixed|string
     */
    private function getHearingDate($hearing_date_raw){

        $hd_days = '([\d]{1,2})(?:th|st|nd|rd)?'; // Hearing date day
        $hd_months = 'january|jan|february|feb|march|mar|april|apr|may|june|jun|july|jul|august|aug|september|sep|october|oct|november|nov|december|dec|[0-9]{0,2}'; // Hearing date month
        $hd_years = '(?:20)*[0|1]{1}[0-9]{1}'; // Hearing date year

        $hearing_date_regex_pattern = '/'.$hd_days.'[\s|\/\.-]*('.$hd_months.')?[\s,\/\.-]*('.$hd_years.')?/i';
        $hearing_date_ignore_text_array = array('date to be fixed', 'to be confirmed', 'to be fixed', '(to be advised)',
            '<wshearingdate>', 'tbc', 'dtbc', 'dtbf', 'date to be confirmed', 'null', 'backer trial');

        $hearing_date_raw = trim(strtolower($hearing_date_raw));

        // strtotime cannot convert dates with a forward slash
        $hearing_date_clean = str_replace('/', '-', $hearing_date_raw);

        // strtotime has an issue recognising dates with commas
        $hearing_date_clean = str_replace(',', ' ', $hearing_date_clean);

        preg_match($hearing_date_regex_pattern, $hearing_date_clean, $match_dates);

        // If all date items have been matched
        if (sizeof($match_dates) >= 4) {

            // If the year is only 2 digits
            if (strlen($match_dates[3]) == 2) {

                $match_dates[3] = '20' . $match_dates[3];
            }

            $hearing_date_matched_all = $match_dates[1] .'-'. $match_dates[2] .'-'. $match_dates[3];

        } else {
            $hearing_date_matched_all = $hearing_date_clean;
        }

        $standard_hearing_date = date('d-M-Y', strtotime($hearing_date_matched_all));

        if (in_array($hearing_date_raw, $hearing_date_ignore_text_array, true)) {

            $standard_hearing_date = '25-12-' . date('Y', time());
        }

        static $count = 0;

        if ($standard_hearing_date == '01-Jan-1970' || $standard_hearing_date == '25-12-' . date('Y', time())) {

            $count++;
            var_dump($count);
            var_dump($hearing_date_raw);
            var_dump($match_dates);
            var_dump($standard_hearing_date);
            echo '<hr>';
        }

        return $standard_hearing_date;
    }
}