<?php

namespace Etu\Core\CoreBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;
use Etu\Core\UserBundle\Entity\User;

use Symfony\Component\HttpFoundation\Response;

// Import @Route() and @Template() annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
	/**
	 * @Route("/", name="homepage")
	 * @Template()
	 */
	public function indexAction()
	{
		if ($this->getUserLayer()->isConnected()) {
			return $this->indexUserAction();
		}

		if ($this->getUserLayer()->isOrga()) {
			return $this->indexOrgaAction();
		}

		return $this->indexAnonymousAction();
	}


	/**
	 * @return Response
	 */
	protected function indexAnonymousAction()
	{
		return $this->render('EtuCoreBundle:Main:indexAnonymous.html.twig');
	}

	/**
	 * @return Response
	 */
	protected function indexOrgaAction()
	{
		return $this->render('EtuCoreBundle:Main:indexOrga.html.twig');
	}

	/**
	 * @return Response
	 */
	protected function indexUserAction()
	{
		// Add a block to the sidebar about the current flux
		$this->getSidebarBuilder()
			->addBlock('flux.sidebar.parameters.title')
				->setPosition(0)
				->add('flux.sidebar.parameters.items.suscribtions')
					->setIcon('etu-icon-star')
					->setUrl('')
				->end()
				->add('flux.sidebar.parameters.items.parameters')
					->setIcon('etu-icon-gear')
					->setUrl('')
				->end()
			->end();

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		// Load only notifications we should display, ie. notifications sent from
		// currently enabled modules
		$where = array();

		$query = $em
			->createQueryBuilder()
			->select('n')
			->from('EtuCoreBundle:Notification', 'n')
			->where('n.user = :user')
			->orderBy('n.isSuper', 'DESC')
			->addOrderBy('n.date', 'DESC')
			->setParameter('user', $this->getUser()->getId())
			->setMaxResults(50);

		foreach ($this->getKernel()->getModulesDefinitions() as $module) {
			$identifier = $module->getIdentifier();

			$where[] = 'n.module = :'.$identifier;
			$query->setParameter($identifier, $identifier);
		}

		/** @var $notifications Notification[] */
		$notifications = $query->andWhere(implode(' OR ', $where))->getQuery()->getResult();

		$this->get('twig')->addGlobal('etu_count_new_notifs', 0);

		$view = $this->render('EtuCoreBundle:Main:index.html.twig', array(
			'notifs' => $notifications
		));

		// Set notifications as viewed
		foreach ($notifications as $notif) {
			if ($notif->getIsNew()) {
				$notif->setIsNew(false);
				$em->persist($notif);
			}
		}

		$em->flush();

		return $view;
	}
}
