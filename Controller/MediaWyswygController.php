<?php

namespace Maith\Common\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Maith\Common\AdminBundle\Services\StaticFilesService;
use Maith\Common\AdminBundle\Services\StaticFileUrlGeneratorService;
use Maith\Common\ImageBundle\Model\ImageUrlGeneratorService;

class MediaWyswygController extends AbstractController
{

	public function wyswygShowFilesAction(Request $request, StaticFilesService $staticFilesService)
    {
    	$ckeditor = $request->query->get('CKEditor');
    	$CKEditorFuncNum = $request->query->get('CKEditorFuncNum');
    	$lang = $request->query->get('langCode');
        return $this->render('@MaithCommonAdmin/Wyswyg/showFiles.html.twig', array(
            'folders' => $staticFilesService->getAllFilesWithData(),
            'ckeditor' => $ckeditor,
            'CKEditorFuncNum' => $CKEditorFuncNum,
            'lang' => $lang,
        ));
    }

    public function wyswygAddFolderFormAction(Request $request, StaticFilesService $staticFilesService){
    	 $form = $this->createFormBuilder()
	        ->add('name', TextType::class)
	        ->setAction($this->generateUrl('maith_admin_wyswyg_media_add_folder'))
    		  ->setMethod('POST')
	        ->getForm();
        $form->handleRequest($request);
        $response = new JsonResponse();
        $isvalid = true;
        $message = '';
        $html = '';
        $reload = false;
  	    if ($form->isSubmitted() && $form->isValid()) {
  	        // data is an array with "name", "email", and "message" keys
  	        $data = $form->getData();
  	        $reload = true;
  	        $isvalid = $staticFilesService->createFolder($data['name']);
  	    } else {
  	    	$html = $this->renderView('@MaithCommonAdmin/Wyswyg/_addFolderForm.html.twig', array(
  			            'form' => $form->createView(),
  			        ));
  	    }
  	    $response->setData(array(
  		        'isvalid' => $isvalid,
  		        'message' => $message,
  		        'html' => $html,
  		        'reload' => $reload,
  	      	));
  	    return $response;
    }

	public function uploadFormAction($name)
    {
      return $this->render('@MaithCommonAdmin/Wyswyg/upload.html.twig', array('folder' => $name));
    }    

    public function refreshFolderAction(Request $request, StaticFilesService $staticFilesService){
      $folder = $request->get('folder');
      $files = $staticFilesService->getFiles($folder);
      $response = new JsonResponse();
      $response->setData(array(
          'status' => 'OK',
          'options' => array('html' => $this->renderView('@MaithCommonAdmin/Wyswyg/folder.html.twig', array('name' => $folder, 'files' => $files)))
      ));
      return $response;
    }

    public function doUploadAction(Request $request, StaticFilesService $staticFilesService)
    {
      $folder = $request->request->get('folder');
      $targetDir = $staticFilesService->checkFolder($folder);
      if ($targetDir === NULL)
      {
        return new Response(json_encode(array("jsonrpc" => '2.0', 'error' => array('code' => 100, 'message' => "Failed to open temp directory."), 'folder' => $folder)));
      }
        
      $fileUploaded = $request->files->get('file');
      $mimeAndName = null;
      if(function_exists('finfo_open'))
      {
          $name = uniqid() . '.' . $fileUploaded->guessExtension();
      }
      else
      {
          $mimeAndName = $staticFilesService->retrieveExtensionAndMiMeType($fileUploaded->getClientOriginalName());
          $name = uniqid(). '.'.$mimeAndName['extension'];
      }
      $movedFile = $fileUploaded->move($targetDir, $name);
      if ($movedFile) 
      {
        return new Response(json_encode(array("jsonrpc" => '2.0', 'result' => null, 'id' => $folder)));
      }
      else
      {
        return new Response(json_encode(array("jsonrpc" => '2.0', 'error' => array('code' => 100, 'message' => "Failed to open temp directory."), 'id' => $gallery)));
      }
      die;
    }

    public function downloadFileAction(StaticFilesService $staticFilesService, $folder, $file)
    {
      $fileObject = $staticFilesService->getFile($folder, $file);
      $content = $fileObject-> getContents();
      $response = new Response();
      $fileMetadata = $staticFilesService->retrieveExtensionAndMiMeType($fileObject->getFilename());
      $response->headers->set('Content-Type', $fileMetadata['mime']);
      $response->headers->set('Content-Disposition', 'attachment;filename="'.$fileObject->getFilename());
      $response->setContent($content);
      return $response;
    }

    public function removeFileAction(StaticFilesService $staticFilesService, $folder, $file)
    {
      $result = $staticFilesService->removeFile($folder, $file);
      $response = new JsonResponse();
      $response->setData(array(
          'result' => $result          
      ));
      return $response;
    }

    /**
     * @param Request $request
     * @param StaticFilesService $staticFilesService
     * @param ImageUrlGeneratorService $imageUrlGeneratorService
     * @param StaticFileUrlGeneratorService $staticFileUrlGeneratorService
     * @param $folder
     * @param $file
     * @return JsonResponse
     */
    public function showFileAction(Request $request, StaticFilesService $staticFilesService, ImageUrlGeneratorService $imageUrlGeneratorService, StaticFileUrlGeneratorService $staticFileUrlGeneratorService, $folder, $file)
    {
      $fileObject =  $staticFilesService->getFile($folder, $file);
      $choices = [
        'Miniatura basico' => 't', 
        'Miniatura tomando el lado mas grande' => 'ot',
        'Respetar el ancho' => 'rce',
        'Hacer un resize maximo con los parametros dados' => 'mpr',
        'Hacer un resize centrado' => 'rcce',
        'Link para descargar' => 'link',
        'Original' => '', 
      ];
      $form = $this->createFormBuilder()
        ->add('width', IntegerType::class, array('required' => true))
        ->add('heigth', IntegerType::class, array('required' => true))
        ->add('tipo', ChoiceType::class, array('choices' => $choices, 'required' => false))
        ->setAction($this->generateUrl('maith_admin_wyswyg_show_file', ['folder' => $folder, 'file' => $file]))
        ->setMethod('POST')
        ->getForm();
      $form->handleRequest($request);
      $response = new JsonResponse();
      $isvalid = true;
      $message = '';
      $html = '';
      $url = '';
      $close = false;
      if ($form->isSubmitted() && $form->isValid()) {
          // data is an array with "name", "email", and "message" keys
          $data = $form->getData();
          $close = true;
          if ($data['tipo'] === 'link') {
              $url = $staticFileUrlGeneratorService->createLink($folder, $file);
          } else {
              $url = $imageUrlGeneratorService->mImageFilter($fileObject->getPathName(), $data['width'], $data['heigth'], $data['tipo'], false, true);
          }
      } else {
        $html = $this->renderView('@MaithCommonAdmin/Wyswyg/_useFileForm.html.twig', array(
                  'form' => $form->createView(),
                  'folder' => $folder,
                  'filename' => $file,
                  'file' => $fileObject,
              ));
      }
      $response->setData(array(
            'isvalid' => $isvalid,
            'message' => $message,
            'html' => $html,
            'close' => $close,
            'url' => $url,
          ));
      return $response;
    }

}
