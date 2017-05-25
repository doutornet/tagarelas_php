<?php
namespace GroupBundle\Service;


use AppBundle\AppBundle;
use AppBundle\Entity\Group;
use AppBundle\Entity\GroupUser;
use AppBundle\Entity\Rule;
use AppBundle\Entity\User;
use AppBundle\Openfire\Ofgroupprop;
use AppBundle\Utility\AppRest;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Session\Session;



//@@TODO refazer as queries utilizando Openfire.
//@@TODO corrigir o banco de dados retirando ID (colocado indevidamente).
//@@TODO Falta salvar os usuários no grupo.


class GroupService {
	const NAME_FOUND     = 1;
	const NAME_NOT_FOUND = 2;

	
	const FIND_BY_NAME = 3;
	const FIND_BY_CREATOR = 4;

	const AVATAR = 'AVATAR';
	
	protected $em;
	private   $container;
	private   $logger;
	
	public function __construct(EntityManager $entityManager, Container $cont, Logger $log){
		$this->em = $entityManager;
		$this->container = $cont;
		$this->logger = $log;
	}
	
	public function loadAllGroups($limit = 0){
		$qb = $this->em->createQueryBuilder();
		$qb->select('g.groupname,g.description')
		   ->from('AppBundle:Group', 'g')
		   ->join('AppBundle:GroupUser', 'gu', Join::WITH,'gu.idGroup = g.id')
		   ->groupBy('g.id,g.groupName')
		   ->orderBy('g.groupName');
		
		 if (0 != $limit)
		   	$qb->setMaxResults($limit);
		
		 $myReturn =  $qb->getQuery()->getResult();
		 return $myReturn;
	}
	/**
	 * Load user groups by status and user
	 *
	 */
	
	public function loadUserGroups(){
		$request  = $this->container->get('request_stack')->getCurrentRequest();
		$limit    = intval($request->get("limit"));
		$username =  $this->container->get('session')->get('username');
		/*
		 * ----------------------------------------------------------
		 * Carrega os usuarios com o sustaus definidos em ofgroupprop
		 * ----------------------------------------------------------
		 */
		$qb = $this->em->createQueryBuilder()
					   ->select('gu.groupname as groupname ,gu.username as username, gu.isAdministrator as admin')
					   ->from('AppBundle:Ofgroupuser', 'gu')
					   ->where('gu.username = :username')
					   ->setParameter("username", $username);
		
		if (0 != $limit)
			$qb->setMaxResults($limit);
			
		$groups =  $qb->getQuery()->getResult();
	    $this->logger->info('groups->' .  var_export($groups, true));
		$myReturn = array();
			
		for ($count=0; $count < count($groups) ; ++$count){
				$group = $groups[$count];
				$this->logger->info('group->$count:' .  var_export($group, true));
				$groupname =  $group["groupname"] ;
		        $admin     =  ($group["admin"] == 1); 
		        $totalMembers =  $this->loadTotalMembers($groupname);
		        
		        if ($admin)  // Admin is ever active
		        	$status =  Rule::USER_ACTIVE;
		        else 
		        	$status    =  $this->loadStatusUser($groupname, $username);
		        
		        $avatar = $this->loadAvatar($groupname);
				
				$this->logger->info('groupname->' . $groupname.  ' admin->' . $admin .  ' status->' . $status 
						. ' avatar->' . $avatar . ' totalMembers->'. $totalMembers);
				array_push($myReturn, array('groupname'=>$groupname,
						'avatar'=>$avatar,
						'totalMembers'=>$totalMembers));
		}
		return $myReturn;
	}
	
	
	
	
	public function loadStatusUser($groupname, $username){

		/*
		 * ----------------------------------------------------------
		 * Carrega os usuarios com o sustaus definidos em ofgroupprop
		 * ----------------------------------------------------------
		 */
		$status = $this->em->createQueryBuilder()
		               ->select('gp.propvalue as propvalue')
		               ->from('AppBundle:Ofgroupprop', 'gp')
		               ->where('gp.name = :name')
		               ->andWhere('gp.groupname = :groupname')
		               ->setParameter("name", $username) 
		               ->setParameter('groupname', $groupname)
		               ->getQuery()
		               ->getOneOrNullResult();;
		
		if ($status == null || count($status) ==0)	return Rule::USER_ACTIVE;
		$this->logger->info('status->' .  var_export($status, true)); 
		return $status["propvalue"];
	}
	
	
	private function loadAvatar($groupname){
		$group = $this->em->createQueryBuilder()
				   ->select('gp.groupname,gp.name, gp.propvalue')
				   ->from('AppBundle:Ofgroupprop', 'gp')
				   ->where('gp.groupname = :groupname')
		           ->andWhere('gp.name = :name')
		           ->setParameter("groupname", $groupname)
				   ->setParameter('name', $this::AVATAR)
		           ->setMaxResults(1)
		           ->getQuery()
		           ->getOneOrNullResult();
		
		if ($group == null || count($group) == 0)	return "default.png";
		$this->logger->info('group To Avatar->' .  var_export($group, true)); 
		return $group["propvalue"]; 
	}

	
	
