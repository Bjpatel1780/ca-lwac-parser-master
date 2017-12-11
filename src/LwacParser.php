<?php

class LwacParser
{
    const CMD_CONVERT = 'soffice --headless --convert-to {format} --outdir "{outdir}" "{filename}"';
    const EXTENSION = 'docx';
    const OUTPUT_FILENAME = 'output.tsv';

    /**
     * @var string
     * @description Run code according to environment it is running on
     * @options local, clone, prod
     */
    public $environment = '';

    protected $path;

    protected $tmpdir;

    protected $dupfaildir;



    public function __construct($path)
    {
        $this->path = $path;
	$this->dupfaildir = __DIR__ . DIRECTORY_SEPARATOR . 'duplicatesFailures';
        switch ($this->environment == 'local'){

            case "local":
                $this->tmpdir = __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . md5(rand(1,time()));
             break;

            case "clone":
                $this->tmpdir = __DIR__ . DIRECTORY_SEPARATOR . 'txtdirhtml';
                break;

            default:
                $this->tmpdir = __DIR__ . DIRECTORY_SEPARATOR . 'txtdirhtml';
                break;
        }
    }


    public function run()
    {
        echo 'Running...' . PHP_EOL;
        echo 'Using temporary directory: ' . $this->tmpdir . PHP_EOL;

        if (!is_dir($this->tmpdir)) {

            mkdir($this->tmpdir, 700);
        }

        $output = [];

        echo 'Searching path: ' . $this->path . ' for ' . self::EXTENSION .'files' . PHP_EOL;
        $files = $this->getFilesNames($this->path, self::EXTENSION);
        echo 'Found ' . count($files) . ' files' . PHP_EOL;

        foreach ($files as $file) {

            $this->convertFile($file, 'html');
        }

        echo 'Searching path: ' . $this->tmpdir . ' for HTML files' .PHP_EOL;
        $files = $this->getFilesNames($this->tmpdir, 'html');
        echo 'Found ' . count($files) . ' files' . PHP_EOL;

        foreach ($files as $file) {

            echo ' wrote ' . $this->stripHTMLTitle($file) . ' bytes';
            $this->convertFile($file, 'txt:Text');
        }

        echo 'Searching path: ' . $this->tmpdir . ' for TXT files' .PHP_EOL;

        $files = $this->getFilesNames($this->tmpdir, 'txt');
        echo 'Found ' . count($files) . ' files' . PHP_EOL;

       // $rows = [];

        foreach ($files as $file) {

            $extractor = new LwacExtractor($file);

            // Show output of File
            //$extractor->toLog();

            $rows = $extractor->toCSV();
	   // print_r($rows);
	    $output_file = str_replace("pdf","tsv",$rows["LWAC"]);
	    $output_directory = __DIR__ . DIRECTORY_SEPARATOR . "../" . "outputs/";

        if (!is_dir($output_directory)) {

            mkdir($output_directory, 700);
        }

        $keys[] = array_keys($rows);
        $values[]= array_values($rows);

        foreach($values as $v) {

                    foreach($v as $p) {

                        $output = implode("\t",$keys[0]) . PHP_EOL;
                        $output .= implode("\t", $v) . PHP_EOL;

		      file_put_contents($output_directory. $output_file  ,  $output );
	       }
            }
        }
    }



    public function emaildupfail()
    {
        $DuplicateFile="Duplicate.csv";
	$header=array("CaseReferenceNumber","LWACNumber","HearingDate","Court");
        $pathToGenerate = __DIR__ . DIRECTORY_SEPARATOR . $DuplicateFile;
        echo $pathToGenerate;
	$fail=array("FailureRecords");
	$duplicate=array("DuplicateRecords");
	$Success=array("SuccessRecords");
	$Other=array("OtherRecords");
        $createFile = fopen($pathToGenerate,"w+");
       // fputcsv($createFile,$header);

	$files = $this->getFilesNames($this->dupfaildir, 'txt');
        foreach ($files as $file)
{
	 
	if (strpos($file,'FailureRecords') !== false) {
		echo "FailureRecords";	
     		fputcsv($createFile,$fail);
		}

	elseif (strpos($file,'DuplicateRecords') !== false) {
		echo "DuplicateRecords";
     		fputcsv($createFile,$duplicate);
		}
	elseif (strpos($file,'SucessRecords') !== false) {
                echo "SuccessRecords";
                fputcsv($createFile,$Success);
                }
	elseif (strpos($file,'OtherRecords') !== false) {
                echo "OtherRecords";
                fputcsv($createFile,$Other);
                }



	$files = file($file);
        $rows = array();
        $rows_all=array();
	
	fputcsv($createFile,$header);
	 foreach ($files as $file)

	 {
                $file=trim($file,$character_mask = " \t\n\r\0\x0B");
        	$file=$this->tmpdir . DIRECTORY_SEPARATOR . $file ;
		echo $file;
	        $extractor = new LwacExtractor($file);
        	$rows=$extractor->forDuplicates();
        	fputcsv($createFile, $rows);

	}

}	
    } 	

    public function convertFile($file, $format)
    {
        $replace = ['{format}' => $format, '{outdir}' => $this->tmpdir, '{filename}' => $file];
        $command = str_replace(array_keys($replace), array_values($replace), self::CMD_CONVERT);
        system($command);
    }

    private function stripHTMLTitle($filename)
    {
        $contents = file_get_contents($filename);
        $contents = str_replace('title="header"', '', $contents);
        return file_put_contents($filename, $contents);
    }

    private function getFilesNames($path, $extension)
    {
        $iterator = new DirectoryIterator($path);
        $files = [];

        foreach ($iterator as $file) {

            if ($file->getExtension() == $extension) {
                // getPathname replaced as it mixes back and forward slashes in the path
                //$files[] = $file->getPathname();
                $files[] = $file->getRealPath();

            }
        }
        return $files;
    }
}
