<?php
/**
 * This task will replace all include_once calls through the included code.
 * 
 * Turns a multifile package into a single file for distribution.
 * 
 * This will package iCalCreator into a single file
 * 
 *		php package.php 
 */
$packager = new Packager();
$packager->execute();

class Packager
{
	protected $inclusionFilename = null;
	protected $inclusionCode = null;
	protected $outfile = null;
	protected $basepath = null;
	
	public function __construct( $file = null, $outfile = 'iCalcreator.class.php' )
	{
		if( is_null($file) )
		{
			$file = realpath(__DIR__.'/../').DIRECTORY_SEPARATOR.'iCalcreator.php';
		}
		$this->inclusionFilename = $file;		
		$this->basepath = dirname($file).DIRECTORY_SEPARATOR;
		$this->inclusionCode = new CodeFile($file);
		$this->outfile = $this->basepath.$outfile;
	}
	
	public function execute()
	{
		$matches = $this->matchInclusions();
		foreach( $matches as $match )
		{
			$this->log( sprintf('Will replace %s by %s',$match['include'], $match['file']));
			$code = new CodeFile($this->basepath.$match['file']);
			$code->removeOpeningTag();
			$code->removeClosingTag();
			$this->inclusionCode->replace($match['include'], $code->getContent(), 1);
		}
		
		$this->inclusionCode->write($this->outfile);
	}
	
	protected function matchInclusions()
	{
		$pattern = '/(?<include>include_once\(\'(?<file>.*?)\'\);)/';
		preg_match_all( $pattern, $this->inclusionCode->getContent(), $matches, PREG_SET_ORDER);
		
		return $matches;
	}	
	
	protected function log( $message, $level = 'info' )
	{
		echo date('Ymd His').' '.  strtoupper($level).': '.$message."\n";
	}
}

/**
 * Representation of a file of phpcode.
 * 
 * The file can be mostly treated as a string. If it is to be included within
 * another file, do not forget to remove the opening and closing php tags.
 */
class CodeFile
{
	protected $filename = null;
	protected $contents = null;
	
	public function __construct( $filename )
	{
		$this->filename = $filename;
		$this->contents = trim(file_get_contents($filename));
	}
	
	public function startswith( $needle )
	{
		$length = strlen( $needle );				
		return substr($this->contents, 0, $length ) == $needle;
	}
	
	public function endswith( $needle )
	{
		$length = strlen( $needle ) + 1;		
		return substr($this->contents, count($this->contents) - $length) == $needle;
	}	
	
	public function replace( $search, $replace, $count = null )
	{
		$this->contents = str_replace( $search, $replace, $this->contents, $count);
	}
	
	public function removeOpeningTag()
	{
		if( $this->startswith('<?php') )
		{
			$this->contents = substr( $this->contents, 5 );
		}
	}
	
	public function removeClosingTag()
	{
		if( $this->endswith('?>') )
		{
			$this->contents = substr( $this->contents, 0, count($this->contents) - 3 );		
		}	
	}
	
	public function getContent()
	{
		return $this->contents;
	}
	
	public function write( $filename )
	{
		file_put_contents($filename, $this->contents);
	}
	
	public function __toString()
	{
		return $this->getContent();
	}
}
