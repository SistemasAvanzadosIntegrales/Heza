<?php
class TipoEmpresaController extends Zend_Controller_Action{
    public function init(){
        $this->view->headScript()->appendFile('/js/backend/tipo-empresa.js');
       
    }//function
 
    public function indexAction(){
      
        $sess=new Zend_Session_Namespace('permisos');
        print_r($sess->permisos);
        $this->view->puedeAgregar=strpos($sess->cliente->permisos,"AGREGAR_TIPO_EMPRESA")!==false;
        $this->view->puedeEliminar=strpos($sess->cliente->permisos,"ELIMINAR_TIPO_EMPRESA")!==false;
//        print($this->view->puedeEliminar);
//        exit;
    }//function

    public function gridAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $sess=new Zend_Session_Namespace('permisos');
        
        $filtro=" 1=1 ";

        $tipo_empresa=$this->_getParam('tipo_empresa');
        //$tipo_calculo=$this->_getParam('tipo_calculo');
        //$tipo_calculo=strtoupper($tipo_calculo);
        
        if($tipo_empresa!='')
        {
            //$tipo_empresa=explode(" ", trim($tipo_empresa));
            //for($i=0; $i<=$tipo_empresa[$i]; $i++)
            //{
                //$tipo_empresa[$i]=trim(str_replace(array("'","\"",),array("�","�"),$tipo_empresa[$i]));
                //if($tipo_empresa[$i]!="")
                    $filtro.=" AND (tipo_empresa LIKE '%".$tipo_empresa."%')  ";
            //}//for
        }//if

        $tipo_calculo=$this->_getParam('tipo_calculo');
        if($this->_getParam('tipo_calculo')!="")          
            $filtro.=" AND tipo_calculo=".$this->_getParam('tipo_calculo');

        /*if($tipo_calculo!='' && ($tipo_calculo == 'SA' || $tipo_calculo == 'SC') )
        {
            if ($tipo_calculo == "SA" ) {
                $tipo_calculo = 1;
            }
            else if ($tipo_calculo == "SC") {
                $tipo_calculo = 2;
            }
            //$tipo_calculo=explode(" ", trim($tipo_calculo));
            //for($i=0; $i<=$tipo_calculo[$i]; $i++)
            //{
                //$tipo_calculo[$i]=trim(str_replace(array("'","\"",),array("�","�"),$tipo_calculo[$i]));
                //if($tipo_calculo[$i]!="")
                    $filtro.=" AND (tipo_calculo LIKE '%".$tipo_calculo."%')  ";
            //}//for
        }//if*/
    
        $registros = My_Comun::registrosGrid("TipoEmpresa", $filtro);
        $grid=array();
        $i=0;

        $editar = My_Comun::tienePermiso("EDITAR_TIPO_EMPRESA");
        $eliminar = My_Comun::tienePermiso("ELIMINAR_TIPO_EMPRESA");
            
