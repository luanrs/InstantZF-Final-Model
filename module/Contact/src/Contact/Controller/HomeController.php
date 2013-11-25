<?php
namespace Contact\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class HomeController extends AbstractActionController {
	public function indexAction() {
		//$data['action'] = __FUNCTION__;
		//$data['controller'] = __CLASS__;
		$contact = $this->getServiceLocator()->get('Contact\Model\Contact');
		$data['rows'] = $contact->getAllRows();
		
		$log = $this->getServiceLocator()->get('Logger');
		$log->info('Hello Zend Log');
		
		return new ViewModel($data);
		//echo 'Hello Zend Framework 2';
		//return $this->response;
	}
	
	public function newAction() {
		$invalids = array();
		$filter = array(
			'name' => array(
				'name' => 'name',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim')
				),
				'validators' => array(
					array('name' => 'not_empty'),
					array(
						'name' => 'string_length',
						'options' => array(
							'min' => 3		
						)	
					)		
				)
			),
			'email' => array(
				'name' => 'name',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim')		
				),
				'validators' => array(
					array('name' => 'not_empty'),
					array('name' => 'email_address')		
				)
			),
			'phone' => array(
				'name' => 'name',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim')		
				),
				'validators' => array(
					array('name' => 'not_empty')
				)
			)
		);
		$data = array();
		if ($_POST) {
			$factory = new \Zend\InputFilter\Factory();
			$input = $factory->createInputFilter($filter);
			$input->setData($_POST);
			
			if($input->isValid()){
				$contact = $this->getServiceLocator()->get('Contact\Model\Contact');
				$contact->addRow($_POST);
				return $this->redirect()->toRoute('home');
			} else {
				$invalids = $input->getInvalidInput();
			}
			
			$data = $input->getValues();
		}
		
		return new ViewModel(array('row' => $data, 'invalids' => $invalids));
	}
	
	public function editAction() {
		$id = $this->params()->fromQuery('id',0);
		$contact = $this->getServiceLocator()->get('Contact\Model\Contact');

		if ($_POST) {
			$contact->updateRow($_POST,$id);
			return $this->redirect()->toRoute('home');	
		} else {
			$row = $contact->getRow($id);
		}
		
		return new ViewModel($row);
	}
	
	public function deleteAction() {
		$id = $this->params()->fromQuery('id', 0);
		
		$contact = $this->getServiceLocator()->get('Contact\Model\Contact');
		$contact->delRow($id);
		return $this->redirect()->toRoute('home');
	}
	
	public function fileUploadAction() {
		if ($this->getRequest()->isPost()) {
			$size = new \Zend\Validator\File\Size(
				array('min' => '10kB', 'max' => '10MB')	
			);
			$ext = new \Zend\Validator\File\Extension('pdf');
			
			$adapter = new \Zend\File\Transfer\Adapter\Http();
			$adapter->setValidators(array($size, $ext));
			
			$adapter->setDestination('public/uploads');
			
			$files = $this->getRequest()->getFiles();
			
			if ($adapter->isValid()) {
				if ($adapter->receive($files['doc']['name'])) {
					return new ViewModel(array(
						'msg' => $files['doc']['name'].' uploaded!'	
					));
				}
			} else {
				return new ViewModel(array(
					'msg' => $adapter->getMessages()
				));
			}
		}
	}
}