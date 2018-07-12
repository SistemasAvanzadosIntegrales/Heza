<?php
class TipoImpuestoController extends Zend_Controller_Action{
    public function init(){
        $this->view->headScript()->appendFile('/js/backend/tipo-impuesto.js');
       
    }//function
 
    public function indexAction(){
      
        $sess=new Zend_Session_Namespace('permisos');
        print_r($sess->permisos);
        $this->view->puedeAgregar=strpos($sess->cliente->permisos,"AGREGAR_TIPO_IMPUESTO")!==false;
        $this->view->puedeEliminar=strpos($sess->cliente->permisos,"ELIMINAR_TIPO_IMPUESTO")!==false;
//        print($this->view->puedeEliminar);
//        exit;
    }//function

    public function gridAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $sess=new Zend_Session_Namespace('permisos');
        
        $filtro=" 1=1 ";

        $descripcion=$this->_getParam('descripcion');

        $abreviatura=$this->_getParam('abreviatura');

        $status=$this->_getParam('status');
        if($this->_getParam('status')!="")          
            $filtro.=" AND status=".$this->_getParam('status');
        
        if($descripcion!='')
        {
            $descripcion=explode(" ", trim($descripcion));
            for($i=0; $i<=$descripcion[$i]; $i++)
            {
                $descripcion[$i]=trim(str_replace(array("'","\"",),array("�","�"),$descripcion[$i]));
                if($descripcion[$i]!="")
                    $filtro.=" AND (descripcion LIKE '%".$descripcion[$i]."%')  ";
            }//for
        }//if

        if($abreviatura!='')
        {
            $abreviatura=explode(" ", trim($abreviatura));
            for($i=0; $i<=$abreviatura[$i]; $i++)
            {
                $abreviatura[$i]=trim(str_replace(array("'","\"",),array("�","�"),$abreviatura[$i]));
                if($abreviatura[$i]!="")
                    $filtro.=" AND (abreviatura LIKE '%".$abreviatura[$i]."%')  ";
            }//for
        }//if
    
        $registros = My_Comun::registrosGrid("TipoImpuesto", $filtro);
        $grid=array();
        $i=0;

        $editar = My_Comun::tienePermiso("EDITAR_TIPO_IMPUESTO");
        $eliminar = My_Comun::tienePermiso("ELIMINAR_TIPO_IMPUESTO");
            
        foreach($registros['registros'] as $registro)
        {
               
            if($registro->status == 0){
                $grid[$i]['abreviatura']='<span class="registro desactivado" rel="'.$registro->id.'">'.$registro->abreviatura.'</span>';
                $grid[$i]['descripcion']='<span class="registro desactivado" rel="'.$registro->id.'">'.$registro->descripcion.'</span>';
            }else{
                $grid[$i]['abreviatura']='<span class="registro" rel="'.$registro->id.'">'.$registro->abreviatura.'</span>';
                $grid[$i]['descripcion']='<span class="registro" rel="'.$registro->id.'">'.$registro->descripcion.'</span>';
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
            $registro= My_Comun::obtener("TipoImpuesto", "id", $_POST["id"]);
            //print_r((string)$registro->id);exit;
            $array["id"]=(string)$registro->id;
            $array["abreviatura"]=(string)$registro->abreviatura;
            $array["descripcion"]=(string)$registro->descripcion;
            $array["status"]=(string)$registro->status;
            
        }else{
            $array["error"]="Error al obtener el tipo de impuesto: identificador no existe.";
        }

        echo json_encode($array);

    }

    public function guardarAction(){
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
            $bitacora = array();
            $bitacora[0]["modelo"] = "TipoImpuesto";
            $bitacora[0]["campo"] = "abreviatura";
            $bitacora[0]["id"] = $_POST["id"];
            $bitacora[0]["agregar"] = "Agregar tipo de impuesto";
            $bitacora[0]["editar"] = "Editar tipo de impuesto";
                   
            $tipoimpuestoId = My_Comun::Guardar("TipoImpuesto", $_POST, $_POST["id"], $bitacora);
            echo($tipoimpuestoId);
    }//guardar
    
    
    function eliminarAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $regi=My_Comun::obtener("TipoImpuesto", "id", $_POST["id"]);
            
        $bitacora = array();
        $bitacora[0]["modelo"] = "TipoImpuesto";
        $bitacora[0]["campo"] = "abreviatura";
        $bitacora[0]["id"] = $_POST["id"];
        $bitacora[0]["eliminar"] = "Eliminar tipo de impuesto";
        $bitacora[0]["deshabilitar"] = "Deshabilitar tipo de impuesto";
        $bitacora[0]["habilitar"] = "Habilitar tipo de impuesto";
            
        echo My_Comun::eliminar("TipoImpuesto", $_POST["id"], $bitacora);
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