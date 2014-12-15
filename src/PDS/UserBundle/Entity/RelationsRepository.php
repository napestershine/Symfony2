<?php

namespace PDS\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Driver\PDOConnection;

/**
 * RelationsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RelationsRepository extends EntityRepository
{
    /**
     * Récupère les demandes d'amis non répondues
     * 
     * @param PDOConnection $con    Connexion à la BDD
     * @param integer       $idUser Identifiant de l'utilisateur à utiliser
     * 
     * @return array resultats
     */
    public function getUntreatedRelationsCount($con, $idUser, $select = '*')
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('COUNT(r)')
           ->where('r.user1Id = :id')
             ->setParameter('id', $idUser)
           ->andWhere('r.areFriends = false')
           ->andWhere('r.requestSended = true')
           ->andWhere('r.isPending = true')
           ->andWhere('r.sendedBy = r.user2Id');
        
        return $qb->getQuery()->getSingleScalarResult();
    }
    /**
     * Récupère les demandes d'amis non répondues
     * 
     * @param PDOConnection $con    Connexion à la BDD
     * @param integer       $idUser Identifiant de l'utilisateur à utiliser
     * 
     * @return array resultats
     */
    public function getUntreatedRelationsJoinUsers($con, $idUser, $select = '*')
    {
        return $con->query(
            sprintf("
                    SELECT %s
                    FROM Relations r
                    INNER JOIN Users u ON u.id = r.user2_id
                    WHERE r.are_friends = false
                    AND r.user1_id = %d
                    AND r.sended_by = r.user2_id
                    AND r.request_sended = true
                    AND r.is_pending = true
                ",
                $select,
                (integer) $idUser
            )
        )->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getAllPendingRelationsJoinUsers($con, $idUser)
    {
        return $con->query(
            sprintf(
                "SELECT * FROM (
                    SELECT 
                      u.*,
                      r.id as r_id,
                      r.user1_id,
                      r.user2_id,
                      r.sended_by,
                      r.are_friends,
                      r.request_sended,
                      r.date as date_relation,
                      r.is_pending
                    FROM Relations r
                    INNER JOIN Users u ON u.id = r.user2_id
                    WHERE r.are_friends = false
                    AND r.user1_id = %d
                    AND r.sended_by = r.user2_id
                    AND r.request_sended = TRUE 
                    AND r.is_pending = TRUE
                    
                    UNION
                    
                    SELECT 
                      u.*,
                      r.id as r_id,
                      r.user1_id,
                      r.user2_id,
                      r.sended_by,
                      r.are_friends,
                      r.request_sended,
                      r.date as date_relation,
                      r.is_pending
                    FROM Relations r
                    INNER JOIN Users u ON u.id = r.user2_id
                    WHERE r.are_friends = false
                    AND r.user1_id = %d
                    AND r.sended_by = r.user1_id
                    AND r.request_sended = TRUE 
                    AND r.is_pending = TRUE
                ) req
                ORDER BY req.date_relation DESC",
                $idUser,
                $idUser
            )
        )->fetchAll(\PDO::FETCH_ASSOC);
    }
}
