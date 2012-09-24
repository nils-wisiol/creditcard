<?php

namespace NilsWisiol\CreditCardBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use NilsWisiol\CreditCardBundle\Entity\Document;
use NilsWisiol\CreditCardBundle\Entity\Entry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {
	
	public function indexAction() {
		$em = $this->getDoctrine()->getEntityManager();
		
		$entries = $em->getRepository('NilsWisiol\CreditCardBundle\Entity\Entry')->findBy(array(), array('date' => 'asc'));
		
		return $this->render('NilsWisiolCreditCardBundle:Default:index.html.twig', array('entries' => $entries, 'categories' => $this->getCategories()));
	}

	public function importAction() {
		$em = $this->getDoctrine()->getEntityManager();
		
    $document = new Document();
    $form = $this->createFormBuilder($document)
        ->add('name')
        ->add('file')
        ->add('format', 'choice', array(
        			'choices' => array('dkb' => 'Deutsche Kreditbank', 'boa' => 'Bank of America'),
        			'required' => true,
        		))
        ->add('account', 'entity', array(
        			'class' => 'NilsWisiolCreditCardBundle:Account',
        		))
        ->getForm()
    ;

    if ($this->getRequest()->getMethod() === 'POST') {
        $form->bindRequest($this->getRequest());
        if ($form->isValid()) {
            $file = $form['file']->getData()->openFile('r');
            
            $input = array();
            while (!$file->eof()) {
            	$input[] = utf8_encode($file->current());
            	$file->next();
            }
            
            $this->doImport($document, $input);
            $em->persist($document);
            $em->flush();

            return $this->redirect($this->generateUrl('nils_wisiol_credit_card_import_detail', array('documentId' => $document->getId())));
        }
    }

    return $this->render('NilsWisiolCreditCardBundle:Default:import.html.twig', array('form' => $form->createView()));
	}
	
	public function importDetailAction($documentId) {
		$em = $this->getDoctrine()->getEntityManager();
		$document = $em->find("NilsWisiol\CreditCardBundle\Entity\Document", $documentId);
		$entries = $em->getRepository("NilsWisiol\CreditCardBundle\Entity\Entry")->findBy(array('document' => $document->getId()), array('date' => 'asc'));
		
		return $this->render('NilsWisiolCreditCardBundle:Default:importDetails.html.twig', array('entries' => $entries, 'categories' => $this->getCategories(), 'import' => $document));
	}
	
	public function importUndoAction($documentId) {
		$em = $this->getDoctrine()->getEntityManager();
		$document = $em->find("NilsWisiol\CreditCardBundle\Entity\Document", $documentId);
		$em->remove($document);
		$em->flush();
		
		return $this->redirect($this->generateUrl('nils_wisiol_credit_card_homepage'));
	}
	
	public function categoryDetailAction($categoryId) {
		$em = $this->getDoctrine()->getEntityManager();
		$category = $em->find("NilsWisiol\CreditCardBundle\Entity\Category", $categoryId);
		
		return $this->render('NilsWisiolCreditCardBundle:Default:categoryDetails.html.twig', array('entries' => $category->getDescendants(), 'categories' => $this->getCategories(), 'category' => $category));
	}
	
	public function categoriesAction() {
		$em = $this->getDoctrine()->getEntityManager();
		$categories = $em->getRepository('NilsWisiol\CreditCardBundle\Entity\Category')->findBy(array('parent' => null));
		return $this->render('NilsWisiolCreditCardBundle:Default:categories.html.twig', array('categories' => $categories));
	}
	
	public function categoryChangeAction() {
		$em = $this->getDoctrine()->getEntityManager();
		$category = $em->find("NilsWisiol\CreditCardBundle\Entity\Category", $this->getRequest()->get('categoryId'));
		$entry = $em->find("NilsWisiol\CreditCardBundle\Entity\Entry", $this->getRequest()->get('entryId'));
		$entry->setCategory($category);
		$entry->setNote($this->getRequest()->get('note'));
		$em->flush();
		
		return new Response();
	}
	
	protected function doImport($document, $input) {
		if ($document->getFormat() == 'dkb') {
			$this->doImportDKBCSV($document, $input);
		} else if ($document->getFormat() == 'boa') {
			$this->doImportBOACSV($document, $input);
		} else {
			throw new \Exception("Unknown file format.");
		}
	}
	
	protected function doImportDKBCSV($document, $input) {
		$em = $this->getDoctrine()->getEntityManager();
		
		for($i=8;$i<count($input);$i++) {
			
			// DKB CSV has 6 cols:
			// entry booked (yes/no); date; receipt date; desc; amount (eur); amount (incl. foreign currency symbol)
			$cells = explode(";",$input[$i]); 
			$e = new Entry();
			$e->setDate(new \DateTime($this->stripQuotationMarks($cells[1])));
			$e->setDateReceipt(new \DateTime($this->stripQuotationMarks($cells[2])));
			$e->setDesc($this->stripQuotationMarks($cells[3]));
			$e->setAmount($this->getDoubleFromDKB($cells[4]));
			if ($cells[5] != "\"\"") {
				$foreign = explode(" ", $this->stripQuotationMarks($cells[5]));
				$e->setAmountOrg($this->getDoubleFromDKB($foreign[0]));
				$e->setCurOrg($foreign[1]);
			}
			
			$e->setCur("EUR");
			$e->setAccount($document->getAccount());
			$e->setDocument($document);
			
			$em->persist($e);
			
			$this->checkForDuplicates($e->getHash());
			
		}
	}
	
	protected function doImportBOACSV($document, $input) {
		$em = $this->getDoctrine()->getEntityManager();
		
		for($i=7;$i<count($input);$i++) {
			if (trim($input[$i]) == "")
				continue;
				
			// BOA CSV has 4 cols:
			// date; desc; amount (usd); running bal
			$cells = explode(",",$input[$i]);
			$e = new Entry();
			$e->setDate(new \DateTime($cells[0]));
			$e->setDesc($this->stripQuotationMarks($cells[1]));
			$e->setAmount((double)$this->stripQuotationMarks($cells[2]));
			$e->setCur("USD");
			$e->setAccount($document->getAccount());
			$e->setDocument($document);
				
			$em->persist($e);
				
		}		
	}
	
	protected function checkForDuplicates($hash) {
		$em = $this->getDoctrine()->getEntityManager();
		$duplicates = $em->getRepository("NilsWisiol\CreditCardBundle\Entity\Entry")->findBy(array('hash' => $hash));
		if ($duplicates > 0)
			throw new \Exception("Duplicate detected: " . $duplicates[0]->getDate()->format('d.m.Y') . " " . $duplicates[0]->getDesc() . " " . $duplicates[0]->getAmount() . " " . $duplicates[0]->getCur());
		return;
	}
	
	private function stripQuotationMarks($subject) {
		if (substr($subject, 0, 1) == "\"") {
			$subject = substr($subject, 1);
		}
		if (substr($subject, -1) == "\"") {
			$subject = substr($subject, 0, -1);
		}
		
		return $subject;
	}
	
	private function getDoubleFromDKB($string) {
		return (double)str_replace(",", ".", str_replace(".", "", $this->stripQuotationMarks($string)));
	}
	
	protected function getCategories() {
		return $this->getDoctrine()->getEntityManager()->getRepository("NilsWisiol\CreditCardBundle\Entity\Category")->findAll();
	}
		
}
