<?php

namespace Repositories;

use Alchemy\Phrasea\Application;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * StoryWZRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StoryWZRepository extends EntityRepository
{

    public function findByUser(Application $app, \User_Adapter $user, $sort)
    {
        $dql = 'SELECT s FROM Entities\StoryWZ s WHERE s.usr_id = :usr_id ';

        if ($sort == 'date') {
            $dql .= ' ORDER BY s.created DESC';
        }

        $query = $this->_em->createQuery($dql);
        $query->setParameters(array('usr_id' => $user->get_id()));

        $stories = $query->getResult();

        foreach ($stories as $key => $story) {
            try {
                $story->getRecord($app)->get_title();
            } catch (NotFoundHttpException $e) {
                $this->getEntityManager()->remove($story);
                unset($stories[$key]);
            }
        }

        $this->getEntityManager()->flush();

        if ($sort == 'name') {
            $sortedStories = array();
            foreach ($stories as $story) {
                $sortedStories[] = $story->getRecord($app)->get_title();
            }

            uasort($sortedStories, function($a, $b) {
                    if ($a == $b) {
                        return 0;
                    }

                    return ($a < $b) ? -1 : 1;
                });

            foreach ($sortedStories as $idStory => $titleStory) {
                $sortedStories[$idStory] = $stories[$idStory];
            }
        }

        return $stories;
    }

    public function findByUserAndId(Application $app, \User_Adapter $user, $id)
    {
        $story = $this->find($id);

        if ($story) {
            try {
                $story->getRecord($app)->get_title();
            } catch (NotFoundHttpException $e) {
                $this->getEntityManager()->remove($story);
                throw new NotFoundHttpException('Story not found');
            }

            if ($story->getUser($app)->get_id() !== $user->get_id()) {
                throw new AccessDeniedHttpException('You have not access to ths story');
            }
        } else {
            throw new NotFoundHttpException('Story not found');
        }

        return $story;
    }

    public function findUserStory(Application $app, \User_Adapter $user, \record_adapter $Story)
    {
        $story = $this->findOneBy(
            array(
                'usr_id'    => $user->get_id(),
                'sbas_id'   => $Story->get_sbas_id(),
                'record_id' => $Story->get_record_id(),
            )
        );

        if ($story) {
            try {
                $story->getRecord($app);
            } catch (NotFoundHttpException $e) {
                $this->getEntityManager()->remove($story);
                $this->getEntityManager()->flush();
                $story = null;
            }
        }

        return $story;
    }

    public function findByRecord(Application $app, \record_adapter $Story)
    {
        $dql = 'SELECT s FROM Entities\StoryWZ s WHERE s.sbas_id = :sbas_id
                AND s.record_id = :record_id';

        $query = $this->_em->createQuery($dql);
        $query->setParameters(array(
            'sbas_id' => $Story->get_sbas_id(),
            'record_id' => $Story->get_record_id(),
        ));

        $stories = $query->getResult();

        foreach ($stories as $key => $story) {
            try {
                $story->getRecord($app);
            } catch (NotFoundHttpException $e) {
                $this->getEntityManager()->remove($story);
                $this->getEntityManager()->flush();
                unset($stories[$key]);
            }
        }

        return $stories;
    }

    public function findByDatabox(Application $app, \databox $databox)
    {
        $dql = 'SELECT s FROM Entities\StoryWZ s WHERE s.sbas_id = :sbas_id';

        $query = $this->_em->createQuery($dql);
        $query->setParameters(array(
            'sbas_id' => $databox->get_sbas_id(),
        ));

        $stories = $query->getResult();

        foreach ($stories as $key => $story) {
            try {
                $story->getRecord($app);
            } catch (NotFoundHttpException $e) {
                $this->getEntityManager()->remove($story);
                $this->getEntityManager()->flush();
                unset($stories[$key]);
            }
        }

        return $stories;
    }
}
