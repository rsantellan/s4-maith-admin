<?php
/**
 * @copyright Copyright © 2019 Geocom. All rights reserved.
 * @author    Rodrigo Santellan <rsantellan@geocom.com.uy>
 */

namespace Maith\Common\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;

class CacheCommandController extends AbstractController
{

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * @return JsonResponse
     */
    public function commandCacheClearAction()
    {
        $cache_dir = $this->parameterBag->get('kernel.cache_dir');
        $finder = new Finder();
        $filesystem = new Filesystem();
        $filesystem->remove($finder->in($cache_dir));
        $response = new JsonResponse();
        $response->setData(['result' => true, 'message' => 'Cache actualizado con exito']);
        return $response;
    }

    /**
     * @return JsonResponse
     */
    public function commandTranslationClearAction()
    {
        $cache_dir = $this->parameterBag->get('kernel.cache_dir');
        $finder = new Finder();
        $filesystem = new Filesystem();
        $filesystem->remove($finder->in($cache_dir)->name('translations'));
        $response = new JsonResponse();
        $response->setData(['result' => true, 'message' => 'Textos actualizados con exito']);
        return $response;
    }
}