<?php

namespace NilsWisiol\CreditCardBundle\Controller;
use Doctrine\Common\Persistence\PersistentObject;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StatisticsController extends Controller {
	
	public function indexAction() {
		$em = $this->getDoctrine()->getEntityManager();
		PersistentObject::setObjectManager($em);
		
		$entries = $em->getRepository('NilsWisiol\CreditCardBundle\Entity\Entry')->findBy(array(), array('date' => 'asc'));
		
		return $this->render('NilsWisiolCreditCardBundle:Statistics:index.html.twig', array('entries' => $entries));
	}
	
}
