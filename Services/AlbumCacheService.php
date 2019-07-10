<?php


namespace Maith\Common\AdminBundle\Services;

use Maith\Common\AdminBundle\Entity\mAlbum;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class AlbumCacheService
{

    /**
     * @var AdapterInterface
     */
    private $cache;

    /**
     * AlbumCacheService constructor.
     */
    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }


    /**
     * @param mAlbum|null $album
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function deleteAlbumAvatar(?mAlbum $album)
    {
        if (!$album) {
            return true;
        }
        $cache_key = mAlbum::ALBUM_AVATAR_CACHE_KEY . $album->retrieveAvatarCacheKey();
        return $this->doDeleteKey($cache_key);
    }

    /**
     * @param $cacheKey
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function doDeleteKey($cacheKey)
    {
        $item = $this->cache->getItem($cacheKey);
        if($item->isHit()){
            $this->cache->deleteItem($cacheKey);
        }
        return true;
    }

    /**
     * @param $cacheKey
     * @return string|null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getByKey($cacheKey)
    {
        $item = $this->cache->getItem($cacheKey);
        if($item->isHit()){
            return $item->get();
        }
        return null;
    }

    /**
     * @param $cacheKey
     * @param $object
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function saveByKey($cacheKey, $object)
    {
        /** @var \Symfony\Component\Cache\Adapter\CacheItem $item */
        $item = $this->cache->getItem($cacheKey);
        $item->set($object);
        return $this->cache->save($item);
    }
}