        foreach($registros['registros'] as $registro)
        {
               
            if($registro->status == 0){
                $grid[$i]['tipo_empresa']='<span class="registro desactivado" rel="'.$registro->id.'">'.$registro->tipo_empresa.'</span>';
                $grid[$i]['tipo_calculo']='<span class="registro desactivado" rel="'.$registro->id.'">'.(($registro->tipo_calculo == 1)?'SA':'SC').'</span>';
                $grid[$i]['status']='<span class="registro desactivado" rel="'.$registro->id.'">Inactivo</span>';
            }else{
                $grid[$i]['tipo_empresa']='<span class="registro" rel="'.$registro->id.'">'.$registro->tipo_empresa.'</span>';
                $grid[$i]['tipo_calculo']='<span class="registro" rel="'.$registro->id.'">'.(($registro->tipo_calculo == 1)?'SA':'SC').'</span>';
                $grid[$i]['status']='<span class="registro" rel="'.$registro->id.'">Activo</span>';
            }
                    
            $i++;
        }//foreach
        My_Comun::armarGrid($registros,$grid);
    }//function

    public function obtenerAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $array = array();
        $array["error"]='';

        if($_POST["id"]!="0"){
            $registro= My_Comun::obtener("TipoEmpresa", "id", $_POST["id"]);
            //print_r((string)$registro->id);exit;
            $array["id"]=(string)$registro->id;
            $array["tipo_empresa"]=(string)$registro->tipo_empresa;
            $array["tipo_calculo"]=(string)$registro->tipo_calculo;
            $array["status"]=(string)$registro->status;
            
        }else{
            $array["error"]="Error al obtener el tipo de empresa: identificador no existe.";
        }

        echo json_encode($array);

    }

    public function guardarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
            //print_r($_POST);
            //exit();
            $bitacora = array();
            $bitacora[0]["modelo"] = "TipoEmpresa";
            $bitacora[0]["campo"] = "tipo_empresa";
            $bitacora[0]["id"] = $_POST["id"];
            $bitacora[0]["agregar"] = "Agregar tipo de empresa";
            $bitacora[0]["editar"] = "Editar tipo de empresa";
                   
            $tipoempresaId = My_Comun::Guardar("TipoEmpresa", $_POST, $_POST["id"], $bitacora);
            echo($tipoempresaId);

            
    }//guardar
    
    
    function eliminarAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $regi=My_Comun::obtener("TipoEmpresa", "id", $_POST["id"]);
            
        $bitacora = array();
        $bitacora[0]["modelo"] = "TipoEmpresa";
        $bitacora[0]["campo"] = "tipo_empresa";
        $bitacora[0]["id"] = $_POST["id"];
        $bitacora[0]["eliminar"] = "Eliminar tipo de empresa";
        $bitacora[0]["deshabilitar"] = "Deshabilitar tipo de empresa";
        $bitacora[0]["habilitar"] = "Habilitar tipo de empresa";
            
        echo My_Comun::eliminar("TipoEmpresa", $_POST["id"], $bitacora);
    }//function 

 /*public function exportarAction(){
        ### Deshabilitamos el layout y la vista
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
          
        $nombre= $this->_getParam('nombre');
       
        $filtro=" 1=1 ";
        $i=6;
        $data = array();
        
        
        if($this->_getParam('status')!=""){         
            $filtro.=" AND status='".str_replace("'","�",$this->_getParam('status'))."' ";
            if($this->_getParam('status') == 0){
                $data[] = array("A$i" =>"Estatus:","B$i" => "Deshabilitado"); 
            }else{
                $data[] = array("A$i" =>"Estatus:","B$i" => "Habilitado");         
            }
          $i++;
        }
        
        if($nombre!=''){
            $data[] = array("A$i" =>"Nombre:","B$i" => $nombre);                
            $i++;
            $nombre=explode(" ", trim($nombre));
            for($j=0; $j<=$nombre[$j]; $j++){
                $nombre[$j]=trim(str_replace(array("'","\"",),array("�","�"),$nombre[$j]));
                if($nombre[$j]!=""){
                    $filtro.=" AND ( nombre LIKE '%".$nombre[$j]."%'  ) ";
                }
            }  
        }

       
        $i++;
        $registros=  My_Comun::obtenerFiltro("Usuario", $filtro, "nombre ASC");

        ini_set("memory_limit", "130M");
        ini_set('max_execution_time', 0);

        $objPHPExcel = new My_PHPExcel_Excel();
        
        
        $columns_name = array
        (
                "A$i" => array(
                        "name" => 'No. DE USUARIO',
                        "width" => 16
                        ),
                "B$i" => array(
                        "name" => 'NOMBRE ',
                        "width" => 30
                        ),
                "C$i" => array(
                        "name" => 'CORREO',
                        "width" => 50
                        ),
                "D$i" => array(
                        "name" => 'ESTATUS',
                        "width" => 13
                        )                           
        );

        //Datos tabla
        foreach($registros as $registro)
        {
            if($registro->status == "0"){
                $a = "Deshabilitado";
            }else{
                $a =  "Habilitado";
            }
            
            $i++;
            $data[] = array(                
                    "A$i" =>$registro->id,
                    "B$i" =>$registro->nombre,
                    "C$i" =>$registro->correo_electronico,
                     "D$i" =>$a
                    );
        }       
        $objPHPExcel->createExcel('Usuario', $columns_name, $data, 10,array('rango'=>'A4:G4','texto'=>'Usuarios KipKar'));
    }

    public function imprimirAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
          
        $nombre= $this->_getParam('nombre');
       
        $filtro=" 1=1 ";

        if($this->_getParam('status')!=""){         
            $filtro.=" AND status='".str_replace("'","�",$this->_getParam('status'))."' ";
            if($this->_getParam('status') == 0){
                $data[] = array("A$i" =>"Estatus:","B$i" => "Deshabilitado"); 
            }else{
                $data[] = array("A$i" =>"Estatus:","B$i" => "Habilitado");         
            }
          $i++;
        }
        
        if($nombre!=''){
            $data[] = array("A$i" =>"Nombre:","B$i" => $nombre);                
            $i++;
            $nombre=explode(" ", trim($nombre));
            for($j=0; $j<=$nombre[$j]; $j++){
                $nombre[$j]=trim(str_replace(array("'","\"",),array("�","�"),$nombre[$j]));
                if($nombre[$j]!=""){
                    $filtro.=" AND ( nombre LIKE '%".$nombre[$j]."%'  ) ";
                }
            }  
        }
        
        

        $registros = My_Comun::obtenerFiltro("Usuario", $filtro);
       
        $pdf= new My_Fpdf_Pdf();
        
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $pdf->Header("IMPRESIÓN DE USUARIOS");

        $pdf->SetFont('Arial','B',11);
        $pdf->SetWidths(array(35,55,55,40));
        $pdf->Row(array('NO. DE USUARIO','NOMBRE','CORREO','ESTATUS'),0,1);
        
        $pdf->SetFont('Arial','',10);
        foreach($registros as $registro)
        {
            $estatus = '';
            switch($registro['status']){
                case 0: $estatus = 'Inhabilitado'; break;
                case 1: $estatus = 'Habilitado'; break;
            }

            $pdf->Row
            (
                array
                (                    
                    $registro->id, $registro->nombre, $registro->correo_electronico, $estatus
                ),0,1           
            );
        }
                
       $pdf->Output();
    }*/
}//class
?>