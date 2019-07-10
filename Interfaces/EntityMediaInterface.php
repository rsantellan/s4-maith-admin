<?php

namespace Maith\Common\AdminBundle\Interfaces;

interface EntityMediaInterface
{

    /**
     * @return string
     */
    public function getFullClassName(): string;

    /**
     * @return array
     */
    public function retrieveAlbums();

    /**
     * @return bool
     */
    public function checkAlbumForOnlineVideo(): bool;
}