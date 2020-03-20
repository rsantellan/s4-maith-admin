<?php

namespace Maith\Common\AdminBundle\Services;

use Monolog\Logger;
use Symfony\Component\Finder\Finder;

/**
 * Description of BcuCotizadorService
 *
 * @author Rodrigo Santellan
 */
class StaticFilesService 
{
	protected $logger;

	private $documentRoot;
  
    private $filesRoot = 'galleries';

    /**
     * StaticFilesService constructor.
     * @param Logger $logger
     * @param $documentRoot
     * @param $filesRoot
     */
	public function __construct(Logger $logger, $documentRoot, $filesRoot)
	{
		$this->logger = $logger;
		$this->documentRoot = $documentRoot;
		$this->filesRoot = $filesRoot;
		$this->logger->addDebug('Starting Static files Service');
	}

    /**
     * @return mixed
     */
	public function getDocumentRoot() {
      return $this->documentRoot;
    }

    /**
     * @param $documentRoot
     */
    public function setDocumentRoot($documentRoot) {
      $this->documentRoot = $documentRoot;
    }

    /**
     * @return string
     */
    public function getFilesRoot() {
      return $this->filesRoot;
    }

    /**
     * @param $filesRoot
     */
    public function setFilesRoot($filesRoot) {
      $this->filesRoot = $filesRoot;
    }

    /**
     * @return string
     */
    public function getFilesFullPath()
    {
      return $this->getDocumentRoot().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'web'. DIRECTORY_SEPARATOR.$this->getFilesRoot();
    }

    /**
     * @return array
     */
    public function getAllFilesWithData()
    {
      $targetDir = $this->getFilesFullPath();
      $this->logger->addDebug("The files full path is: ", [$targetDir]);
      if(!is_dir($targetDir))
      {
        mkdir($targetDir);
      }
      $finder = new Finder();
      $finder->in($targetDir)->directories()->sortByName();
      $directories = array();
      foreach($finder->getIterator() as $directory)
      {
      	$this->logger->addDebug("directory is: ",[$directory]);
        $directories[$directory->getRelativePathname()] = array();
        $fileFinder = new Finder();
        $fileFinder->in($directory->getPathname())->files()->sortByChangedTime();
        foreach($fileFinder->getIterator() as $file)
        {
          $directories[$directory->getRelativePathname()][] = $file;
        }
      }
      return $directories;
    }

    /**
     * @param $folderName
     * @return bool
     */
    public function createFolder($folderName)
    {
    	$targetDir = $this->getFilesFullPath().DIRECTORY_SEPARATOR.$folderName;
    	$this->logger->addDebug("The new folder path is: ", [$targetDir]);
    	$response = true;
    	if(!is_dir($targetDir))
	    {
	        $response = mkdir($targetDir);
	    }
	    return $response;
    }

    /**
     * @param $folderName
     * @return string|null
     */
    public function checkFolder($folderName){
    	if($this->createFolder($folderName)){
    		return $this->getFilesFullPath().DIRECTORY_SEPARATOR.$folderName;
    	}
    	return NULL;
    }

    /**
     * @param $folder
     * @return array
     */
    public function getFiles($folder)
    {
      $targetDir = $this->getFilesFullPath().DIRECTORY_SEPARATOR.$folder;
      $files = array();
      if(!is_dir($targetDir))
      {
        return $files;
      }
      $fileFinder = new Finder();
      $fileFinder->in($targetDir)->files()->sortByChangedTime();
      
      foreach($fileFinder->getIterator() as $file)
      {
        $files[] = $file;
      }
      return $files;
    }

    /**
     * @param $folder
     * @param $filename
     * @return mixed|null
     */
    public function getFile($folder, $filename)
    {
      $targetDir = $this->getFilesFullPath().DIRECTORY_SEPARATOR.$folder;
      if(!is_dir($targetDir))
      {
        return null;
      }
      $files = array();
      $fileFinder = new Finder();
      $fileFinder->in($targetDir)->files()->name($filename);
      foreach($fileFinder->getIterator() as $file)
      {
        $files[] = $file;
      }
      return array_pop($files);
    }

    /**
     * @param $folder
     * @param $filename
     * @return bool|null
     */
    public function removeFile($folder, $filename)
    {
      $targetDir = $this->getFilesFullPath().DIRECTORY_SEPARATOR.$folder;
      if(!is_dir($targetDir))
      {
        return null;
      }
      $files = array();
      $fileFinder = new Finder();
      $fileFinder->in($targetDir)->files()->name($filename);
      foreach($fileFinder->getIterator() as $file)
      {
        $files[] = $file;
      }
      $spFile = array_pop($files);
      return @unlink($spFile->getPathName());
    }

    /**
     * @param $filename
     * @return array
     */
    public function retrieveExtensionAndMiMeType($filename)
    {
        /* Figure out the MIME type (if not specified) */
        $known_mime_types = array(
            "pdf" => "application/pdf",
            "txt" => "text/plain",
            "html" => "text/html",
            "htm" => "text/html",
            "exe" => "application/octet-stream",
            "zip" => "application/zip",
            "doc" => "application/msword",
            "xls" => "application/vnd.ms-excel",
            "ppt" => "application/vnd.ms-powerpoint",
            "gif" => "image/gif",
            "png" => "image/png",
            "jpeg" => "image/jpg",
            "jpg" => "image/jpg",
            "php" => "text/plain"
        );


        $file_extension = strtolower(substr(strrchr($filename, "."), 1));
        if (array_key_exists($file_extension, $known_mime_types)) {
            $mime_type = $known_mime_types[$file_extension];
        } else {
            $mime_type = "application/force-download";
        }

        return array('extension' => $file_extension, 'mime' => $mime_type);
    }

}