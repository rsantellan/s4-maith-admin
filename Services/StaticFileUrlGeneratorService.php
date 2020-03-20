<?php
/**
 * @copyright Copyright © 2020 Geocom. All rights reserved.
 * @author    Rodrigo Santellan <rsantellan@geocom.com.uy>
 */

namespace Maith\Common\AdminBundle\Services;


use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class StaticFileUrlGeneratorService
{
    private $router;
    
    /**
     * StaticFileUrlGeneratorService constructor.
     * @param RouterInterface $router
     */
    function __construct(RouterInterface $router) {
        $this->router = $router;
    }

    /**
     * @param string $folder
     * @param string $file
     * @return string
     */
    public function createLink(string $folder, string $file)
    {
        $url_data = array("folder" => $folder, "file"=>$file);
        $url = base64_encode(serialize($url_data));
        return $this->router->generate("maith_public_wyswyg_download_file", array('url' => $url), UrlGeneratorInterface::ABSOLUTE_URL);
    }
}