	private function loadTotalMembers($groupname){
		$members = $this->em->createQueryBuilder()
					->select('gu.groupname,count(gu.username) as totalMembers')
					->from('AppBundle:Ofgroupuser', 'gu')
					->where('gu.groupname = :groupname')
					->setParameter("groupname", $groupname)
					->groupBy("gu.groupname")
					->getQuery()
					->getOneOrNullResult();
		
		$this->logger->info('group To TotalMembers->' .  var_export($members , true));
		if ($members == null || count($members) == 0) return 0;
		 
		return $members ["totalMembers"]; 
	}
	
	/**
	 * @param QueryBuilder $qb
	 * @param Group        $group
	 * @param int		   $userId
	 * @param int		   $status
	 * @param array		   $myReturn
	 */
	private function loadGroupUserInformation($group,$userId,$status, &$myReturn){
		$groupsuser = $this->generateQueryGroupUser($group, $userId, $status);
		foreach($groupsuser as $gu){
			$group["userStatus"] = $gu["userStatus"];
			$myReturn[ ] = $group;
		}
		return $myReturn;
	}
	/**
	 * @param Group        $group
	 * @param int		   $user
	 * @param int		   $status
	 */
	private function generateQueryGroupUser($group,$userId,$status){
		$qb = $this->em->createQueryBuilder();
		$qb->select('gu.groupname,gu.idUser, gu.idGroup ,gu.userStatus')
			->from('AppBundle:ofGroup', 'gu')
			->where('gu.idGroup = :idGroup')
			->andwhere('gu.idUser = :idUser')
			->andWhere('gu.userStatus = :status')
			->setParameter("idGroup", $group["id"])
			->setParameter('idUser', $userId)
			->setParameter('status', $status);
		return  $qb->getQuery()->getResult();
	}
	
	public function findGroupByKey($key,$value){
		$qb = $this->em->createQueryBuilder();
		$qb->select('g.groupname,g.description')
			->from('AppBundle:Ofgroup', 'g');
		
		if (GroupService::FIND_BY_CREATOR==$key){
			$qb->where('g.createdBy = :value')
	   	       ->setParameter('value', $value );
		} else {
			$qb->where('g.groupname = :value')
			->setParameter('value', $value );
		}

		$myReturn =  $qb->getQuery()->getResult();
		return $myReturn;
	}
	
	
	public function save(){
		try{
			$request = $this->container->get('request_stack')->getCurrentRequest();
			$groupName   = $request->get("groupName");
			if (count($this->findGroupByKey(GroupService::FIND_BY_NAME,$groupName)) >0){
				throw new \Exception('Nome Grupo já está cadastrado. ' .
						'Não foi possível cadastrar o grupo. ');
			}
		   
			AppRest::doConnectRest()->createGroup($groupName);
			$this->em->flush ();
			$avatar = $this->persistImage();
			$groupAttribute = new Ofgroupprop();;
			$groupAttribute->doLoadAll($groupName, GroupService::AVATAR, $avatar);
			$this->em->merge($groupAttribute);
			$this->em->flush ();
		    return Rule::SUCCESS_SAVE;
		} catch(Exception $e){
			$this->logger->error("Conteudo de error by reference " . $e->__toString());
			return Rule::FAIL_SAVE;
		}

	}

	private function persistImage(){
		$request = $this->container->get('request_stack')->getCurrentRequest();
		$file = $request->files->get("file");
		$path = $this->container->getParameter('group_images_directory') .'/';
		if (is_null($file)) {
			return 'default.png';
		}
		$filename = md5(uniqid()).'.'.$file->getClientOriginalExtension();
		$this->logger->info("arquivo de imagem salvo:" . $filename);
		
		$file->move( $path, $filename);
		return $filename;
	}

	/**
	 * Altera o status dos usuarios em um grupo grupo
	 * @param unknown $username - nome do usuario
	 * @param unknown $groupname - nome do usuario. 
	 * @param unknown $rule - Veja as regas em Rules.php 
	 */
	public function saveGroupUserRule($username, $groupname, $rule){
		$this->em->flush ();
		$groupAttribute = new Ofgroupprop();;
		$groupAttribute->doLoadAll($groupname, $username, $rule);
		$this->em->merge($groupAttribute);
		$this->em->flush ();
	}
	
}