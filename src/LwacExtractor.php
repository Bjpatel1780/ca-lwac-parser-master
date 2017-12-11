<?php

date_default_timezone_set("UTC");

class LwacExtractor
{

    /**
     * @var string
     * @description Lwac file contents
     */
    protected $contents;

    /**
     * @var null
     * @description Lwac filename
     */
    protected $filename;

    public $output = array();

    public function __construct($filename = null)
    {
        $this->filename = $filename;
        $this->contents = file_get_contents($filename);
    }
      
    public function forDuplicates()

	{  
         preg_match('/court.+centre:.?\n(.+$)/im', $this->contents, $matches);

        $court_name_raw = (isset($matches[1])) ? preg_replace('/(\s)+/', ' ', trim($matches[1])) : '';

         $rows= array('CaseReferenceNumber'=>$this->getCaseReferenceNumber(),'LWACNumber'=>$this->getLWACNumber(),'HearingDate'=>$this->getHearingDate(),'Court'=>$court_name_raw);
    
        return $rows;
	}
 
    public function toCSV()
    {
        static $count = 0;
        $count++;
        $rows = [];

        $new_file_name = $this->getNewFilename($this->filename);
        $court_center = $this->getCourtCenter();
        $hearing_date = date('d/m/Y',strtotime($this->getHearingDate()));

        if (time() > strtotime($this->getHearingDate())){
            $hearing_date_past = true;
		$output[$count] = array($new_file_name, $court_center, $hearing_date, $hearing_date_past);
	        var_dump($output);
        } else {
            $hearing_date_past = false;
        }

        $output[$count] = array($new_file_name, $court_center, $hearing_date, $hearing_date_past);
        //var_dump($output);

        //foreach ($this->getWitnesses() as $witness) {

        if (!$hearing_date_past) {
	
		$rows= array('Case name'=> $this->getCaseName(),'Case reference number'=> $this->getCaseReferenceNumber(),'Operational reference'=>$this->getOperationalReference(),'Court'=>$court_center,'Hearing start date'=>$hearing_date,'Hearing end date'=>$hearing_date,'Time of Hearing'=>'','LWAC'=>$new_file_name);

//Get number of Witnesses and their details into an associative array.
 $rows1 = array();
 $rows1= $this->getWitnesses();
   foreach($rows1 as $key=>$value)
                {
                        if($value===NULL)
                        {
                                $array[$key]="";
                        }
                }
//echo "****rows1******";
//var_dump($rows1);

 $NoOfWit=sizeof($rows1);
 $witness = array();
$witness_All=array();
for ($i=0;$i<sizeof($rows1);$i++)
{
  $n=$i+1;
  $witness='witness'.$n;
 $witness=array('NoOfWitnesses'=>$NoOfWit,'WitnessName'.$n=>$rows1[$i]["name"],'DOB'.$n=>$rows1[$i]["dob"],'Flags'.$n=>$rows1[$i]["flags"],'Address'.$n=>$rows1[$i]["address"],'mobile'.$n=>$rows1[$i]["mobile"],'home'.$n=>$rows1[$i]["home"],'work'.$n=>$rows1[$i]["work"]);
$witness_All=($witness_All + $witness);
}

//echo "************* witness_All **********";
//var_dump($witness_All);
#$rows=$rows+$witness_All;

//echo "******* rows ******";
//var_dump($rows);

	
        }

        return $rows;
        #$serialized = array_map('serialize',$rows);
        #$unique = array_unique($serialized);
        #return array_intersect_key($rows,$unique);
        #return array_unique($rows, SORT_REGULAR);
        #return array_unique(array_map("serialize",$rows));
        #return array_unique($rows[$this->getCaseReferenceNumber()]);
    }

    public function getNewFilename($file_name){

        if(strpos($file_name, '/') !== false) {
            $delimiter = '/';
        } elseif (strpos($file_name, '\\') !== false) {
            $delimiter = '\\';
        }

        $explode_file_name = explode($delimiter,$file_name);
        $new_file_name = str_replace("txt", "pdf", end($explode_file_name));

        return $new_file_name;
    }

    public function toLog()
    {
        $output = '';
        $output .= '---------------------BEGIN DOCUMENT------------------' . PHP_EOL;
        $output .= $this->filename . PHP_EOL;
        $output .= PHP_EOL;
        $output .= 'LWAC Number: ' . $this->getLWACNumber() . PHP_EOL;
        $output .= 'Case Reference Number: ' . $this->getCaseReferenceNumber() . PHP_EOL;
        $output .= 'Previous Case Ref:' . $this->getPreviousCaseReferenceNumber() . PHP_EOL;
        $output .= 'Operational Ref:' . $this->getOperationalReference() . PHP_EOL;
        $output .= 'Contact Name: ' . $this->getContactName() . PHP_EOL;
        $output .= 'Telephone: ' . $this->getTelephone() . PHP_EOL;
        $output .= 'Case Name: ' . $this->getCaseName() . PHP_EOL;
        $output .= 'Court Center: ' . $this->getCourtCenter() . PHP_EOL;
        $output .= 'Hearing Date: ' . $this->getHearingDate() . PHP_EOL;
        $output .= 'Hearing Time: ' . $this->getHearingTime() . PHP_EOL;
        $output .= 'Witnesses: ' . PHP_EOL;

        foreach ($this->getWitnesses() as $witness) {

            $witness_string = var_export($witness, true);
            $this->output .= PHP_EOL . $witness_string;
        }

        $output .= PHP_EOL;
        $output .= '---------------------END DOCUMENT--------------------' . PHP_EOL;
        $output .= PHP_EOL;
        $output .= PHP_EOL;

        $this->output['log'] = $output;
    }

