<?php

class IngresoController extends Zend_Controller_Action
{

    public function init(){

        $this->view->headScript()->appendFile('/js/backend/ingreso.js');
    }

    public function indexAction(){
        
    }

    public function ingresarAction(){
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        ### mandamos un parametro extra para indicar que se esta ingresando desde la parte publica
        $adaptador = new ZC_Auth_Adapter($_POST['correo_electronico'], $_POST['contrasena'],"1");
        $resultado = Zend_Auth::getInstance()->authenticate($adaptador);

        if(Zend_Auth::getInstance()->hasIdentity()){

            $sess = new Zend_Session_Namespace('permisos');
            $sess->cliente = Zend_Auth::getInstance()->getIdentity();
            echo $sess->cliente->id;

        }
        else{

            $usuario = $resultado->getIdentity();
            $mensajes = $resultado->getMessages();
            echo $usuario->correo_electronico.'|'.$mensajes[0];
        }

    }

    public function salirAction(){
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::namespaceUnset('permisos');
        Zend_Session::destroy();
        
        header("Location: http://".$_SERVER["SERVER_NAME"]);
    }

    
}

