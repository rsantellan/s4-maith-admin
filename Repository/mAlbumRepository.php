<?php

namespace Maith\Common\AdminBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Maith\Common\AdminBundle\Entity\mAlbum;

/**
 * Description of ProjectRepository
 *
 * @author Rodrigo Santellan
 */
class mAlbumRepository extends ServiceEntityRepository
{

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, mAlbum::class);
    }

    /**
     * @param $object_id
     * @param $object_class
     * @return mixed
     */
    public function retrieveByObjectIdAndClass($object_id, $object_class)
    {
        $dql = "select a from MaithCommonAdminBundle:mAlbum a where a.object_id = :object_id and a.object_class = :object_class";
        return $this->getEntityManager()->createQuery($dql)->setParameters(array('object_id' => $object_id, 'object_class' => $object_class))->getResult();
    }

    /**
     * @param $object_id
     * @param $object_class
     * @param $name
     * @param bool $onlyone
     * @return mAlbum|null
     */
    public function retrieveByObjectIdClassAndAlbumName($object_id, $object_class, $name, $onlyone = true)
    {
        $params = ['object_id' => $object_id, 'object_class' => $object_class, 'name' => $name];
        $dql = "select a from MaithCommonAdminBundle:mAlbum a where a.object_id = :object_id and a.object_class = :object_class and a.name = :name";
        return $this->getEntityManager()
                ->createQuery($dql)
                ->setParameters($params)
                ->getOneOrNullResult();
    }

    /**
     * @param $object_id
     * @param $object_class
     * @param $name
     * @return mixed
     */
    public function retrieveFirstFileOfAlbum($object_id, $object_class, $name)
    {
        $query = $this->getEntityManager()->createQuery("select f from MaithCommonAdminBundle:mFile f join f.album a where a.object_id = :id and a.object_class = :object_class and a.name = :name order by f.orden ASC");
        $query->setParameters(array('id' => $object_id, 'object_class' => $object_class, 'name' => $name));
        $query->setFirstResult(0);
        $query->setMaxResults(1);
        return $query->getOneOrNullResult();
    }

}