    private function getWitnesses()
    {
        $witnesses = [];
        $text = explode('Please warn the following witnesses to attend Court for this hearing', $this->contents);

        preg_match_all('/\d+\n.?witness name and address:(.+(?=\n\d\n|$|\.\n))/isU', $text[1], $matches);

        if (!empty($matches[0])) {

            if (count($matches) == 2) {
                if (is_array($matches[1])) {
                    foreach ($matches[1] as $m) {
                        $witnesses[] = $this->getSingleWitness($m);
                    }
                }
            }
        } else {

            echo 'No witnesses exist for this report '. $this->filename .'<br />';
        }

        return $witnesses;
    }


    private function getSingleWitness($text)
    {
        $witness = [
            'dob' => '',
            'flags' => '',
            'name' => '',
            'address' => '',
            'mobile' => '',
            'home' => '',
            'work' => ''
        ];

        $address=[];

        $rows = explode(PHP_EOL, $text);
        foreach ($rows as $key => $row) {
            if (stristr($row, 'status') || $key == 0) {
                preg_match_all('/\b[vpcfxlsi]\b/i', $row, $matches);
                if (!empty($matches[0])) {
                    $witness['flags'] = implode(', ', $matches[0]);
                }
                $witness['name'] = $row;
            } elseif ($key == 0 && !stristr('status', $row) || $key == 1) {
                $witness['name'] = $row;
            } elseif (stristr($row, 'mobile')) {
                $witness['mobile'] = preg_replace('/[^0-9]/is', '', $row);
            } elseif (stristr($row, 'home')) {
                $witness['home'] = preg_replace('/[^0-9]/is', '', $row);
            } elseif (stristr($row, 'work')) {
                $witness['work'] = preg_replace('/[^0-9]/is', '', $row);
            } else {
                if (!empty($row)) {
                    $address[] = $row;
                }
            }
        }
        //extract dob
        preg_match('/\d{2}[\/|-]\d{2}[\/|-]\d{2,4}/is', $witness['name'], $matches);
        if (isset($matches[0])) {
            $witness['dob'] = $matches[0];
        }
        //cleanup name
        $witness['name'] = preg_replace('/\(?dob?:?.?.?[\d|\/|-|]+\)?/is', '', $witness['name']);
        $witness['address'] = implode(', ', $address);
        return $witness;
    }


    private function getCaseName()
    {
        preg_match('/case.\nname:.?\n(.+$)/im', $this->contents, $matches);
        return (isset($matches[1])) ? preg_replace('/(\s)+/', ' ', trim($matches[1])) : '';
    }


    private function getHearingDate()
    {
        preg_match('/Date.+of.+hearing:.?\n(.+$)/im', $this->contents, $matches);

        $hearing_date_raw = $matches[1];

        $objHearingDate = new HearingDate();

        $standard_hearing_date = $objHearingDate->getHearingDateFromRawDate($hearing_date_raw);

        return $standard_hearing_date;
    }


    private function getHearingTime()
    {
        preg_match('/Time.+of.+hearing:.?\n(.+$)/im', $this->contents, $matches);
        return (isset($matches[1])) ? preg_replace('/(\s)+/', ' ', trim($matches[1])) : '';
    }


    private function getLWACNumber()
    {
        preg_match('/lwac.+number:.?\n(.+$)/im', $this->contents, $matches);
        return (isset($matches[1])) ? preg_replace('/(\s)+/', ' ', trim($matches[1])) : '';
    }


    private function getCourtCenter()
    {
        preg_match('/court.+centre:.?\n(.+$)/im', $this->contents, $matches);

        $court_name_raw = (isset($matches[1])) ? preg_replace('/(\s)+/', ' ', trim($matches[1])) : '';

        $objCourtNames = new CourtNames();

        $actual_court_name = $objCourtNames->getCourtNameFromRawData($court_name_raw);

        return $actual_court_name;
    }


    private function getCaseReferenceNumber()
    {
        preg_match('/case.+reference.+no:.?\n(.+$)/im', $this->contents, $matches);
        return (isset($matches[1])) ? preg_replace('/(\s)+/', ' ', trim($matches[1])) : '';
    }


    private function getOperationalReference()
    {
        preg_match('/operational.+reference:.?\n(.+$)/im', $this->contents, $matches);
        return (isset($matches[1])) ? preg_replace('/(\s)+/', ' ', trim($matches[1])) : '';
    }


    private function getPreviousCaseReferenceNumber()
    {
        preg_match('/previous.+applicable.?\n(.+$)/im', $this->contents, $matches);
        return (isset($matches[1])) ? preg_replace('/(\s)+/', ' ', trim($matches[1])) : '';
    }


    private function getTelephone()
    {
        preg_match('/telephone:.?\n(.+$)/im', $this->contents, $matches);
        return (isset($matches[1])) ? preg_replace('/(\s)+/', ' ', trim($matches[1])) : '';
    }


    private function getContactName()
    {
        preg_match('/Contact.+name:.?\n(.+$)/im', $this->contents, $matches);
        return (isset($matches[1])) ? preg_replace('/(\s)+/', ' ', trim($matches[1])) : '';
    }
}
