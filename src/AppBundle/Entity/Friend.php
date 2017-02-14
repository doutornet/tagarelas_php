<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="amizade")
 * @ORM\HasLifecycleCallbacks()
 */
class Friend
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 *
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="userFriends")
	 * @ORM\JoinColumn(name="id_usuario", referencedColumnName="id")
	 * @var AppBundle\Entity\User
	 **/
	protected $userFriends;
	

	/**
	 *
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="friendUsers")
	 * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
	 * @var AppBundle\Entity\User
	 **/
	protected $friendUsers;

	/**
	 *
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Relationship", inversedBy="relationships")
	 * @ORM\JoinColumn(name="id_tipo_relacao", referencedColumnName="id")
	 * @var AppBundle\Entity\RelationShip
	 **/
	protected $relationships;
	
	
	/**
	 * @ORM\Column(name="data_criacao", type="datetime", nullable=true)
	 */
	protected $created;
	
	
	/**
	 * @ORM\Column(name="ultima_atualizacao", type="datetime", nullable=true)
	 */
	protected $lastUpdate;
	
	
	/**
	 * @ORM\PrePersist
	 */
	public function onPrePersist()
	{
		//using Doctrine DateTime here
		$this->created = new \DateTime('now');
		$this->lastUpdate = new \DateTime('now');
	}
	/**
	 * @ORM\PreUpdate
	 */
	public function onPreUpdate()
	{
		//using Doctrine DateTime here
		$this->lastUpdate = new \DateTime('now');
	}
	
	public function __construct()
	{
		
	}
	
	/**
	 * id
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * id
	 * @param int $id
	 * @return User
	 */
	public function setId($id){
		$this->id = $id;
		return $this;
	}


	/**
	 * createdBy
	 * @return string
	 */
	public function getCreatedBy(){
		return $this->createdBy;
	}

	/**
	 * createdBy
	 * @param string $createdBy
	 * @return Group
	 */
	public function setCreatedBy($createdBy){
		$this->createdBy = $createdBy;
		return $this;
	}

	/**
	 * created
	 * @return string
	 */
	public function getCreated(){
		return $this->created;
	}

	/**
	 * created
	 * @param string $created
	 * @return Group
	 */
	public function setCreated($created){
		$this->created = $created;
		return $this;
	}

	/**
	 * lastUpdate
	 * @return string
	 */
	public function getLastUpdate(){
		return $this->lastUpdate;
	}

	/**
	 * lastUpdate
	 * @param string $lastUpdate
	 * @return Group
	 */
	public function setLastUpdate($lastUpdate){
		$this->lastUpdate = $lastUpdate;
		return $this;
	}





	/**
	 * groupUsers
	 * @return string
	 */
	public function getGroupUsers(){
		return $this->groupUsers;
	}

	/**
	 * groupUsers
	 * @param string $groupUsers
	 * @return Group
	 */
	public function setGroupUsers($groupUsers){
		$this->groupUsers = $groupUsers;
		return $this;
	}

	/**
	 * userGroups
	 * @return string
	 */
	public function getUserGroups(){
		return $this->userGroups;
	}

	/**
	 * userGroups
	 * @param string $userGroups
	 * @return Group
	 */
	public function setUserGroups($userGroups){
		$this->userGroups = $userGroups;
		return $this;
	}


	/**
	 * userFriends
	 * @return string
	 */
	public function getUserFriends(){
		return $this->userFriends;
	}

	/**
	 * userFriends
	 * @param string $userFriends
	 * @return Friend
	 */
	public function setUserFriends($userFriends){
		$this->userFriends = $userFriends;
		return $this;
	}

	/**
	 * friendUsers
	 * @return string
	 */
	public function getFriendUsers(){
		return $this->friendUsers;
	}

	/**
	 * friendUsers
	 * @param string $friendUsers
	 * @return Friend
	 */
	public function setFriendUsers($friendUsers){
		$this->friendUsers = $friendUsers;
		return $this;
	}

	/**
	 * relationships
	 * @return string
	 */
	public function getRelationships(){
		return $this->relationships;
	}

	/**
	 * relationships
	 * @param string $relationships
	 * @return Friend
	 */
	public function setRelationships($relationships){
		$this->relationships = $relationships;
		return $this;
	}

}