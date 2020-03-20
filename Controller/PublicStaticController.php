<?php
/**
 * @copyright Copyright © 2020 Geocom. All rights reserved.
 * @author    Rodrigo Santellan <rsantellan@geocom.com.uy>
 */

namespace Maith\Common\AdminBundle\Controller;

use Maith\Common\AdminBundle\Services\StaticFilesService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PublicStaticController extends Controller
{

    public function downloadFileAction(StaticFilesService $staticFilesService, $url)
    {
        $data = unserialize(base64_decode($url));
        $folder = $data['folder'];
        $file = $data['file'];
        $fileObject = $staticFilesService->getFile($folder, $file);
        $content = $fileObject-> getContents();
        $response = new Response();
        $fileMetadata = $staticFilesService->retrieveExtensionAndMiMeType($fileObject->getFilename());
        $response->headers->set('Content-Type', $fileMetadata['mime']);
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$fileObject->getFilename());
        $response->setContent($content);
        return $response;
    }
}