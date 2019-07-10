<?php

namespace Maith\Common\AdminBundle\Twig;

use Doctrine\ORM\EntityManager;

/**
 * Description of mImageExtension
 *
 * @author rodrigo
 */
class mAvatarExtension extends \Twig_Extension
{
  
  private $em;
  private $conn;
  private $rootDir;
    /**
     * @var \Maith\Common\AdminBundle\Services\AlbumCacheService
     */
  private $cache;
  
  function __construct(EntityManager $em, $rootDir, $cache) {
    $this->em = $em;
    $this->conn = $em->getConnection();
    $this->rootDir = $rootDir;
    $this->cache = $cache;
  }

  
  public function getFilters() {
    return array(
        new \Twig_SimpleFilter('mAvatar', array($this, 'mAvatarFilter'))
    );
  }
  
  public function mAvatarFilter($objectId, $objectClass, $albumName = "Default", $position = 1, $cache = False)
  {
    $cache_key = null;
    if($cache){
      $cache_key = \Maith\Common\AdminBundle\Entity\mAlbum::ALBUM_AVATAR_CACHE_KEY.md5($albumName.$objectClass.$objectId);
      $cacheData = $this->cache->getByKey($cache_key);
      if ($cacheData) {
          return $cacheData;
      }
    }
    $query = $this->em->createQuery("select f from MaithCommonAdminBundle:mFile f join f.album a where a.object_id = :id and a.object_class = :object_class and a.name = :name order by f.orden ASC");
    $query->setParameters(array('id' => $objectId, 'object_class' => $objectClass, 'name' => $albumName));
    $position --;
    $query->setFirstResult($position);
    $query->setMaxResults(1);
    if($cache)
    {
        $query->useResultCache(true, 360);
    }
    $file = $query->getOneOrNullResult();
    if($file !== null)
    {
      if($cache){
          $this->cache->saveByKey($cache_key, $file->getFullPath());
      }
      return $file->getFullPath();
    }
    $firstPath = $this->rootDir.'/public/images/noimage.png';
    if(is_file($firstPath))
    {
      return $firstPath;
    }
    return $this->rootDir."/public/bundles/maithcommonimage/images/noimage.png";
  }

  public function getName() {
    return "maith_m_avatar";
  }
}

